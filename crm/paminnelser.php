<?php
/**
 * CRM — Fakturapåminnelser (automated overdue invoice reminders)
 */
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/invoice-reminders.php';
$me = require_role(['finance', 'sales', 'super_admin']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $action = $_POST['action'] ?? '';

    if ($action === 'save_settings') {
        $days = array_filter(array_map('trim', explode(',', $_POST['days'] ?? '')), fn($d) => ctype_digit($d) && $d !== '');
        set_setting('invoice_reminder_days', implode(',', $days) ?: '3,7,14,30');
        set_setting('invoice_reminders_enabled', isset($_POST['enabled']) ? '1' : '0');
        flash('Inställningar sparade.');
    } elseif ($action === 'run_now') {
        $summary = invoice_reminders_run();
        audit('invoice_reminders_run', 'system', 0, json_encode($summary) . ' (manual)');
        flash("Klart: {$summary['sent']} påminnelse(r) skickade av {$summary['checked']} förfallna fakturor (saknar e-post: {$summary['skipped_no_email']}).");
    }
    header('Location: paminnelser.php'); exit;
}

$schedule = invoice_reminder_schedule();
$enabled  = invoice_reminders_enabled();

$daysOverdueExpr = DB_DRIVER === 'mysql'
    ? 'DATEDIFF(CURDATE(), i.due_date)'
    : "CAST((julianday('now') - julianday(i.due_date)) AS INTEGER)";
$todayExpr = DB_DRIVER === 'mysql' ? 'CURDATE()' : "date('now')";
$overdue = db()->query("
    SELECT i.*, c.name AS customer_name, c.email AS customer_email,
           $daysOverdueExpr AS days_overdue
    FROM invoices i
    JOIN customers c ON c.id = i.customer_id
    WHERE i.status IN ('sent','overdue') AND i.due_date < $todayExpr
    ORDER BY i.due_date ASC
")->fetchAll();

$recentReminders = db()->query("
    SELECT ir.*, i.invoice_no, c.name AS customer_name
    FROM invoice_reminders ir
    JOIN invoices i ON i.id = ir.invoice_id
    JOIN customers c ON c.id = i.customer_id
    ORDER BY ir.sent_at DESC LIMIT 25
")->fetchAll();

$crm_title = 'Fakturapåminnelser';
$crm_page  = 'paminnelser';
require_once __DIR__ . '/includes/crm-header.php';
?>
<div class="topbar">
  <div>
    <h1>Fakturapåminnelser</h1>
    <div class="topbar__sub">Automatiska betalningspåminnelser för förfallna fakturor</div>
  </div>
  <div class="topbar__actions">
    <form method="post" onsubmit="return confirm('Skicka påminnelser nu för alla kvalificerade förfallna fakturor?')">
      <?= csrf_field() ?><input type="hidden" name="action" value="run_now">
      <button class="btn btn--primary">Skicka påminnelser nu</button>
    </form>
  </div>
</div>

<?php flash(); ?>

<div class="grid-2" style="margin-bottom:20px;display:grid;grid-template-columns:1fr 1fr;gap:20px">
  <div class="card card--pad">
    <h3 style="font-size:14.5px;margin-bottom:14px">Inställningar</h3>
    <form method="post">
      <?= csrf_field() ?><input type="hidden" name="action" value="save_settings">
      <div class="fg">
        <label style="display:flex;align-items:center;gap:8px;font-weight:500">
          <input type="checkbox" name="enabled" <?= $enabled ? 'checked' : '' ?>> Aktivera automatiska påminnelser
        </label>
      </div>
      <div class="fg">
        <label>Skicka påminnelse efter (dagar förfallen, kommaseparerat)</label>
        <input class="fi" type="text" name="days" value="<?= e(implode(',', $schedule)) ?>" placeholder="3,7,14,30">
        <span style="font-size:11.5px;color:var(--gray)">En påminnelse skickas per tröskelvärde, max en gång per faktura och dag.</span>
      </div>
      <button class="btn btn--ghost btn--sm">Spara</button>
    </form>
  </div>

  <div class="card card--pad" style="background:#F9FAFB">
    <h3 style="font-size:14px;margin-bottom:8px">Schemalägg daglig körning (cPanel cron)</h3>
    <p style="font-size:12.5px;color:var(--gray);margin-bottom:8px">Lägg till en cron-job i cPanel &gt; Cron Jobs, t.ex. varje morgon kl 08:00:</p>
    <code style="display:block;background:#fff;border:1px solid var(--border);border-radius:8px;padding:10px 14px;font-size:12px;word-break:break-all">0 8 * * * php <?= dirname(__DIR__) ?>/crm/cron-invoice-reminders.php</code>
  </div>
</div>

<div class="card" style="overflow:hidden;margin-bottom:20px">
  <div style="padding:14px 18px;border-bottom:1px solid var(--border)"><h3 style="font-size:14.5px;margin:0">Förfallna fakturor (<?= count($overdue) ?>)</h3></div>
  <?php if ($overdue): ?>
  <table class="data">
    <thead><tr><th>Faktura</th><th>Kund</th><th>Förfallodatum</th><th>Dagar försenad</th><th>Belopp</th><th>E-post</th></tr></thead>
    <tbody>
    <?php foreach ($overdue as $inv): ?>
    <tr>
      <td><a href="faktura.php?id=<?= $inv['id'] ?>"><?= e($inv['invoice_no'] ?? '#'.$inv['id']) ?></a></td>
      <td><?= e($inv['customer_name']) ?></td>
      <td style="font-size:12.5px"><?= e($inv['due_date']) ?></td>
      <td><span class="badge-danger" style="padding:2px 8px;border-radius:20px;font-size:11.5px"><?= (int)$inv['days_overdue'] ?> dagar</span></td>
      <td style="font-weight:600"><?= number_format($inv['total'] - $inv['paid_amount'], 0, ',', ' ') ?> kr</td>
      <td style="font-size:12px;color:<?= $inv['customer_email'] ? 'var(--gray)' : 'var(--red)' ?>"><?= e($inv['customer_email'] ?: 'Saknas') ?></td>
    </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
  <?php else: ?>
  <p style="padding:24px;color:var(--gray);font-size:13px">Inga förfallna fakturor just nu.</p>
  <?php endif; ?>
</div>

<div class="card" style="overflow:hidden">
  <div style="padding:14px 18px;border-bottom:1px solid var(--border)"><h3 style="font-size:14.5px;margin:0">Senast skickade påminnelser</h3></div>
  <?php if ($recentReminders): ?>
  <table class="data">
    <thead><tr><th>Faktura</th><th>Kund</th><th>Dagar försenad</th><th>Skickad</th></tr></thead>
    <tbody>
    <?php foreach ($recentReminders as $r): ?>
    <tr>
      <td><a href="faktura.php?id=<?= $r['invoice_id'] ?>"><?= e($r['invoice_no'] ?? '#'.$r['invoice_id']) ?></a></td>
      <td><?= e($r['customer_name']) ?></td>
      <td><?= (int)$r['days_overdue'] ?> dagar</td>
      <td style="font-size:12px;color:var(--gray)"><?= e($r['sent_at']) ?></td>
    </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
  <?php else: ?>
  <p style="padding:24px;color:var(--gray);font-size:13px">Inga påminnelser skickade ännu.</p>
  <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/crm-footer.php'; ?>
