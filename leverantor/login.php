<?php
require_once __DIR__ . '/includes/auth.php';
require_once dirname(__DIR__) . '/includes/i18n.php';
supp_start();
$lang = current_lang();
if (supp_user()) { header('Location: /leverantor/'); exit; }

$error = ''; $invite = null;
if (!empty($_GET['token'])) {
    $invite = supp_validate_invite($_GET['token']);
    if (!$invite) $error = t('login.invalid_invite', 'Inbjudningslänken är ogiltig eller har löpt ut.');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['set_password'])) {
    $token = $_POST['token'] ?? '';
    $inv   = supp_validate_invite($token);
    $pw    = $_POST['password'] ?? '';
    $pw2   = $_POST['password2'] ?? '';
    if (!$inv)               $error = t('login.invalid_invite2', 'Ogiltig inbjudan.');
    elseif (strlen($pw) < 8) $error = t('login.password_too_short', 'Lösenordet måste vara minst 8 tecken.');
    elseif ($pw !== $pw2)    $error = t('login.password_mismatch', 'Lösenorden matchar inte.');
    else {
        $hash = password_hash($pw, PASSWORD_DEFAULT);
        $ex = db()->prepare("SELECT id FROM supplier_users WHERE supplier_id = ?");
        $ex->execute([$inv['supplier_id']]);
        $existing = $ex->fetch();
        if ($existing) {
            db()->prepare("UPDATE supplier_users SET password_hash=?,email=?,active=1 WHERE id=?")->execute([$hash,$inv['email'],$existing['id']]);
        } else {
            db()->prepare("INSERT INTO supplier_users (supplier_id,email,password_hash) VALUES (?,?,?)")->execute([$inv['supplier_id'],$inv['email'],$hash]);
        }
        db()->prepare("UPDATE supplier_invites SET used_at=datetime('now','localtime') WHERE token=?")->execute([$token]);
        supp_login($inv['email'], $pw);
        header('Location: /leverantor/'); exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['set_password'])) {
    $emailAttempt = $_POST['email'] ?? '';
    if (!rate_limit_check('leverantor', $emailAttempt)) {
        $error = t('login.rate_limited');
        usleep(400000);
    } elseif (!supp_login($emailAttempt, $_POST['password'] ?? '')) {
        rate_limit_record('leverantor', $emailAttempt, false);
        $error = t('login.error');
        usleep(400000);
    } else {
        rate_limit_record('leverantor', $emailAttempt, true);
        header('Location: /leverantor/'); exit;
    }
}
?>
<!DOCTYPE html>
<html lang="<?= e($lang) ?>">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover">
<meta name="robots" content="noindex,nofollow">
<title><?= e(t('login.title')) ?> — M2 Leverantörsportal</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="/leverantor/assets/portal.css">
<link rel="manifest" href="/leverantor/manifest.json">
<link rel="apple-touch-icon" href="/leverantor/assets/icons/icon-192.png">
<meta name="theme-color" content="#1e3a8a">
</head>
<body>
<div class="portal-login" style="background:#1e3a8a">
  <div class="portal-login__card">
    <div class="portal-login__logo">
      <div class="portal-login__logo-mark" style="background:#3b82f6;color:#fff">m2</div>
      <div class="portal-login__logo-text">
        <strong>M2 Bygg Team</strong>
        <span><?= e(t('supplier.badge')) ?></span>
      </div>
    </div>
    <div style="text-align:right;margin-bottom:8px"><?= lang_switcher_html(true) ?></div>
    <?php if ($error): ?><div class="alert alert--error"><?= e($error) ?></div><?php endif; ?>
    <?php if ($invite && !$error): ?>
    <h2 style="margin-bottom:6px"><?= e(sprintf(t('login.welcome'), $invite['company'])) ?></h2>
    <p style="color:var(--steel);font-size:.875rem;margin-bottom:24px"><?= e(t('login.set_password_supplier', 'Skapa ett lösenord för att aktivera ditt leverantörskonto.')) ?></p>
    <form method="post">
      <input type="hidden" name="set_password" value="1">
      <input type="hidden" name="token" value="<?= e($invite['token']) ?>">
      <div class="form-group">
        <label class="form-label"><?= e(t('login.email')) ?></label>
        <input class="form-control" type="email" value="<?= e($invite['email']) ?>" disabled>
      </div>
      <div class="form-group">
        <label class="form-label"><?= e(t('login.choose_password', 'Välj lösenord')) ?></label>
        <input class="form-control" type="password" name="password" minlength="8" required autofocus>
      </div>
      <div class="form-group">
        <label class="form-label"><?= e(t('login.confirm_password', 'Bekräfta lösenord')) ?></label>
        <input class="form-control" type="password" name="password2" required>
      </div>
      <button type="submit" class="btn btn--primary btn--lg" style="width:100%"><?= e(t('login.activate')) ?></button>
    </form>
    <?php else: ?>
    <h2 style="margin-bottom:6px"><?= e(t('login.title')) ?></h2>
    <p style="color:var(--steel);font-size:.875rem;margin-bottom:24px"><?= e(t('login.supplier_tagline')) ?></p>
    <form method="post">
      <div class="form-group"><label class="form-label"><?= e(t('login.email')) ?></label><input class="form-control" type="email" name="email" required autofocus autocomplete="email"></div>
      <div class="form-group"><label class="form-label"><?= e(t('login.password')) ?></label><input class="form-control" type="password" name="password" required autocomplete="current-password"></div>
      <button type="submit" class="btn btn--primary btn--lg" style="width:100%;margin-top:4px"><?= e(t('login.submit')) ?></button>
    </form>
    <p style="margin-top:16px;font-size:.8rem;text-align:center">
      <a href="/leverantor/forgot-password.php"><?= e(t('login.forgot_password', 'Glömt lösenord?')) ?></a>
    </p>
    <?php endif; ?>
  </div>
</div>
</body>
</html>
