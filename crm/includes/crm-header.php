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

$navItems = [
  ['key'=>'dashboard','href'=>'index.php','label'=>'Dashboard','roles'=>null,
   'icon'=>'<rect x="3" y="3" width="7" height="9" rx="1.5"/><rect x="14" y="3" width="7" height="5" rx="1.5"/><rect x="14" y="12" width="7" height="9" rx="1.5"/><rect x="3" y="16" width="7" height="5" rx="1.5"/>'],
  ['key'=>'leads','href'=>'leads.php','label'=>'Leads','roles'=>['sales','support'],
   'icon'=>'<path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87"/>','count'=>$newLeads],
  ['key'=>'kunder','href'=>'kunder.php','label'=>'Kunder','roles'=>['sales','support','project'],
   'icon'=>'<path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/>'],
  ['key'=>'offerter','href'=>'offerter.php','label'=>'Offerter','roles'=>['sales'],
   'icon'=>'<path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/>'],
  ['key'=>'projekt','href'=>'projekt.php','label'=>'Projekt','roles'=>['project','sales'],
   'icon'=>'<path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/>'],
  ['key'=>'fakturor','href'=>'fakturor.php','label'=>'Fakturor','roles'=>['finance','sales'],
   'icon'=>'<rect x="2" y="5" width="20" height="14" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/>'],
  ['key'=>'leverantorer','href'=>'leverantorer.php','label'=>'Leverantörer','roles'=>['project'],
   'icon'=>'<path d="M14.7 6.3a1 1 0 000 1.4l1.6 1.6a1 1 0 001.4 0l3.77-3.77a6 6 0 01-7.94 7.94l-6.91 6.91a2.12 2.12 0 01-3-3l6.91-6.91a6 6 0 017.94-7.94l-3.76 3.76z"/>'],
  ['key'=>'tidrapporter','href'=>'tidrapporter.php','label'=>'Tidrapporter','roles'=>['project','finance'],
   'icon'=>'<circle cx="12" cy="12" r="10"/><polyline points="12,6 12,12 16,14"/>','count'=>$pendingTimeReports],
  ['key'=>'portaler','href'=>'portaler.php','label'=>'Portalåtkomst','roles'=>['super_admin','sales','support'],
   'icon'=>'<rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0110 0v4"/>'],
  ['key'=>'meddelanden','href'=>'meddelanden.php','label'=>'Kommunikation','roles'=>['sales','support','project'],
   'icon'=>'<path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"/>','count'=>$unreadPortalMsgs],
];

$adminItems = [
  ['key'=>'anvandare','href'=>'anvandare.php','label'=>'Användare',
   'icon'=>'<path d="M16 21v-2a4 4 0 00-4-4H6a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M19 8v6M22 11h-6"/>'],
  ['key'=>'installningar','href'=>'installningar.php','label'=>'Inställningar',
   'icon'=>'<circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 00.33 1.82l.06.06a2 2 0 11-2.83 2.83l-.06-.06a1.65 1.65 0 00-1.82-.33 1.65 1.65 0 00-1 1.51V21a2 2 0 11-4 0v-.09A1.65 1.65 0 008 19.4a1.65 1.65 0 00-1.82.33l-.06.06a2 2 0 11-2.83-2.83l.06-.06a1.65 1.65 0 00.33-1.82 1.65 1.65 0 00-1.51-1H2a2 2 0 110-4h.09A1.65 1.65 0 003.6 8a1.65 1.65 0 00-.33-1.82l-.06-.06a2 2 0 112.83-2.83l.06.06a1.65 1.65 0 001.82.33H8a1.65 1.65 0 001-1.51V2a2 2 0 114 0v.09a1.65 1.65 0 001 1.51 1.65 1.65 0 001.82-.33l.06-.06a2 2 0 112.83 2.83l-.06.06a1.65 1.65 0 00-.33 1.82V8a1.65 1.65 0 001.51 1H21a2 2 0 110 4h-.09a1.65 1.65 0 00-1.51 1z"/>'],
];

function nav_visible(?array $roles, array $me): bool {
  if ($roles === null) return true;
  if ($me['role'] === 'super_admin') return true;
  return in_array($me['role'], $roles);
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
<div class="app">

  <!-- SIDEBAR -->
  <aside class="sidebar">
    <a href="index.php" class="sidebar__logo">
      <div class="sidebar__logo-mark">m2</div>
      <div class="sidebar__logo-text">
        <div class="sidebar__logo-name">M2 Platform</div>
        <div class="sidebar__logo-sub">Bygg Team CRM</div>
      </div>
    </a>

    <?php foreach ($navItems as $item): if (!nav_visible($item['roles'], $me)) continue; ?>
    <a href="<?= $item['href'] ?>" class="nav-link <?= $crm_page === $item['key'] ? 'active' : '' ?>">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><?= $item['icon'] ?></svg>
      <?= e($item['label']) ?>
      <?php if (!empty($item['count'])): ?><span class="nav-count"><?= $item['count'] ?></span><?php endif; ?>
    </a>
    <?php endforeach; ?>

    <?php if ($me['role'] === 'super_admin'): ?>
    <div class="nav-sep">Administration</div>
    <?php foreach ($adminItems as $item): ?>
    <a href="<?= $item['href'] ?>" class="nav-link <?= $crm_page === $item['key'] ? 'active' : '' ?>">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><?= $item['icon'] ?></svg>
      <?= e($item['label']) ?>
    </a>
    <?php endforeach; ?>
    <?php endif; ?>

    <div class="sidebar__user">
      <div class="avatar"><?= e(initials($me['name'])) ?></div>
      <div style="flex:1;min-width:0">
        <div class="sidebar__user-name"><?= e($me['name']) ?></div>
        <div class="sidebar__user-role"><?= e(ROLES[$me['role']] ?? $me['role']) ?></div>
      </div>
      <a href="logout.php" title="Logga ut" style="color:var(--gray-lt);display:flex" onclick="return confirm('Logga ut?')">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:16px;height:16px"><path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
      </a>
    </div>
  </aside>

  <!-- MAIN -->
  <main class="main">
