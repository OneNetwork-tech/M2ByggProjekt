<?php
$crm_title = 'Leverantörsfakturor';
$crm_page  = 'leverantorsfakturor';
require_once __DIR__ . '/includes/crm-header.php';
require_role(['finance','sales']);
$pdo = db();

$filter = $_GET['status'] ?? 'all';
$where = $filter !== 'all' ? "WHERE si.status = " . $pdo->quote($filter) : '';
$invoices = $pdo->query("SELECT si.*, s.company AS supplier_name FROM supplier_invoices si LEFT JOIN suppliers s ON s.id=si.supplier_id $where ORDER BY si.created_at DESC")->fetchAll();

$counts = [];
foreach (SUPPLIER_INVOICE_STATUSES as $k => $_) $counts[$k] = $pdo->query("SELECT COUNT(*) FROM supplier_invoices WHERE status='$k'")->fetchColumn();
$totOutstanding = (float)$pdo->query("SELECT COALESCE(SUM(total-paid_amount),0) FROM supplier_invoices WHERE status='approved'")->fetchColumn();
$totPaid = (float)$pdo->query("SELECT COALESCE(SUM(paid_amount),0) FROM supplier_invoices WHERE status != 'rejected'")->fetchColumn();
?>

<div class="topbar">
  <div>
    <h1>Leverantörsfakturor</h1>
    <div class="topbar__sub">Betalt till leverantörer: <strong style="color:var(--green)"><?= money($totPaid) ?></strong> · Att betala: <strong style="color:var(--amber)"><?= money($totOutstanding) ?></strong></div>
  </div>
</div>

<?php flash(); ?>

<div class="tabs">
  <a href="?status=all" class="tab <?= $filter==='all'?'active':'' ?>">Alla</a>
  <?php foreach (SUPPLIER_INVOICE_STATUSES as $k => $cfg): ?>
  <a href="?status=<?= $k ?>" class="tab <?= $filter===$k?'active':'' ?>"><?= e($cfg['label']) ?> (<?= $counts[$k] ?>)</a>
  <?php endforeach; ?>
</div>

<div class="card">
  <div class="table-wrap">
    <table class="data">
      <thead><tr><th>Nr</th><th>Leverantör</th><th>Status</th><th>Belopp</th><th>Betalt</th><th>Kvar</th><th>Förfaller</th></tr></thead>
      <tbody>
        <?php if (!$invoices): ?>
        <tr><td colspan="7" style="text-align:center;color:var(--gray);padding:36px">Inga leverantörsfakturor ännu. Leverantörer skickar in fakturor via sin portal.</td></tr>
        <?php endif; ?>
        <?php foreach ($invoices as $inv): ?>
        <tr data-href="leverantorsfaktura.php?id=<?= $inv['id'] ?>">
          <td style="font-weight:550"><?= e($inv['invoice_no']) ?></td>
          <td><?= e($inv['supplier_name'] ?: '–') ?></td>
          <td><?= badge($inv['status'], SUPPLIER_INVOICE_STATUSES) ?></td>
          <td style="font-weight:600"><?= money($inv['total']) ?></td>
          <td style="color:var(--green)"><?= money($inv['paid_amount']) ?></td>
          <td style="font-weight:550;color:<?= ($inv['total']-$inv['paid_amount']) > 0 && $inv['status'] !== 'rejected' ? 'var(--amber)' : 'var(--gray-lt)' ?>"><?= money(max(0,$inv['total']-$inv['paid_amount'])) ?></td>
          <td style="font-size:12.5px;color:var(--gray)"><?= dt($inv['due_date']) ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require_once __DIR__ . '/includes/crm-footer.php'; ?>
