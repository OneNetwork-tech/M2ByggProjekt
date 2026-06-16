<?php
function supp_head(string $title, array $su): void { ?>
<!DOCTYPE html>
<html lang="sv">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="robots" content="noindex,nofollow">
<title><?= htmlspecialchars($title) ?> — M2 Leverantörsportal</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="/leverantor/assets/portal.css">
</head>
<body>
<header class="portal-topbar" style="background:#1e3a8a">
  <a href="/leverantor/" class="portal-topbar__logo">
    <div class="portal-topbar__logo-mark" style="background:#3b82f6;color:#fff">m2</div>
    M2 Bygg Team
    <span class="portal-topbar__badge">Leverantörsportal</span>
  </a>
  <div class="portal-topbar__spacer"></div>
  <div class="portal-topbar__user">
    Inloggad som <strong><?= htmlspecialchars($su['company']) ?></strong>
  </div>
  <a href="/leverantor/logout.php" class="portal-topbar__logout">Logga ut</a>
</header>
<?php }

function supp_nav(string $active): void {
    $links = [
        ['/', 'Dashboard', '<path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/>'],
        ['/jobb.php', 'Jobberbjudanden', '<path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h11"/>'],
        ['/tidrapport.php', 'Tidrapporter', '<circle cx="12" cy="12" r="10"/><polyline points="12,6 12,12 16,14"/>'],
        ['/betalningar.php', 'Betalningar', '<rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/>'],
        ['/dokument.php', 'Dokument', '<path d="M22 19a2 2 0 01-2 2H4a2 2 0 01-2-2V5a2 2 0 012-2h5l2 3h9a2 2 0 012 2z"/>'],
    ];
    echo '<nav class="portal-nav">';
    foreach ($links as [$href, $label, $icon]) {
        $isActive = $active === $href ? ' active' : '';
        echo "<a href=\"/leverantor{$href}\" class=\"{$isActive}\"><svg viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"currentColor\" stroke-width=\"1.8\"><{$icon}</svg>{$label}</a>";
    }
    echo '</nav>';
}

function supp_foot(): void { ?>
</body></html>
<?php }
