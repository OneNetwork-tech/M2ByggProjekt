<?php
require_once __DIR__ . '/includes/auth.php';
require_once dirname(__DIR__) . '/includes/i18n.php';
portal_start();
$lang = current_lang();

$token = $_GET['token'] ?? $_POST['token'] ?? '';
$reset = find_password_reset('portal', $token);
$error = $reset ? '' : t('login.invalid_reset', 'Länken är ogiltig eller har löpt ut. Begär en ny återställningslänk.');
$done  = false;

if ($reset && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $pw  = $_POST['password'] ?? '';
    $pw2 = $_POST['password2'] ?? '';
    if (strlen($pw) < 8) {
        $error = t('login.password_too_short', 'Lösenordet måste vara minst 8 tecken.');
    } elseif ($pw !== $pw2) {
        $error = t('login.password_mismatch', 'Lösenorden matchar inte.');
    } else {
        db()->prepare("UPDATE portal_users SET password_hash = ? WHERE id = ?")
            ->execute([password_hash($pw, PASSWORD_DEFAULT), $reset['user_id']]);
        consume_password_reset((int)$reset['id']);
        audit('password_reset', 'portal_user', (int)$reset['user_id']);
        $done = true;
    }
}
?>
<!DOCTYPE html>
<html lang="<?= e($lang) ?>">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover">
<meta name="robots" content="noindex,nofollow">
<title><?= e(t('login.reset_title', 'Återställ lösenord')) ?> — M2 Kundportal</title>
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

    <?php if ($done): ?>
      <div class="alert" style="background:rgba(5,150,105,.12);border:1px solid rgba(5,150,105,.3);color:#059669"><?= e(t('login.reset_done', 'Lösenordet är uppdaterat. Du kan nu logga in med ditt nya lösenord.')) ?></div>
    <?php elseif ($error && !$reset): ?>
      <div class="alert alert--error"><?= e($error) ?></div>
    <?php else: ?>
      <?php if ($error): ?><div class="alert alert--error"><?= e($error) ?></div><?php endif; ?>
      <h2 style="margin-bottom:6px"><?= e(t('login.reset_title', 'Återställ lösenord')) ?></h2>
      <form method="post" style="margin-top:18px">
        <input type="hidden" name="token" value="<?= e($token) ?>">
        <div class="form-group">
          <label class="form-label"><?= e(t('login.choose_password', 'Välj lösenord')) ?></label>
          <input class="form-control" type="password" name="password" minlength="8" required autofocus placeholder="<?= e(t('login.min_chars', 'Minst 8 tecken')) ?>">
        </div>
        <div class="form-group">
          <label class="form-label"><?= e(t('login.confirm_password', 'Bekräfta lösenord')) ?></label>
          <input class="form-control" type="password" name="password2" required placeholder="<?= e(t('login.repeat_password', 'Upprepa lösenordet')) ?>">
        </div>
        <button type="submit" class="btn btn--primary btn--lg" style="width:100%"><?= e(t('login.save_password', 'Spara nytt lösenord')) ?></button>
      </form>
    <?php endif; ?>
    <p style="margin-top:20px;font-size:.8rem;color:var(--steel);text-align:center">
      <a href="/portal/login.php"><?= e(t('login.back_to_login', '← Tillbaka till inloggning')) ?></a>
    </p>
  </div>
</div>
</body>
</html>
