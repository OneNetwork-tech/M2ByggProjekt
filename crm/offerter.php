<?php
$crm_title = 'Offerter';
$crm_page  = 'offerter';
require_once __DIR__ . '/includes/crm-header.php';
require_role(['sales','finance']);
$pdo = db();

$filter = $_GET['status'] ?? 'all';
$where = $filter !== 'all' ? "WHERE q.status = " . $pdo->quote($filter) : '';
$quotes = $pdo->query("
  SELECT q.*, c.name AS customer_name, l.name AS lead_name
  FROM quotes q
  LEFT JOIN customers c ON c.id = q.customer_id
  LEFT JOIN leads l ON l.id = q.lead_id
  $where ORDER BY q.created_at DESC")->fetchAll();

$counts = [];
foreach (QUOTE_STATUSES as $k => $_) {
  $counts[$k] = $pdo->query("SELECT COUNT(*) FROM quotes WHERE status='$k'")->fetchColumn();
}
?>

<div class="topbar">
  <div>
    <h1>Offerter</h1>
    <div class="topbar__sub"><?= array_sum($counts) ?> offerter · <?= $counts['accepted'] ?> accepterade</div>
  </div>
  <div class="topbar__actions">
    <a href="offert.php" class="btn btn--primary">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
      Ny offert
    </a>
  </div>
</div>

<?php flash(); ?>

<div class="tabs">
  <a href="?status=all" class="tab <?= $filter==='all'?'active':'' ?>">Alla</a>
  <?php foreach (QUOTE_STATUSES as $k => $cfg): ?>
  <a href="?status=<?= $k ?>" class="tab <?= $filter===$k?'active':'' ?>"><?= e($cfg['label']) ?> (<?= $counts[$k] ?>)</a>
  <?php endforeach; ?>
</div>

<div class="card">
  <div class="table-wrap">
    <table class="data">
      <thead><tr><th>Nr</th><th>Titel</th><th>Kund/Lead</th><th>Belopp (inkl. ROT)</th><th>Status</th><th>Giltig till</th><th>Skapad</th></tr></thead>
      <tbody>
        <?php if (!$quotes): ?>
        <tr><td colspan="7" style="text-align:center;color:var(--gray);padding:36px">Inga offerter.</td></tr>
        <?php endif; ?>
        <?php foreach ($quotes as $qt): ?>
        <tr data-href="offert.php?id=<?= $qt['id'] ?>">
          <td style="font-size:12px;color:var(--gray-lt)"><?= e($qt['quote_no']) ?></td>
          <td style="font-weight:550"><?= e($qt['title']) ?></td>
          <td><?= e($qt['customer_name'] ?: $qt['lead_name'] ?: '–') ?></td>
          <td style="font-weight:600"><?= money($qt['total']) ?></td>
          <td><?= badge($qt['status'], QUOTE_STATUSES) ?></td>
          <td style="font-size:12.5px;color:<?= $qt['valid_until'] && strtotime($qt['valid_until']) < time() && !in_array($qt['status'],['accepted','rejected']) ? 'var(--red)' : 'var(--gray)' ?>"><?= dt($qt['valid_until']) ?></td>
          <td style="font-size:12.5px;color:var(--gray)"><?= dt($qt['created_at']) ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require_once __DIR__ . '/includes/crm-footer.php'; ?>
