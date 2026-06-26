<?php
/**
 * Customer Portal — Auth helpers
 */
require_once __DIR__ . '/../../crm/includes/db.php';

function portal_start(): void {
    if (session_status() === PHP_SESSION_NONE) {
        session_name('m2portal_session');
        session_set_cookie_params([
            // Path is site-wide (not just /portal) so a customer's portal login is also
            // recognized on /blogg for commenting. Still httponly + samesite=Lax, so this
            // doesn't expose the session token to scripts or cross-site requests.
            'lifetime' => 0, 'path' => '/',
            'secure' => isset($_SERVER['HTTPS']),
            'httponly' => true, 'samesite' => 'Lax',
        ]);
        session_start();
    }
}

function portal_user(): ?array {
    portal_start();
    if (empty($_SESSION['portal_user_id'])) return null;
    static $u = null;
    if ($u) return $u;
    $s = db()->prepare(
        "SELECT pu.*, c.name, c.phone, c.address, c.city
         FROM portal_users pu JOIN customers c ON c.id = pu.customer_id
         WHERE pu.id = ? AND pu.active = 1"
    );
    $s->execute([$_SESSION['portal_user_id']]);
    $u = $s->fetch() ?: null;
    return $u;
}

function portal_require(): array {
    $u = portal_user();
    if (!$u) {
        header('Location: /portal/login.php?redir=' . urlencode($_SERVER['REQUEST_URI']));
        exit;
    }
    return $u;
}

function portal_login(string $email, string $password): bool {
    portal_start();
    $s = db()->prepare("SELECT * FROM portal_users WHERE email = ? AND active = 1");
    $s->execute([strtolower(trim($email))]);
    $pu = $s->fetch();
    if (!$pu || !password_verify($password, $pu['password_hash'])) return false;
    $_SESSION['portal_user_id'] = $pu['id'];
    db()->prepare("UPDATE portal_users SET last_login = datetime('now','localtime') WHERE id = ?")
       ->execute([$pu['id']]);
    return true;
}

function portal_logout(): void {
    portal_start();
    $_SESSION = [];
    session_destroy();
    header('Location: /portal/login.php');
    exit;
}

function portal_projects(int $customerId): array {
    $s = db()->prepare(
        "SELECT p.*, q.total AS quote_total, q.status AS quote_status, q.id AS qid
         FROM projects p
         LEFT JOIN quotes q ON q.id = p.quote_id
         WHERE p.customer_id = ? ORDER BY p.created_at DESC"
    );
    $s->execute([$customerId]);
    return $s->fetchAll();
}

function portal_status_label(string $status): string {
    return PROJECT_STATUSES[$status]['label'] ?? ucfirst($status);
}

function portal_status_pct(string $status): int {
    $map = ['lead'=>5,'inspection'=>15,'planning'=>25,'scheduled'=>40,
            'in_progress'=>60,'quality'=>85,'completed'=>100,'closed'=>100];
    return $map[$status] ?? 0;
}

/** Validate invite token and return customer data, or null if invalid */
function portal_validate_invite(string $token): ?array {
    $s = db()->prepare(
        "SELECT pi.*, c.name, c.email AS c_email
         FROM portal_invites pi JOIN customers c ON c.id = pi.customer_id
         WHERE pi.token = ? AND pi.used_at IS NULL AND pi.expires_at > datetime('now','localtime')"
    );
    $s->execute([$token]);
    return $s->fetch() ?: null;
}

if (!function_exists('e')) {
    function e(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }
}
