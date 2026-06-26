<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/mailer.php';

if (current_user()) { header('Location: index.php'); exit; }

$sent = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = strtolower(trim($_POST['email'] ?? ''));
    if (!rate_limit_check('crm_reset', $email)) {
        usleep(400000);
    } elseif (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        rate_limit_record('crm_reset', $email, true);
        $u = db()->prepare("SELECT * FROM users WHERE email = ? AND active = 1");
        $u->execute([$email]);
        $user = $u->fetch();
        // Always show the same success message whether or not the email exists —
        // prevents enumerating valid accounts via this form.
        if ($user) {
            $token = create_password_reset_token('crm', (int)$user['id']);
            $resetUrl = (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . '/crm/reset-password.php?token=' . $token;
            crm_send_mail(
                $user['email'], $user['name'],
                'Återställ lösenord – M2 Platform',
                '<p>Hej ' . htmlspecialchars($user['name'], ENT_QUOTES, 'UTF-8') . '!</p><p>Vi fick en begäran om att återställa ditt lösenord till M2 Platform. Klicka på knappen nedan för att välja ett nytt lösenord. Länken är giltig i 60 minuter.</p><p>Om du inte begärde detta kan du ignorera mejlet — ditt lösenord ändras inte.</p>',
                'user', (int)$user['id'], $resetUrl, 'Återställ lösenord'
            );
        }
    }
    $sent = true;
}
?>
<!DOCTYPE html>
<html lang="sv">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="robots" content="noindex,nofollow">
<title>Glömt lösenord – M2 Platform</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
body{font-family:"SF Pro Text",-apple-system,BlinkMacSystemFont,"Inter",sans-serif;background:#0B1220;min-height:100vh;display:flex;align-items:center;justify-content:center;padding:24px;-webkit-font-smoothing:antialiased}
.login{width:100%;max-width:380px;text-align:center}
.logo{font-family:"SF Pro Display",-apple-system,"Inter",sans-serif;font-size:64px;font-weight:800;letter-spacing:-4px;color:#fff;line-height:1;margin-bottom:4px}
.logo span{color:#0066FF}
.brand{font-size:13px;font-weight:600;letter-spacing:.35em;text-transform:uppercase;color:rgba(255,255,255,.85);margin-bottom:8px}
.tagline{font-size:14px;color:rgba(255,255,255,.45);margin-bottom:40px}
.card{background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.09);border-radius:20px;padding:28px 26px;backdrop-filter:blur(20px);text-align:left}
.fg{margin-bottom:14px}
.fg label{display:block;font-size:11px;font-weight:600;letter-spacing:.08em;text-transform:uppercase;color:rgba(255,255,255,.4);margin-bottom:6px}
.fg input{width:100%;background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.12);border-radius:11px;padding:12px 15px;color:#fff;font-size:15px;outline:none;transition:border-color .18s}
.fg input:focus{border-color:#0066FF}
.fg input::placeholder{color:rgba(255,255,255,.25)}
.btn{width:100%;background:#0066FF;color:#fff;font-size:15px;font-weight:600;padding:13px;border:none;border-radius:11px;cursor:pointer;transition:background .18s;margin-top:6px}
.btn:hover{background:#0052CC}
.ok{background:rgba(5,150,105,.12);border:1px solid rgba(5,150,105,.3);color:#6EE7B7;font-size:13px;padding:12px 14px;border-radius:10px;text-align:left;line-height:1.5}
.back{display:block;margin-top:20px;font-size:13px;color:rgba(255,255,255,.45);text-decoration:none}
.back:hover{color:#fff}
p.desc{font-size:13px;color:rgba(255,255,255,.45);margin-bottom:18px;line-height:1.5}
</style>
</head>
<body>
<div class="login">
  <div class="logo">m<span>2</span></div>
  <div class="brand">Bygg Team</div>
  <p class="tagline">Trygga byggtjänster till <em>fast pris</em></p>
  <div class="card">
    <?php if ($sent): ?>
      <div class="ok">Om e-postadressen finns i systemet har vi skickat en länk för att återställa lösenordet. Kolla din inkorg.</div>
    <?php else: ?>
      <p class="desc">Ange din e-postadress och vi skickar en länk för att återställa lösenordet.</p>
      <form method="POST">
        <div class="fg">
          <label for="email">E-post</label>
          <input type="email" id="email" name="email" placeholder="din@m2team.se" required autofocus>
        </div>
        <button type="submit" class="btn">Skicka återställningslänk</button>
      </form>
    <?php endif; ?>
  </div>
  <a href="login.php" class="back">← Tillbaka till inloggning</a>
</div>
</body>
</html>
