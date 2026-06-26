<?php
require_once __DIR__ . '/includes/auth.php';
require_once dirname(__DIR__) . '/includes/i18n.php';
portal_start();
$lang = current_lang();

// Already logged in
if (portal_user()) {
    header('Location: /portal/');
    exit;
}

$error = '';
$invite = null;

// Invite-link flow
if (!empty($_GET['token'])) {
    $invite = portal_validate_invite($_GET['token']);
    if (!$invite) $error = 'Inbjudningslänken är ogiltig eller har löpt ut.';
}

// Set-password via invite
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['set_password'])) {
    $token    = $_POST['token'] ?? '';
    $inv      = portal_validate_invite($token);
    $pw       = $_POST['password'] ?? '';
    $pw2      = $_POST['password2'] ?? '';
    if (!$inv)              $error = t('login.invalid_invite', 'Ogiltig inbjudan.');
    elseif (strlen($pw) < 8) $error = t('login.password_too_short', 'Lösenordet måste vara minst 8 tecken.');
    elseif ($pw !== $pw2)    $error = t('login.password_mismatch', 'Lösenorden matchar inte.');
    else {
        // Create or update portal user
        $existing = db()->prepare("SELECT id FROM portal_users WHERE customer_id = ?");
        $existing->execute([$inv['customer_id']]);
        $pu = $existing->fetch();
        $hash = password_hash($pw, PASSWORD_DEFAULT);
        if ($pu) {
            db()->prepare("UPDATE portal_users SET password_hash=?, email=?, active=1 WHERE id=?")->execute([$hash, $inv['email'], $pu['id']]);
        } else {
            db()->prepare("INSERT INTO portal_users (customer_id, email, password_hash) VALUES (?,?,?)")->execute([$inv['customer_id'], $inv['email'], $hash]);
        }
        // Mark invite used
        db()->prepare("UPDATE portal_invites SET used_at = datetime('now','localtime') WHERE token = ?")->execute([$token]);
        // Auto-login
        portal_login($inv['email'], $pw);
        header('Location: /portal/');
        exit;
    }
}

// Normal login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['set_password'])) {
    $emailAttempt = $_POST['email'] ?? '';
    if (!rate_limit_check('portal', $emailAttempt)) {
        $error = t('login.rate_limited');
        usleep(400000);
    } elseif (!portal_login($emailAttempt, $_POST['password'] ?? '')) {
        rate_limit_record('portal', $emailAttempt, false);
        $error = t('login.error');
        usleep(400000);
    } else {
        rate_limit_record('portal', $emailAttempt, true);
        $redir = preg_match('#^/portal/#', $_GET['redir'] ?? '') ? $_GET['redir'] : '/portal/';
        header('Location: ' . $redir);
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="<?= e($lang) ?>">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover">
<meta name="robots" content="noindex,nofollow">
<title><?= e(t('login.title')) ?> — M2 Kundportal</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="/portal/assets/portal.css">
<link rel="manifest" href="/portal/manifest.json">
<link rel="apple-touch-icon" href="/portal/assets/icons/icon-192.png">
<meta name="theme-color" content="#111318">
</head>
<body>
<div class="portal-login">
  <div class="portal-login__card">
    <div class="portal-login__logo">
      <div class="portal-login__logo-mark">m2</div>
      <div class="portal-login__logo-text">
        <strong>M2 Bygg Team</strong>
        <span><?= e(t('portal.badge')) ?></span>
      </div>
    </div>

    <div style="text-align:right;margin-bottom:8px"><?= lang_switcher_html(true) ?></div>

    <?php if ($error): ?>
    <div class="alert alert--error"><?= e($error) ?></div>
    <?php endif; ?>

    <?php if ($invite && !$error): ?>
    <!-- Set password for first-time invite -->
    <h2 style="margin-bottom:6px"><?= e(sprintf(t('login.welcome'), $invite['name'])) ?></h2>
    <p style="color:var(--steel);font-size:.875rem;margin-bottom:24px"><?= e(t('login.set_password')) ?></p>
    <form method="post">
      <input type="hidden" name="set_password" value="1">
      <input type="hidden" name="token" value="<?= e($invite['token']) ?>">
      <div class="form-group">
        <label class="form-label"><?= e(t('login.email')) ?></label>
        <input class="form-control" type="email" value="<?= e($invite['email']) ?>" disabled>
      </div>
      <div class="form-group">
        <label class="form-label"><?= e(t('login.choose_password', 'Välj lösenord')) ?></label>
        <input class="form-control" type="password" name="password" minlength="8" required autofocus placeholder="<?= e(t('login.min_chars', 'Minst 8 tecken')) ?>">
      </div>
      <div class="form-group">
        <label class="form-label"><?= e(t('login.confirm_password', 'Bekräfta lösenord')) ?></label>
        <input class="form-control" type="password" name="password2" required placeholder="<?= e(t('login.repeat_password', 'Upprepa lösenordet')) ?>">
      </div>
      <button type="submit" class="btn btn--primary btn--lg" style="width:100%"><?= e(t('login.activate')) ?></button>
    </form>

    <?php else: ?>
    <!-- Normal login -->
    <h2 style="margin-bottom:6px"><?= e(t('login.title')) ?></h2>
    <p style="color:var(--steel);font-size:.875rem;margin-bottom:24px"><?= e(t('login.portal_tagline')) ?></p>
    <form method="post">
      <div class="form-group">
        <label class="form-label"><?= e(t('login.email')) ?></label>
        <input class="form-control" type="email" name="email" required autofocus autocomplete="email">
      </div>
      <div class="form-group">
        <label class="form-label"><?= e(t('login.password')) ?></label>
        <input class="form-control" type="password" name="password" required autocomplete="current-password">
      </div>
      <button type="submit" class="btn btn--primary btn--lg" style="width:100%;margin-top:4px"><?= e(t('login.submit')) ?></button>
    </form>
    <p style="margin-top:16px;font-size:.8rem;text-align:center">
      <a href="/portal/forgot-password.php"><?= e(t('login.forgot_password', 'Glömt lösenord?')) ?></a>
    </p>
    <p style="margin-top:8px;font-size:.8rem;color:var(--steel);text-align:center">
      <?= e(t('login.no_account', 'Har du inget konto? Kontakta oss på')) ?> <a href="mailto:info@m2team.se">info@m2team.se</a>
    </p>
    <?php endif; ?>
  </div>
</div>
</body>
</html>
