<?php
require_once __DIR__ . '/includes/auth.php';
require_once dirname(__DIR__) . '/includes/i18n.php';
require_once dirname(__DIR__) . '/crm/includes/mailer.php';
portal_start();
$lang = current_lang();

if (portal_user()) { header('Location: /portal/'); exit; }

$sent = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = strtolower(trim($_POST['email'] ?? ''));
    if (!rate_limit_check('portal_reset', $email)) {
        usleep(400000);
    } elseif (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        rate_limit_record('portal_reset', $email, true);
        $u = db()->prepare("SELECT pu.*, c.name FROM portal_users pu JOIN customers c ON c.id = pu.customer_id WHERE pu.email = ? AND pu.active = 1");
        $u->execute([$email]);
        $user = $u->fetch();
        if ($user) {
            $token = create_password_reset_token('portal', (int)$user['id']);
            $resetUrl = (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . '/portal/reset-password.php?token=' . $token;
            crm_send_mail(
                $user['email'], $user['name'],
                'Återställ lösenord – M2 Kundportal',
                '<p>Hej ' . htmlspecialchars($user['name'], ENT_QUOTES, 'UTF-8') . '!</p><p>Vi fick en begäran om att återställa ditt lösenord till kundportalen. Klicka på knappen nedan för att välja ett nytt lösenord. Länken är giltig i 60 minuter.</p><p>Om du inte begärde detta kan du ignorera mejlet.</p>',
                'portal_user', (int)$user['id'], $resetUrl, 'Återställ lösenord'
            );
        }
    }
    $sent = true;
}
?>
<!DOCTYPE html>
<html lang="<?= e($lang) ?>">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover">
<meta name="robots" content="noindex,nofollow">
<title><?= e(t('login.forgot_title', 'Glömt lösenord')) ?> — M2 Kundportal</title>
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

    <?php if ($sent): ?>
      <div class="alert" style="background:rgba(5,150,105,.12);border:1px solid rgba(5,150,105,.3);color:#059669"><?= e(t('login.reset_sent', 'Om e-postadressen finns i systemet har vi skickat en länk för att återställa lösenordet. Kolla din inkorg.')) ?></div>
    <?php else: ?>
      <h2 style="margin-bottom:6px"><?= e(t('login.forgot_title', 'Glömt lösenord')) ?></h2>
      <p style="color:var(--steel);font-size:.875rem;margin-bottom:24px"><?= e(t('login.forgot_desc', 'Ange din e-postadress och vi skickar en länk för att återställa lösenordet.')) ?></p>
      <form method="post">
        <div class="form-group">
          <label class="form-label"><?= e(t('login.email')) ?></label>
          <input class="form-control" type="email" name="email" required autofocus autocomplete="email">
        </div>
        <button type="submit" class="btn btn--primary btn--lg" style="width:100%"><?= e(t('login.send_reset_link', 'Skicka återställningslänk')) ?></button>
      </form>
    <?php endif; ?>
    <p style="margin-top:20px;font-size:.8rem;color:var(--steel);text-align:center">
      <a href="/portal/login.php"><?= e(t('login.back_to_login', '← Tillbaka till inloggning')) ?></a>
    </p>
  </div>
</div>
</body>
</html>
