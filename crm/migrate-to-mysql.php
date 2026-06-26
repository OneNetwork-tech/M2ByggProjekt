<?php
/**
 * One-off CLI helper: migrate data from the local SQLite database to MySQL.
 *
 * Usage:
 *   1. Create the MySQL database + user on your host (cPanel: MySQL Databases).
 *   2. Fill in DB_HOST / DB_NAME / DB_USER / DB_PASS in crm/config.php (leave DB_DRIVER as 'sqlite' for now).
 *   3. Run from CLI:  php crm/migrate-to-mysql.php
 *   4. Once it reports success, switch DB_DRIVER to 'mysql' in crm/config.php.
 *
 * This script is read-only against SQLite and additive against MySQL — safe to re-run,
 * but it does NOT delete existing MySQL rows first. Run against a fresh MySQL database.
 */

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    die('This script can only be run from the command line (php crm/migrate-to-mysql.php), not via a browser.');
}

require_once __DIR__ . '/config.php';

echo "M2 Platform — SQLite → MySQL migration\n";
echo "========================================\n\n";

// Source: SQLite (always, regardless of current DB_DRIVER setting)
$sqlitePath = DB_SQLITE_PATH;
if (!is_file($sqlitePath)) {
    die("ERROR: SQLite database not found at $sqlitePath\n");
}
$sqlite = new PDO('sqlite:' . $sqlitePath, null, null, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
]);
echo "✓ Connected to source SQLite: $sqlitePath\n";

// Target: MySQL
try {
    $mysql = new PDO(
        'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
        DB_USER, DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
    );
} catch (PDOException $e) {
    die("ERROR: Could not connect to MySQL (" . DB_HOST . '/' . DB_NAME . "): " . $e->getMessage() . "\n" .
        "Check DB_HOST / DB_NAME / DB_USER / DB_PASS in crm/config.php.\n");
}
echo "✓ Connected to target MySQL: " . DB_HOST . '/' . DB_NAME . "\n\n";

// Create schema on MySQL (reuses the same table definitions as SQLite, dialect-aware)
require_once __DIR__ . '/includes/db.php';
echo "Creating schema on MySQL...\n";
migrate($mysql, 'mysql');
echo "✓ Schema created/verified.\n\n";

// Discover tables in SQLite (skip internal sqlite tables)
$tables = $sqlite->query("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%'")
    ->fetchAll(PDO::FETCH_COLUMN);

$mysql->exec('SET FOREIGN_KEY_CHECKS=0');

$totalRows = 0;
foreach ($tables as $table) {
    $rows = $sqlite->query("SELECT * FROM \"$table\"")->fetchAll();
    if (!$rows) {
        echo "  $table: 0 rows (skipped)\n";
        continue;
    }

    $columns = array_keys($rows[0]);
    $colList = implode(',', array_map(fn($c) => "`$c`", $columns));
    $placeholders = implode(',', array_fill(0, count($columns), '?'));
    $stmt = $mysql->prepare("INSERT IGNORE INTO `$table` ($colList) VALUES ($placeholders)");

    $inserted = 0;
    foreach ($rows as $row) {
        $stmt->execute(array_values($row));
        $inserted++;
    }
    $totalRows += $inserted;
    echo "  $table: $inserted rows migrated\n";
}

$mysql->exec('SET FOREIGN_KEY_CHECKS=1');

echo "\n✓ Done. $totalRows total rows migrated across " . count($tables) . " tables.\n";
echo "\nNext step: set DB_DRIVER to 'mysql' in crm/config.php to switch the app over.\n";
