<?php
/**
 * Custom 500 error page (Apache ErrorDocument 500 /500.php).
 * Deliberately standalone — no includes/header.php, no db.php — because a database
 * or include failure could be the very thing that caused the 500 in the first place.
 */
http_response_code(500);
?>
<!DOCTYPE html>
<html lang="sv">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Ett fel uppstod – M2 Bygg Team AB</title>
<meta name="robots" content="noindex">
<style>
  body{margin:0;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif;background:#FAF7F2;color:#1D1D1F;min-height:100vh;display:flex;align-items:center;justify-content:center;text-align:center;padding:24px}
  .wrap{max-width:480px}
  .code{font-size:clamp(4rem,16vw,8rem);font-weight:700;letter-spacing:-0.04em;line-height:1;margin-bottom:8px}
  .code em{font-style:normal;color:#B5712A}
  h1{font-size:1.5rem;margin:0 0 12px}
  p{color:#52525B;line-height:1.6;margin:0 0 28px}
  .btn{display:inline-block;background:#1D1D1F;color:#fff;text-decoration:none;padding:12px 24px;border-radius:980px;font-size:14px;font-weight:600}
</style>
</head>
<body>
  <div class="wrap">
    <div class="code">5<em>0</em>0</div>
    <h1>Något gick fel</h1>
    <p>Ett tekniskt fel uppstod på vår sida. Vi har blivit notifierade. Försök igen om en liten stund, eller kontakta oss direkt.</p>
    <a href="/" class="btn">Tillbaka till startsidan</a>
    <p style="margin-top:24px;font-size:13px">031-96 88 88 · info@m2team.se</p>
  </div>
</body>
</html>
