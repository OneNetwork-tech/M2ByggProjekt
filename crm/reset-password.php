<?php
require_once __DIR__ . '/includes/auth.php';

$token = $_GET['token'] ?? $_POST['token'] ?? '';
$reset = find_password_reset('crm', $token);
$error = $reset ? '' : 'Länken är ogiltig eller har löpt ut. Begär en ny återställningslänk.';
$done  = false;

if ($reset && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $pw  = $_POST['password'] ?? '';
    $pw2 = $_POST['password2'] ?? '';
    if (strlen($pw) < 8) {
        $error = 'Lösenordet måste vara minst 8 tecken.';
    } elseif ($pw !== $pw2) {
        $error = 'Lösenorden matchar inte.';
    } else {
        db()->prepare("UPDATE users SET password_hash = ? WHERE id = ?")
            ->execute([password_hash($pw, PASSWORD_DEFAULT), $reset['user_id']]);
        consume_password_reset((int)$reset['id']);
        audit('password_reset', 'user', (int)$reset['user_id']);
        $done = true;
    }
}
?>
<!DOCTYPE html>
<html lang="sv">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="robots" content="noindex,nofollow">
<title>Återställ lösenord – M2 Platform</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
body{font-family:"SF Pro Text",-apple-system,BlinkMacSystemFont,"Inter",sans-serif;background:#0B1220;min-height:100vh;display:flex;align-items:center;justify-content:center;padding:24px;-webkit-font-smoothing:antialiased}
.login{width:100%;max-width:380px;text-align:center}
.logo{margin-bottom:4px}
.logo img{height:72px;width:auto}
.brand{font-size:13px;font-weight:600;letter-spacing:.35em;text-transform:uppercase;color:rgba(255,255,255,.85);margin-bottom:8px}
.tagline{font-size:14px;color:rgba(255,255,255,.45);margin-bottom:40px}
.card{background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.09);border-radius:20px;padding:28px 26px;backdrop-filter:blur(20px);text-align:left}
.fg{margin-bottom:14px}
.fg label{display:block;font-size:11px;font-weight:600;letter-spacing:.08em;text-transform:uppercase;color:rgba(255,255,255,.4);margin-bottom:6px}
.fg input{width:100%;background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.12);border-radius:11px;padding:12px 15px;color:#fff;font-size:15px;outline:none;transition:border-color .18s}
.fg input:focus{border-color:#0066FF}
.btn{width:100%;background:#0066FF;color:#fff;font-size:15px;font-weight:600;padding:13px;border:none;border-radius:11px;cursor:pointer;transition:background .18s;margin-top:6px}
.btn:hover{background:#0052CC}
.err{background:rgba(220,38,38,.12);border:1px solid rgba(220,38,38,.3);color:#FCA5A5;font-size:13px;padding:10px 14px;border-radius:10px;margin-bottom:14px}
.ok{background:rgba(5,150,105,.12);border:1px solid rgba(5,150,105,.3);color:#6EE7B7;font-size:13px;padding:12px 14px;border-radius:10px;text-align:left;line-height:1.5}
.back{display:block;margin-top:20px;font-size:13px;color:rgba(255,255,255,.45);text-decoration:none}
.back:hover{color:#fff}
</style>
</head>
<body>
<div class="login">
  <div class="logo"><img src="/assets/images/M2-logotyp-wht.svg" alt="M2"></div>
  <div class="brand">Bygg Team</div>
  <p class="tagline">Trygga byggtjänster till <em>fast pris</em></p>
  <div class="card">
    <?php if ($done): ?>
      <div class="ok">Lösenordet är uppdaterat. Du kan nu logga in med ditt nya lösenord.</div>
    <?php elseif ($error && !$reset): ?>
      <div class="err"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
    <?php else: ?>
      <?php if ($error): ?><div class="err"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
      <form method="POST">
        <input type="hidden" name="token" value="<?= htmlspecialchars($token, ENT_QUOTES, 'UTF-8') ?>">
        <div class="fg">
          <label for="password">Nytt lösenord</label>
          <input type="password" id="password" name="password" placeholder="Minst 8 tecken" minlength="8" required autofocus>
        </div>
        <div class="fg">
          <label for="password2">Bekräfta lösenord</label>
          <input type="password" id="password2" name="password2" placeholder="Upprepa lösenordet" minlength="8" required>
        </div>
        <button type="submit" class="btn">Spara nytt lösenord</button>
      </form>
    <?php endif; ?>
  </div>
  <a href="login.php" class="back">← Tillbaka till inloggning</a>
</div>
</body>
</html>
