<?php
/**
 * M2 Platform — CRM Layout Header
 * Set before include: $crm_title, $crm_page (nav key)
 */
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/helpers.php';
$me = require_login();

$crm_title = $crm_title ?? 'Dashboard';
$crm_page  = $crm_page  ?? '';

// Counts for sidebar badges
$newLeads = db()->query("SELECT COUNT(*) FROM leads WHERE stage='new'")->fetchColumn();
$unreadNotifs = 0;
$st = db()->prepare("SELECT COUNT(*) FROM notifications WHERE user_id=? AND read_at IS NULL");
$st->execute([$me['id']]); $unreadNotifs = $st->fetchColumn();
$unreadPortalMsgs = db()->query("SELECT COUNT(*) FROM portal_messages WHERE sender_type='customer' AND read_at IS NULL")->fetchColumn();
$pendingTimeReports = db()->query("SELECT COUNT(*) FROM time_reports WHERE approved=0")->fetchColumn();
$pendingGdprRequests = db()->query("SELECT COUNT(*) FROM gdpr_requests WHERE status='pending'")->fetchColumn();

$dashboardItem = ['key'=>'dashboard','href'=>'index.php','label'=>'Dashboard','roles'=>null,
   'icon'=>'<rect x="3" y="3" width="7" height="9" rx="1.5"/><rect x="14" y="3" width="7" height="5" rx="1.5"/><rect x="14" y="12" width="7" height="9" rx="1.5"/><rect x="3" y="16" width="7" height="5" rx="1.5"/>'];

$navGroups = [
  'CRM' => [
    ['key'=>'leads','href'=>'leads.php','label'=>'Leads','roles'=>['sales','support'],
     'icon'=>'<path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87"/>','count'=>$newLeads],
    ['key'=>'kunder','href'=>'kunder.php','label'=>'Kunder','roles'=>['sales','support','project'],
     'icon'=>'<path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/>'],
    ['key'=>'leverantorer','href'=>'leverantorer.php','label'=>'Leverantörer','roles'=>['project'],
     'icon'=>'<path d="M14.7 6.3a1 1 0 000 1.4l1.6 1.6a1 1 0 001.4 0l3.77-3.77a6 6 0 01-7.94 7.94l-6.91 6.91a2.12 2.12 0 01-3-3l6.91-6.91a6 6 0 017.94-7.94l-3.76 3.76z"/>'],
    ['key'=>'meddelanden','href'=>'meddelanden.php','label'=>'Kommunikation','roles'=>['sales','support','project'],
     'icon'=>'<path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"/>','count'=>$unreadPortalMsgs],
    ['key'=>'portaler','href'=>'portaler.php','label'=>'Portalåtkomst','roles'=>['super_admin','sales','support'],
     'icon'=>'<rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0110 0v4"/>'],
    ['key'=>'recensioner','href'=>'recensioner.php','label'=>'Recensioner','roles'=>['super_admin','sales','support'],
     'icon'=>'<path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>'],
  ],
  'Försäljning & fakturering' => [
    ['key'=>'offerter','href'=>'offerter.php','label'=>'Offerter','roles'=>['sales'],
     'icon'=>'<path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/>'],
    ['key'=>'fakturor','href'=>'fakturor.php','label'=>'Fakturor','roles'=>['finance','sales'],
     'icon'=>'<rect x="2" y="5" width="20" height="14" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/>'],
    ['key'=>'leverantorsfakturor','href'=>'leverantorsfakturor.php','label'=>'Leverantörsfakturor','roles'=>['finance','sales'],
     'icon'=>'<rect x="2" y="5" width="20" height="14" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/><line x1="6" y1="15" x2="10" y2="15"/>'],
    ['key'=>'paminnelser','href'=>'paminnelser.php','label'=>'Påminnelser','roles'=>['finance','sales'],
     'icon'=>'<path d="M18 8a6 6 0 10-12 0c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 01-3.46 0"/>'],
    ['key'=>'bokforing','href'=>'bokforing.php','label'=>'Bokföring','roles'=>['super_admin'],
     'icon'=>'<path d="M21 7v12a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h9l7 7z"/><path d="M16 3v6h6"/>'],
  ],
  'Projekt' => [
    ['key'=>'projekt','href'=>'projekt.php','label'=>'Projekt','roles'=>['project','sales'],
     'icon'=>'<path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/>'],
    ['key'=>'kalender','href'=>'kalender.php','label'=>'Kalender','roles'=>null,
     'icon'=>'<rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>'],
    ['key'=>'tidrapporter','href'=>'tidrapporter.php','label'=>'Tidrapporter','roles'=>['project','finance'],
     'icon'=>'<circle cx="12" cy="12" r="10"/><polyline points="12,6 12,12 16,14"/>','count'=>$pendingTimeReports],
    ['key'=>'portfolio','href'=>'portfolio.php','label'=>'Projektportfolio','roles'=>null,
     'icon'=>'<rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/>'],
  ],
  'Innehåll' => [
    ['key'=>'tjanster','href'=>'tjanster.php','label'=>'Tjänster','roles'=>null,
     'icon'=>'<circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 00.33 1.82l.06.06a2 2 0 11-2.83 2.83l-.06-.06a1.65 1.65 0 00-1.82-.33 1.65 1.65 0 00-1 1.51V21a2 2 0 11-4 0v-.09A1.65 1.65 0 008 19.4a1.65 1.65 0 00-1.82.33l-.06.06a2 2 0 11-2.83-2.83l.06-.06a1.65 1.65 0 00.33-1.82 1.65 1.65 0 00-1.51-1H2a2 2 0 110-4h.09A1.65 1.65 0 003.6 8a1.65 1.65 0 00-.33-1.82l-.06-.06a2 2 0 112.83-2.83l.06.06a1.65 1.65 0 001.82.33H8a1.65 1.65 0 001-1.51V2a2 2 0 114 0v.09a1.65 1.65 0 001 1.51 1.65 1.65 0 001.82-.33l.06-.06a2 2 0 112.83 2.83l-.06.06a1.65 1.65 0 00-.33 1.82V8a1.65 1.65 0 001.51 1H21a2 2 0 110 4h-.09a1.65 1.65 0 00-1.51 1z"/>'],
    ['key'=>'blogg','href'=>'blogg.php','label'=>'Blogg','roles'=>null,
     'icon'=>'<path d="M4 19.5A2.5 2.5 0 016.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 014 19.5v-15A2.5 2.5 0 016.5 2z"/>'],
    ['key'=>'email','href'=>'email.php','label'=>'E-post','roles'=>null,
     'icon'=>'<path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/>'],
  ],
  'Rapporter' => [
    ['key'=>'rapporter','href'=>'rapporter.php','label'=>'Rapporter','roles'=>['sales','finance','project'],
     'icon'=>'<line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/>'],
  ],
];

$adminGroupLabel = 'Administration';
$adminItems = [
  ['key'=>'anvandare','href'=>'anvandare.php','label'=>'Användare',
   'icon'=>'<path d="M16 21v-2a4 4 0 00-4-4H6a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M19 8v6M22 11h-6"/>'],
  ['key'=>'installningar','href'=>'installningar.php','label'=>'Inställningar',
   'icon'=>'<circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 00.33 1.82l.06.06a2 2 0 11-2.83 2.83l-.06-.06a1.65 1.65 0 00-1.82-.33 1.65 1.65 0 00-1 1.51V21a2 2 0 11-4 0v-.09A1.65 1.65 0 008 19.4a1.65 1.65 0 00-1.82.33l-.06.06a2 2 0 11-2.83-2.83l.06-.06a1.65 1.65 0 00.33-1.82 1.65 1.65 0 00-1.51-1H2a2 2 0 110-4h.09A1.65 1.65 0 003.6 8a1.65 1.65 0 00-.33-1.82l-.06-.06a2 2 0 112.83-2.83l.06.06a1.65 1.65 0 001.82.33H8a1.65 1.65 0 001-1.51V2a2 2 0 114 0v.09a1.65 1.65 0 001 1.51 1.65 1.65 0 001.82-.33l.06-.06a2 2 0 112.83 2.83l-.06.06a1.65 1.65 0 00-.33 1.82V8a1.65 1.65 0 001.51 1H21a2 2 0 110 4h-.09a1.65 1.65 0 00-1.51 1z"/>'],
  ['key'=>'gdpr','href'=>'gdpr.php','label'=>'GDPR','roles'=>['super_admin'],
   'icon'=>'<path d="M12 2L3 7v6c0 5 4 9 9 9s9-4 9-9V7l-9-5z"/>','count'=>$pendingGdprRequests],
  ['key'=>'backup','href'=>'backup.php','label'=>'Backup','roles'=>['super_admin'],
   'icon'=>'<ellipse cx="12" cy="5" rx="9" ry="3"/><path d="M21 12c0 1.66-4 3-9 3s-9-1.34-9-3"/><path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"/>'],
];

function nav_visible(?array $roles, array $me): bool {
  if ($roles === null) return true;
  if (in_array($me['role'], ADMIN_TIER_ROLES)) return true;
  return in_array($me['role'], $roles);
}

function nav_group_slug(string $label): string {
  $slug = preg_replace('/[^a-z0-9]+/', '-', mb_strtolower($label));
  return 'grp-' . trim($slug, '-');
}
?>
<!DOCTYPE html>
<html lang="sv">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="robots" content="noindex,nofollow">
<title><?= e($crm_title) ?> – M2 Platform</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/crm.css">
</head>
<body>
<script>if(localStorage.getItem('crm_sidebar_collapsed')==='1')document.body.classList.add('sidebar-collapsed');</script>
<div class="app">

  <!-- SIDEBAR -->
  <aside class="sidebar">
    <a href="index.php" class="sidebar__logo">
      <img class="sidebar__logo-mark" src="/assets/images/M2-symbol-wht.svg" alt="M2">
      <div class="sidebar__logo-text">
        <div class="sidebar__logo-name">M2 Platform</div>
        <div class="sidebar__logo-sub">Bygg Team CRM</div>
      </div>
    </a>

    <nav class="sidebar__nav">
      <a href="<?= $dashboardItem['href'] ?>" class="nav-link <?= $crm_page === $dashboardItem['key'] ? 'active' : '' ?>" title="<?= e($dashboardItem['label']) ?>">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><?= $dashboardItem['icon'] ?></svg>
        <span class="nav-label"><?= e($dashboardItem['label']) ?></span>
      </a>

      <?php foreach ($navGroups as $groupLabel => $navGroupItems):
        $navVisibleItems = array_values(array_filter($navGroupItems, fn($i) => nav_visible($i['roles'], $me)));
        if (!$navVisibleItems) continue;
        $groupSlug = nav_group_slug($groupLabel);
        $isActiveGroup = in_array($crm_page, array_column($navVisibleItems, 'key'));
      ?>
      <div class="nav-group <?= $isActiveGroup ? 'open' : '' ?>" data-group="<?= $groupSlug ?>">
        <button type="button" class="nav-group__header" onclick="toggleNavGroup('<?= $groupSlug ?>')">
          <span class="nav-label"><?= e(mb_strtoupper($groupLabel)) ?></span>
          <svg class="nav-group__chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
        </button>
        <div class="nav-group__body">
          <div class="nav-group__body-inner">
            <?php foreach ($navVisibleItems as $navItem): ?>
            <a href="<?= $navItem['href'] ?>" class="nav-link <?= $crm_page === $navItem['key'] ? 'active' : '' ?>" title="<?= e($navItem['label']) ?>">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><?= $navItem['icon'] ?></svg>
              <span class="nav-label"><?= e($navItem['label']) ?></span>
              <?php if (!empty($navItem['count'])): ?><span class="nav-count"><?= $navItem['count'] ?></span><?php endif; ?>
            </a>
            <?php endforeach; ?>
          </div>
        </div>
      </div>
      <?php endforeach; ?>

      <?php if ($me['role'] === 'super_admin'):
        $adminSlug = nav_group_slug($adminGroupLabel);
        $isActiveAdmin = in_array($crm_page, array_column($adminItems, 'key'));
      ?>
      <div class="nav-group <?= $isActiveAdmin ? 'open' : '' ?>" data-group="<?= $adminSlug ?>">
        <button type="button" class="nav-group__header" onclick="toggleNavGroup('<?= $adminSlug ?>')">
          <span class="nav-label"><?= e(mb_strtoupper($adminGroupLabel)) ?></span>
          <svg class="nav-group__chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
        </button>
        <div class="nav-group__body">
          <div class="nav-group__body-inner">
            <?php foreach ($adminItems as $navItem): ?>
            <a href="<?= $navItem['href'] ?>" class="nav-link <?= $crm_page === $navItem['key'] ? 'active' : '' ?>" title="<?= e($navItem['label']) ?>">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><?= $navItem['icon'] ?></svg>
              <span class="nav-label"><?= e($navItem['label']) ?></span>
              <?php if (!empty($navItem['count'])): ?><span class="nav-count"><?= $navItem['count'] ?></span><?php endif; ?>
            </a>
            <?php endforeach; ?>
          </div>
        </div>
      </div>
      <?php endif; ?>
    </nav>

    <div class="sidebar__user">
      <div class="avatar"><?= e(initials($me['name'])) ?></div>
      <div class="sidebar__user-info" style="flex:1">
        <div class="sidebar__user-name"><?= e($me['name']) ?></div>
        <div class="sidebar__user-role"><?= e(ROLES[$me['role']] ?? $me['role']) ?></div>
      </div>
      <a href="logout.php" title="Logga ut" style="color:var(--sb-text);display:flex;flex-shrink:0" onclick="return confirm('Logga ut?')">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:16px;height:16px"><path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
      </a>
    </div>

    <button type="button" class="sidebar-collapse-btn" id="sidebarToggle">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><polyline points="11 17 6 12 11 7"/><polyline points="18 17 13 12 18 7"/></svg>
      <span class="nav-label">Fäll ihop</span>
    </button>
  </aside>

  <!-- MAIN -->
  <main class="main">
