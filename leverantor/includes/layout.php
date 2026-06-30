<?php
require_once dirname(__DIR__, 2) . '/includes/i18n.php';

function supp_head(string $title, array $su): void {
    $lang = current_lang(); ?>
<!DOCTYPE html>
<html lang="<?= e($lang) ?>">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover">
<meta name="robots" content="noindex,nofollow">
<title><?= htmlspecialchars($title) ?> — M2 Leverantörsportal</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="/leverantor/assets/portal.css">
<link rel="manifest" href="/leverantor/manifest.json">
<link rel="apple-touch-icon" href="/leverantor/assets/icons/icon-192.png">
<meta name="theme-color" content="#1e3a8a">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
<script>
if ('serviceWorker' in navigator) {
  window.addEventListener('load', () => navigator.serviceWorker.register('/leverantor/sw.js').catch(() => {}));
}
</script>
</head>
<body>
<header class="portal-topbar" style="background:#1e3a8a">
  <a href="/leverantor/" class="portal-topbar__logo">
    <img class="portal-topbar__logo-mark" src="/assets/images/M2-symbol-wht.svg" alt="M2">
    M2 Bygg Team
    <span class="portal-topbar__badge"><?= e(t('supplier.badge')) ?></span>
  </a>
  <div class="portal-topbar__spacer"></div>
  <div class="portal-topbar__user">
    <?= e(t('logged_in_as')) ?> <strong><?= htmlspecialchars($su['company']) ?></strong>
  </div>
  <?= lang_switcher_html() ?>
  <a href="/leverantor/logout.php" class="portal-topbar__logout"><?= e(t('logout')) ?></a>
</header>
<?php }

function supp_nav(string $active): void {
    $links = [
        ['/', t('nav.dashboard'), '<path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/>'],
        ['/jobb.php', t('nav.jobs'), '<path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h11"/>'],
        ['/tidrapport.php', t('nav.time_reports'), '<circle cx="12" cy="12" r="10"/><polyline points="12,6 12,12 16,14"/>'],
        ['/betalningar.php', t('nav.payments'), '<rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/>'],
        ['/fakturor.php', t('nav.invoices'), '<rect x="2" y="5" width="20" height="14" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/>'],
        ['/dokument.php', t('nav.documents'), '<path d="M22 19a2 2 0 01-2 2H4a2 2 0 01-2-2V5a2 2 0 012-2h5l2 3h9a2 2 0 012 2z"/>'],
        ['/installningar.php', t('nav.settings'), '<circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 00.33 1.82l.06.06a2 2 0 11-2.83 2.83l-.06-.06a1.65 1.65 0 00-1.82-.33 1.65 1.65 0 00-1 1.51V21a2 2 0 11-4 0v-.09A1.65 1.65 0 008 19.4a1.65 1.65 0 00-1.82.33l-.06.06a2 2 0 11-2.83-2.83l.06-.06a1.65 1.65 0 00.33-1.82 1.65 1.65 0 00-1.51-1H2a2 2 0 110-4h.09A1.65 1.65 0 003.6 8a1.65 1.65 0 00-.33-1.82l-.06-.06a2 2 0 112.83-2.83l.06.06a1.65 1.65 0 001.82.33H8a1.65 1.65 0 001-1.51V2a2 2 0 114 0v.09a1.65 1.65 0 001 1.51 1.65 1.65 0 001.82-.33l.06-.06a2 2 0 112.83 2.83l-.06.06a1.65 1.65 0 00-.33 1.82V8a1.65 1.65 0 001.51 1H21a2 2 0 110 4h-.09a1.65 1.65 0 00-1.51 1z"/>'],
    ];
    echo '<nav class="portal-nav">';
    foreach ($links as [$href, $label, $icon]) {
        $isActive = $active === $href ? ' active' : '';
        echo "<a href=\"/leverantor{$href}\" class=\"{$isActive}\"><svg viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"currentColor\" stroke-width=\"1.8\"><{$icon}</svg>" . htmlspecialchars($label) . "</a>";
    }
    echo '</nav>';
}

function supp_foot(): void { ?>
</body></html>
<?php }
