<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/layout.php';
$pu  = portal_require();
$cid = (int)$pu['customer_id'];

$invoices = db()->prepare(
    "SELECT i.*, p.title AS project_title
     FROM invoices i LEFT JOIN projects p ON p.id = i.project_id
     WHERE i.customer_id = ? ORDER BY i.created_at DESC"
);
$invoices->execute([$cid]);
$invoices = $invoices->fetchAll();

$statusColors = ['draft'=>'gray','sent'=>'blue','partial'=>'orange','paid'=>'green','overdue'=>'red','cancelled'=>'gray'];

portal_head('Fakturor', $pu);
portal_nav('/fakturor.php');
?>
<main class="portal-main">
  <div class="portal-page-title"><h1>Fakturor</h1><p>Alla fakturor kopplade till ditt konto.</p></div>

  <?php if ($invoices): ?>
  <div class="card">
    <table class="tbl">
      <thead>
        <tr><th>Fakturanr</th><th>Projekt</th><th>Förfaller</th><th style="text-align:right">Belopp</th><th>Status</th><th></th></tr>
      </thead>
      <tbody>
      <?php foreach ($invoices as $inv):
        $color = $statusColors[$inv['status']] ?? 'gray';
        $lbl   = INVOICE_STATUSES[$inv['status']]['label'] ?? $inv['status'];
      ?>
      <tr>
        <td><strong><?= e($inv['invoice_no'] ?? '—') ?></strong><br><span style="font-size:.75rem;color:var(--steel)"><?= e($inv['issue_date'] ?? '') ?></span></td>
        <td style="color:var(--steel);font-size:.85rem"><?= e($inv['project_title'] ?? '—') ?></td>
        <td style="font-size:.85rem<?= $inv['status'] === 'overdue' ? ';color:var(--red);font-weight:600' : '' ?>"><?= e($inv['due_date'] ?? '—') ?></td>
        <td style="text-align:right;font-weight:700"><?= number_format($inv['total'] ?? 0, 0, ',', ' ') ?> kr</td>
        <td><span class="badge badge--<?= $color ?>"><?= e($lbl) ?></span></td>
        <td>
          <?php if ($inv['status'] !== 'draft'): ?>
          <a href="/crm/offert-pdf.php?invoice=<?= $inv['id'] ?>" target="_blank" class="btn btn--outline btn--sm">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:13px;height:13px"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="7,10 12,15 17,10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
            PDF
          </a>
          <?php endif; ?>
        </td>
      </tr>
      <?php endforeach; ?>
      </tbody>
    </table>

    <!-- Summary row -->
    <?php
    $totalUnpaid = 0; $totalPaid = 0;
    foreach ($invoices as $inv) {
        if ($inv['status'] === 'paid') $totalPaid += $inv['total'];
        elseif (in_array($inv['status'], ['sent','partial','overdue'])) $totalUnpaid += $inv['total'] - ($inv['paid_amount'] ?? 0);
    }
    ?>
    <div style="display:flex;gap:24px;padding:16px 14px 0;border-top:2px solid var(--border);font-size:.875rem;flex-wrap:wrap">
      <span style="color:var(--steel)">Betalt: <strong style="color:var(--green)"><?= number_format($totalPaid, 0, ',', ' ') ?> kr</strong></span>
      <span style="color:var(--steel)">Obetalt: <strong style="color:<?= $totalUnpaid > 0 ? 'var(--red)' : 'var(--green)' ?>"><?= number_format($totalUnpaid, 0, ',', ' ') ?> kr</strong></span>
    </div>
  </div>
  <?php else: ?>
  <div class="card" style="text-align:center;padding:48px"><p style="color:var(--steel)">Inga fakturor ännu.</p></div>
  <?php endif; ?>
</main>
<?php portal_foot(); ?>
