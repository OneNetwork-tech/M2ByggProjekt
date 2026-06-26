<?php
/**
 * Portal layout helpers — call portal_head() and portal_nav() at top of each page,
 * portal_foot() at bottom.
 */
require_once dirname(__DIR__, 2) . '/includes/i18n.php';

function portal_head(string $title, array $pu): void {
    $lang = current_lang(); ?>
<!DOCTYPE html>
<html lang="<?= e($lang) ?>">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover">
<meta name="robots" content="noindex,nofollow">
<title><?= htmlspecialchars($title) ?> — M2 Kundportal</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="/portal/assets/portal.css">
<link rel="manifest" href="/portal/manifest.json">
<link rel="apple-touch-icon" href="/portal/assets/icons/icon-192.png">
<meta name="theme-color" content="#111318">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
<script>
if ('serviceWorker' in navigator) {
  window.addEventListener('load', () => navigator.serviceWorker.register('/portal/sw.js').catch(() => {}));
}
</script>
</head>
<body>
<header class="portal-topbar">
  <a href="/portal/" class="portal-topbar__logo">
    <div class="portal-topbar__logo-mark">m2</div>
    M2 Bygg Team
    <span class="portal-topbar__badge"><?= e(t('portal.badge')) ?></span>
  </a>
  <div class="portal-topbar__spacer"></div>
  <div class="portal-topbar__user">
    <?= e(t('logged_in_as')) ?> <strong><?= htmlspecialchars($pu['name']) ?></strong>
  </div>
  <?= lang_switcher_html() ?>
  <a href="/portal/logout.php" class="portal-topbar__logout"><?= e(t('logout')) ?></a>
</header>
<?php }

function portal_nav(string $active): void {
    $links = [
        ['/', t('nav.dashboard'), '<path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/>'],
        ['/projekt.php', t('nav.projects'), '<rect x="2" y="3" width="20" height="14" rx="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/>'],
        ['/offerter.php', t('nav.quotes'), '<path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14,2 14,8 20,8"/>'],
        ['/fakturor.php', t('nav.invoices'), '<rect x="1" y="4" width="22" height="16" rx="2" ry="2"/><line x1="1" y1="10" x2="23" y2="10"/>'],
        ['/dokument.php', t('nav.documents'), '<path d="M22 19a2 2 0 01-2 2H4a2 2 0 01-2-2V5a2 2 0 012-2h5l2 3h9a2 2 0 012 2z"/>'],
        ['/meddelanden.php', t('nav.messages'), '<path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"/>'],
        ['/installningar.php', t('nav.settings'), '<circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 00.33 1.82l.06.06a2 2 0 11-2.83 2.83l-.06-.06a1.65 1.65 0 00-1.82-.33 1.65 1.65 0 00-1 1.51V21a2 2 0 11-4 0v-.09A1.65 1.65 0 008 19.4a1.65 1.65 0 00-1.82.33l-.06.06a2 2 0 11-2.83-2.83l.06-.06a1.65 1.65 0 00.33-1.82 1.65 1.65 0 00-1.51-1H2a2 2 0 110-4h.09A1.65 1.65 0 003.6 8a1.65 1.65 0 00-.33-1.82l-.06-.06a2 2 0 112.83-2.83l.06.06a1.65 1.65 0 001.82.33H8a1.65 1.65 0 001-1.51V2a2 2 0 114 0v.09a1.65 1.65 0 001 1.51 1.65 1.65 0 001.82-.33l.06-.06a2 2 0 112.83 2.83l-.06.06a1.65 1.65 0 00-.33 1.82V8a1.65 1.65 0 001.51 1H21a2 2 0 110 4h-.09a1.65 1.65 0 00-1.51 1z"/>'],
    ];
    echo '<nav class="portal-nav">';
    foreach ($links as [$href, $label, $icon]) {
        $isActive = $active === $href ? ' active' : '';
        echo "<a href=\"/portal{$href}\" class=\"{$isActive}\"><svg viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"currentColor\" stroke-width=\"1.8\"><{$icon}</svg>" . htmlspecialchars($label) . "</a>";
    }
    echo '</nav>';
}

function portal_foot(): void { ?>
</body>
</html>
<?php }
