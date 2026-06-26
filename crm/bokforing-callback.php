<?php
/**
 * OAuth callback for Fortnox/Visma — exchanges the authorization code for tokens.
 */
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/fortnox.php';
require_once __DIR__ . '/includes/visma.php';
require_role(['super_admin']);

$provider = $_GET['provider'] ?? '';
$code     = $_GET['code'] ?? '';
$state    = $_GET['state'] ?? '';
$redirectUri = (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . '/crm/bokforing-callback.php?provider=' . $provider;

$ok = false;

if ($provider === 'fortnox' && $code && $state === get_setting('fortnox_oauth_state')) {
    $ok = fortnox_exchange_code($code, $redirectUri);
} elseif ($provider === 'visma' && $code && $state === get_setting('visma_oauth_state')) {
    $ok = visma_exchange_code($code, $redirectUri);
}

flash($ok ? 'Anslutning lyckades!' : 'Anslutningen misslyckades. Kontrollera klientuppgifterna och försök igen.', $ok ? 'success' : 'error');
header('Location: bokforing.php');
exit;
