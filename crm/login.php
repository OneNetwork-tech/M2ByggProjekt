<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/helpers.php';

if (current_user()) { header('Location: index.php'); exit; }

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $emailAttempt = $_POST['email'] ?? '';
    if (!rate_limit_check('crm', $emailAttempt)) {
        $error = 'För många felaktiga inloggningsförsök. Försök igen om 15 minuter.';
        usleep(400000);
    } elseif (attempt_login($emailAttempt, $_POST['password'] ?? '')) {
        rate_limit_record('crm', $emailAttempt, true);
        header('Location: index.php'); exit;
    } else {
        rate_limit_record('crm', $emailAttempt, false);
        $error = 'Fel e-post eller lösenord.';
        usleep(400000); // slow brute force
    }
}
?>
<!DOCTYPE html>
<html lang="sv">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="robots" content="noindex,nofollow">
<title>Logga in – M2 Platform</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
body{
  font-family:"SF Pro Text",-apple-system,BlinkMacSystemFont,"Inter",sans-serif;
  background:#0B1220;
  min-height:100vh;
  display:flex;align-items:center;justify-content:center;
  padding:24px;
  -webkit-font-smoothing:antialiased;
}
.login{width:100%;max-width:380px;text-align:center}
.logo{margin-bottom:4px}
.logo img{height:72px;width:auto}
.brand{font-size:13px;font-weight:600;letter-spacing:.35em;text-transform:uppercase;color:rgba(255,255,255,.85);margin-bottom:8px}
.tagline{font-size:14px;color:rgba(255,255,255,.45);margin-bottom:40px}
.tagline em{font-style:normal;color:#3385FF}
.card{
  background:rgba(255,255,255,.04);
  border:1px solid rgba(255,255,255,.09);
  border-radius:20px;
  padding:28px 26px;
  backdrop-filter:blur(20px);
  text-align:left;
}
.fg{margin-bottom:14px}
.fg label{display:block;font-size:11px;font-weight:600;letter-spacing:.08em;text-transform:uppercase;color:rgba(255,255,255,.4);margin-bottom:6px}
.fg input{
  width:100%;background:rgba(255,255,255,.06);
  border:1px solid rgba(255,255,255,.12);border-radius:11px;
  padding:12px 15px;color:#fff;font-size:15px;outline:none;
  transition:border-color .18s;
}
.fg input:focus{border-color:#0066FF}
.fg input::placeholder{color:rgba(255,255,255,.25)}
.btn{
  width:100%;background:#0066FF;color:#fff;
  font-size:15px;font-weight:600;
  padding:13px;border:none;border-radius:11px;cursor:pointer;
  transition:background .18s;margin-top:6px;
}
.btn:hover{background:#0052CC}
.err{background:rgba(220,38,38,.12);border:1px solid rgba(220,38,38,.3);color:#FCA5A5;font-size:13px;padding:10px 14px;border-radius:10px;margin-bottom:14px}
.foot{margin-top:24px;font-size:12px;color:rgba(255,255,255,.3);display:flex;align-items:center;justify-content:center;gap:6px}
.foot svg{width:13px;height:13px;color:#0066FF}
</style>
</head>
<body>
<div class="login">
  <div class="logo"><img src="/assets/images/M2-logotyp-wht.svg" alt="M2"></div>
  <div class="brand">Bygg Team</div>
  <p class="tagline">Trygga byggtjänster till <em>fast pris</em>.</p>
  <div class="card">
    <?php if ($error): ?><div class="err"><?= e($error) ?></div><?php endif; ?>
    <form method="POST" autocomplete="on">
      <div class="fg">
        <label for="email">E-post</label>
        <input type="email" id="email" name="email" placeholder="din@m2team.se" required autofocus autocomplete="username">
      </div>
      <div class="fg">
        <label for="password">Lösenord</label>
        <input type="password" id="password" name="password" placeholder="••••••••" required autocomplete="current-password">
      </div>
      <button type="submit" class="btn">Logga in</button>
    </form>
    <a href="forgot-password.php" style="display:block;text-align:center;margin-top:16px;font-size:13px;color:rgba(255,255,255,.45);text-decoration:none">Glömt lösenord?</a>
  </div>
  <div class="foot">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
    Kvalitet. Trygghet. Fast pris.
  </div>
</div>
</body>
</html>
