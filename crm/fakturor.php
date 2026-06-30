<?php
$crm_title = 'Fakturor';
$crm_page  = 'fakturor';
require_once __DIR__ . '/includes/crm-header.php';
require_role(['finance','sales']);
$pdo = db();

// refresh overdue
$pdo->exec("UPDATE invoices SET status='overdue' WHERE status='sent' AND due_date < " . today_expr());

$filter = $_GET['status'] ?? 'all';
$where = $filter !== 'all' ? "WHERE i.status = " . $pdo->quote($filter) : '';
$invoices = $pdo->query("SELECT i.*, c.name AS customer_name FROM invoices i LEFT JOIN customers c ON c.id=i.customer_id $where ORDER BY i.created_at DESC")->fetchAll();

$counts = [];
foreach (INVOICE_STATUSES as $k => $_) $counts[$k] = $pdo->query("SELECT COUNT(*) FROM invoices WHERE status='$k'")->fetchColumn();
$totOutstanding = (float)$pdo->query("SELECT COALESCE(SUM(total-paid_amount),0) FROM invoices WHERE status IN ('sent','partial','overdue')")->fetchColumn();
$totPaid = (float)$pdo->query("SELECT COALESCE(SUM(paid_amount),0) FROM invoices WHERE status != 'cancelled'")->fetchColumn();
?>

<div class="topbar">
  <div>
    <h1>Fakturor</h1>
    <div class="topbar__sub">Betalt: <strong style="color:var(--green)"><?= money($totPaid) ?></strong> · Utestående: <strong style="color:var(--amber)"><?= money($totOutstanding) ?></strong></div>
  </div>
</div>

<?php flash(); ?>

<div class="tabs">
  <a href="?status=all" class="tab <?= $filter==='all'?'active':'' ?>">Alla</a>
  <?php foreach (INVOICE_STATUSES as $k => $cfg): ?>
  <a href="?status=<?= $k ?>" class="tab <?= $filter===$k?'active':'' ?>"><?= e($cfg['label']) ?> (<?= $counts[$k] ?>)</a>
  <?php endforeach; ?>
</div>

<div class="card">
  <div class="table-wrap">
    <table class="data">
      <thead><tr><th>Nr</th><th>Kund</th><th>Status</th><th>Belopp</th><th>Betalt</th><th>Kvar</th><th>Förfaller</th></tr></thead>
      <tbody>
        <?php if (!$invoices): ?>
        <tr><td colspan="7" style="text-align:center;color:var(--gray);padding:36px">Inga fakturor. Fakturautkast skapas automatiskt vid offertacceptans.</td></tr>
        <?php endif; ?>
        <?php foreach ($invoices as $inv): ?>
        <tr data-href="faktura.php?id=<?= $inv['id'] ?>">
          <td style="font-weight:550"><?= e($inv['invoice_no']) ?></td>
          <td><?= e($inv['customer_name'] ?: '–') ?></td>
          <td><?= badge($inv['status'], INVOICE_STATUSES) ?></td>
          <td style="font-weight:600"><?= money($inv['total']) ?></td>
          <td style="color:var(--green)"><?= money($inv['paid_amount']) ?></td>
          <td style="font-weight:550;color:<?= ($inv['total']-$inv['paid_amount']) > 0 && $inv['status'] !== 'cancelled' ? 'var(--amber)' : 'var(--gray-lt)' ?>"><?= money(max(0,$inv['total']-$inv['paid_amount'])) ?></td>
          <td style="font-size:12.5px;color:<?= $inv['status']==='overdue' ? 'var(--red)' : 'var(--gray)' ?>"><?= dt($inv['due_date']) ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require_once __DIR__ . '/includes/crm-footer.php'; ?>
