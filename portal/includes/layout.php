<?php
/**
 * Portal layout helpers — call portal_head() and portal_nav() at top of each page,
 * portal_foot() at bottom.
 */
function portal_head(string $title, array $pu): void { ?>
<!DOCTYPE html>
<html lang="sv">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="robots" content="noindex,nofollow">
<title><?= htmlspecialchars($title) ?> — M2 Kundportal</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="/portal/assets/portal.css">
</head>
<body>
<header class="portal-topbar">
  <a href="/portal/" class="portal-topbar__logo">
    <div class="portal-topbar__logo-mark">m2</div>
    M2 Bygg Team
    <span class="portal-topbar__badge">Kundportal</span>
  </a>
  <div class="portal-topbar__spacer"></div>
  <div class="portal-topbar__user">
    Inloggad som <strong><?= htmlspecialchars($pu['name']) ?></strong>
  </div>
  <a href="/portal/logout.php" class="portal-topbar__logout">Logga ut</a>
</header>
<?php }

function portal_nav(string $active): void {
    $links = [
        ['/', 'Dashboard', '<path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/>'],
        ['/projekt.php', 'Projekt', '<rect x="2" y="3" width="20" height="14" rx="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/>'],
        ['/offerter.php', 'Offerter', '<path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14,2 14,8 20,8"/>'],
        ['/fakturor.php', 'Fakturor', '<rect x="1" y="4" width="22" height="16" rx="2" ry="2"/><line x1="1" y1="10" x2="23" y2="10"/>'],
        ['/dokument.php', 'Dokument', '<path d="M22 19a2 2 0 01-2 2H4a2 2 0 01-2-2V5a2 2 0 012-2h5l2 3h9a2 2 0 012 2z"/>'],
        ['/meddelanden.php', 'Meddelanden', '<path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"/>'],
    ];
    echo '<nav class="portal-nav">';
    foreach ($links as [$href, $label, $icon]) {
        $isActive = $active === $href ? ' active' : '';
        echo "<a href=\"/portal{$href}\" class=\"{$isActive}\"><svg viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"currentColor\" stroke-width=\"1.8\"><{$icon}</svg>{$label}</a>";
    }
    echo '</nav>';
}

function portal_foot(): void { ?>
</body>
</html>
<?php }
