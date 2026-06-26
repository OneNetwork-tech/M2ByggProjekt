<?php
/**
 * CLI cron entry point for overdue-invoice payment reminders.
 * Schedule via cPanel > Cron Jobs:  0 8 * * * php /home/youruser/public_html/crm/cron-invoice-reminders.php
 */

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    die('This script can only be run from the command line / cron, not via a browser.');
}

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/invoice-reminders.php';

$summary = invoice_reminders_run();

audit('invoice_reminders_run', 'system', 0, json_encode($summary));

echo "Checked: {$summary['checked']} overdue invoice(s)\n";
echo "Reminders sent: {$summary['sent']}\n";
echo "Skipped (no customer email): {$summary['skipped_no_email']}\n";
if ($summary['errors']) {
    echo "Errors:\n";
    foreach ($summary['errors'] as $e) echo " - $e\n";
}
