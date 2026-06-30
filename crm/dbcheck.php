<?php
// TEMPORARY DIAGNOSTIC — delete this file immediately after checking it once.
require_once __DIR__ . '/includes/db.php';
header('Content-Type: text/plain; charset=utf-8');

echo "DB_DRIVER: " . DB_DRIVER . "\n";
echo "config.local.php exists: " . (file_exists(__DIR__ . '/config.local.php') ? 'YES' : 'NO') . "\n";

if (DB_DRIVER === 'mysql') {
    echo "DB_HOST: " . DB_HOST . "\n";
    echo "DB_NAME: " . DB_NAME . "\n";
    echo "DB_USER: " . DB_USER . "\n";
}

try {
    $pdo = db();
    $count = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
    echo "Connected OK. Users in DB: $count\n";
    $rows = $pdo->query("SELECT id, email, created_at FROM users ORDER BY id DESC LIMIT 5")->fetchAll();
    echo "Last 5 users:\n";
    foreach ($rows as $r) echo "  #{$r['id']} {$r['email']} ({$r['created_at']})\n";
} catch (Throwable $e) {
    echo "CONNECTION FAILED: " . $e->getMessage() . "\n";
}
