<?php
// TEMPORARY DIAGNOSTIC — delete this file from the server immediately after use.
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

echo "<pre>";
echo "PHP version: " . PHP_VERSION . "\n";

try {
    require_once __DIR__ . '/includes/auth.php';
    echo "auth.php loaded OK\n";
    require_once dirname(__DIR__) . '/includes/i18n.php';
    echo "i18n.php loaded OK\n";
    portal_start();
    echo "portal_start() OK\n";
    echo "DB_DRIVER: " . (defined('DB_DRIVER') ? DB_DRIVER : 'NOT DEFINED') . "\n";

    $token = $_GET['token'] ?? '65cca167c85ba7d81a31ed2763b3d5e6b72f95e0f03bf5cd';
    echo "Testing token: $token (length " . strlen($token) . ")\n";

    $invite = portal_validate_invite($token);
    echo "portal_validate_invite() OK, result: ";
    var_dump($invite);
} catch (Throwable $e) {
    echo "CAUGHT ERROR:\n";
    echo $e->getMessage() . "\n";
    echo $e->getFile() . ':' . $e->getLine() . "\n";
    echo $e->getTraceAsString() . "\n";
}
echo "</pre>";
