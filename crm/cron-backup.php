<?php
/**
 * CLI cron entry point for scheduled backups.
 * Schedule via cPanel > Cron Jobs:  0 3 * * * php /home/youruser/public_html/crm/cron-backup.php
 *
 * Creates a new backup, then applies retention (keeps the most recent N, default 14).
 */

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    die('This script can only be run from the command line / cron, not via a browser.');
}

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/backup.php';

$keep = 14; // adjust if you want a different retention window

$name = backup_create();
if ($name) {
    audit('backup_create', 'system', 0, $name . ' (cron)');
    echo "Backup created: $name\n";
} else {
    error_log('cron-backup.php: backup_create() failed');
    echo "ERROR: backup creation failed — check error log.\n";
    exit(1);
}

$deleted = backup_apply_retention($keep);
echo "Retention applied: $deleted old backup(s) removed, keeping the most recent $keep.\n";
