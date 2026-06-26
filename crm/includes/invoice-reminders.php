<?php
/**
 * Automated overdue-invoice payment reminders.
 *
 * Schedule (days overdue) is stored as a comma-separated setting, e.g. "3,7,14,30".
 * Each invoice gets at most one reminder per threshold crossed — invoice_reminders
 * logs which thresholds have already fired so a daily cron run never double-sends.
 */

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/mailer.php';

function invoice_reminder_schedule(): array {
    $raw = get_setting('invoice_reminder_days', '3,7,14,30');
    $days = array_filter(array_map('intval', explode(',', $raw)));
    sort($days);
    return array_values(array_unique($days));
}

function invoice_reminders_enabled(): bool {
    return get_setting('invoice_reminders_enabled', '1') === '1';
}

/** Marks any sent invoice past its due date as overdue. Returns count updated. */
function invoice_reminders_refresh_overdue(): int {
    $pdo = db();
    $sql = DB_DRIVER === 'mysql'
        ? "UPDATE invoices SET status='overdue' WHERE status='sent' AND due_date < CURDATE()"
        : "UPDATE invoices SET status='overdue' WHERE status='sent' AND due_date < date('now')";
    return $pdo->exec($sql);
}

/**
 * Sends due reminders for all overdue invoices. Returns a summary array:
 * ['checked' => int, 'sent' => int, 'skipped_no_email' => int, 'errors' => array]
 */
function invoice_reminders_run(): array {
    $summary = ['checked' => 0, 'sent' => 0, 'skipped_no_email' => 0, 'errors' => []];

    if (!invoice_reminders_enabled()) {
        return $summary;
    }

    invoice_reminders_refresh_overdue();
    $schedule = invoice_reminder_schedule();
    if (!$schedule) return $summary;

    $pdo = db();
    $invoices = $pdo->query("
        SELECT i.*, c.name AS customer_name, c.email AS customer_email
        FROM invoices i
        JOIN customers c ON c.id = i.customer_id
        WHERE i.status = 'overdue'
    ")->fetchAll();

    $sentStmt = $pdo->prepare("SELECT days_overdue FROM invoice_reminders WHERE invoice_id = ?");
    $logStmt  = $pdo->prepare("INSERT INTO invoice_reminders (invoice_id, days_overdue) VALUES (?, ?)");

    foreach ($invoices as $inv) {
        $summary['checked']++;
        $daysOverdue = (int)floor((time() - strtotime($inv['due_date'])) / 86400);
        if ($daysOverdue < $schedule[0]) continue;

        $sentStmt->execute([$inv['id']]);
        $already = array_map('intval', array_column($sentStmt->fetchAll(), 'days_overdue'));

        // Largest threshold reached today that hasn't fired yet.
        $threshold = null;
        foreach ($schedule as $t) {
            if ($daysOverdue >= $t && !in_array($t, $already, true)) $threshold = $t;
        }
        if ($threshold === null) continue;

        if (empty($inv['customer_email'])) {
            $summary['skipped_no_email']++;
            continue;
        }

        $amountDue = number_format($inv['total'] - $inv['paid_amount'], 0, ',', ' ');
        $payUrl = (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? 'm2team.se') . '/portal/betala.php?invoice=' . $inv['id'];
        $subject = "Påminnelse: Faktura {$inv['invoice_no']} är förfallen";
        $body = "<p>Hej {$inv['customer_name']},</p>"
              . "<p>Vi vill påminna om att faktura <strong>{$inv['invoice_no']}</strong> på <strong>{$amountDue} kr</strong> "
              . "förföll till betalning den " . date('j M Y', strtotime($inv['due_date'])) . " ({$daysOverdue} dagar sedan).</p>"
              . "<p>Logga in på kundportalen för att betala eller kontakta oss om du har frågor.</p>";

        $ok = crm_send_mail($inv['customer_email'], $inv['customer_name'], $subject, $body, 'invoice', (int)$inv['id'], $payUrl, 'Betala nu');

        if ($ok) {
            $logStmt->execute([$inv['id'], $threshold]);
            $summary['sent']++;
            log_timeline('invoice', (int)$inv['id'], 'reminder', "Betalningspåminnelse skickad ({$daysOverdue} dagar försenad)");
        } else {
            $summary['errors'][] = "Invoice {$inv['invoice_no']}: send failed";
        }
    }

    return $summary;
}
