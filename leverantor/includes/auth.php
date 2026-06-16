<?php
require_once __DIR__ . '/../../crm/includes/db.php';

function supp_start(): void {
    if (session_status() === PHP_SESSION_NONE) {
        session_name('m2supplier_session');
        session_set_cookie_params([
            'lifetime' => 0, 'path' => '/leverantor',
            'secure' => isset($_SERVER['HTTPS']),
            'httponly' => true, 'samesite' => 'Lax',
        ]);
        session_start();
    }
}

function supp_user(): ?array {
    supp_start();
    if (empty($_SESSION['supp_user_id'])) return null;
    static $u = null;
    if ($u) return $u;
    $s = db()->prepare(
        "SELECT su.*, s.company, s.contact, s.phone, s.specialty, s.email AS supplier_email
         FROM supplier_users su JOIN suppliers s ON s.id = su.supplier_id
         WHERE su.id = ? AND su.active = 1"
    );
    $s->execute([$_SESSION['supp_user_id']]);
    $u = $s->fetch() ?: null;
    return $u;
}

function supp_require(): array {
    $u = supp_user();
    if (!$u) {
        header('Location: /leverantor/login.php?redir=' . urlencode($_SERVER['REQUEST_URI']));
        exit;
    }
    return $u;
}

function supp_login(string $email, string $password): bool {
    supp_start();
    $s = db()->prepare("SELECT * FROM supplier_users WHERE email = ? AND active = 1");
    $s->execute([strtolower(trim($email))]);
    $su = $s->fetch();
    if (!$su || !password_verify($password, $su['password_hash'])) return false;
    $_SESSION['supp_user_id'] = $su['id'];
    db()->prepare("UPDATE supplier_users SET last_login = datetime('now','localtime') WHERE id = ?")
       ->execute([$su['id']]);
    return true;
}

function supp_logout(): void {
    supp_start();
    $_SESSION = [];
    session_destroy();
    header('Location: /leverantor/login.php');
    exit;
}

function supp_validate_invite(string $token): ?array {
    $s = db()->prepare(
        "SELECT si.*, s.company, s.email AS s_email
         FROM supplier_invites si JOIN suppliers s ON s.id = si.supplier_id
         WHERE si.token = ? AND si.used_at IS NULL AND si.expires_at > datetime('now','localtime')"
    );
    $s->execute([$token]);
    return $s->fetch() ?: null;
}

function e(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }
