<?php
/**
 * M2 Platform — Authentication & RBAC
 */
require_once __DIR__ . '/db.php';

ini_set('session.cookie_httponly', 1);
ini_set('session.use_strict_mode', 1);
session_name(SESSION_NAME);
session_set_cookie_params(['lifetime' => SESSION_LIFETIME, 'httponly' => true, 'samesite' => 'Lax', 'secure' => isset($_SERVER['HTTPS'])]);
if (session_status() === PHP_SESSION_NONE) session_start();

function current_user(): ?array {
    if (empty($_SESSION['user_id'])) return null;
    static $user = null;
    if ($user === null) {
        $stmt = db()->prepare("SELECT * FROM users WHERE id = ? AND active = 1");
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch() ?: null;
    }
    return $user;
}

function require_login(): array {
    $user = current_user();
    if (!$user) { header('Location: login.php'); exit; }
    return $user;
}

/**
 * Role check. super_admin and admin both pass everything (admin-tier roles have full
 * app access) — the narrower restriction (admin can't create/promote/demote/deactivate
 * other admin-tier accounts) is enforced separately in crm/anvandare.php, not here.
 */
function require_role(array $roles): array {
    $user = require_login();
    if (!in_array($user['role'], ADMIN_TIER_ROLES) && !in_array($user['role'], $roles)) {
        http_response_code(403);
        die('<div style="font-family:sans-serif;padding:48px;text-align:center"><h2>403 – Behörighet saknas</h2><p>Din roll har inte tillgång till denna sida.</p><a href="index.php">← Tillbaka</a></div>');
    }
    return $user;
}

function attempt_login(string $email, string $password): bool {
    $stmt = db()->prepare("SELECT * FROM users WHERE email = ? AND active = 1");
    $stmt->execute([strtolower(trim($email))]);
    $user = $stmt->fetch();
    if ($user && password_verify($password, $user['password_hash'])) {
        session_regenerate_id(true);
        $_SESSION['user_id'] = $user['id'];
        db()->prepare("UPDATE users SET last_login = " . (DB_DRIVER==='mysql' ? 'NOW()' : "datetime('now','localtime')") . " WHERE id = ?")->execute([$user['id']]);
        audit('login', 'user', $user['id']);
        return true;
    }
    return false;
}

function logout(): void {
    audit('logout');
    $_SESSION = [];
    session_destroy();
}

/* CSRF */
function csrf_token(): string {
    if (empty($_SESSION[CSRF_KEY])) $_SESSION[CSRF_KEY] = bin2hex(random_bytes(24));
    return $_SESSION[CSRF_KEY];
}
function csrf_field(): string {
    return '<input type="hidden" name="csrf" value="' . csrf_token() . '">';
}
function csrf_check(): void {
    if (($_POST['csrf'] ?? '') !== ($_SESSION[CSRF_KEY] ?? '-')) {
        http_response_code(419); die('CSRF token mismatch');
    }
}
