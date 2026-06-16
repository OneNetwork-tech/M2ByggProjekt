<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/layout.php';
$su  = supp_require();
$sid = (int)$su['supplier_id'];

$s = db()->prepare("
    SELECT tr.*, p.title AS project_title
    FROM time_reports tr
    JOIN projects p ON p.id=tr.project_id
    WHERE tr.supplier_id=? AND tr.amount IS NOT NULL
    ORDER BY tr.report_date DESC
");
$s->execute([$sid]);
$reports = $s->fetchAll();

$totalPaid   = array_sum(array_column(array_filter($reports, fn($r) => $r['paid_at']), 'amount'));
$totalUnpaid = array_sum(array_column(array_filter($reports, fn($r) => !$r['paid_at']), 'amount'));

supp_head('Betalningar', $su);
supp_nav('/betalningar.php');
?>
<main class="portal-main">
  <div class="portal-page-title"><h1>Betalningar</h1><p>Översikt av utbetalningar från M2 Bygg Team.</p></div>

  <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:16px;margin-bottom:32px">
    <div class="stat-card">
      <div class="stat-card__value" style="color:var(--success)"><?= number_format($totalPaid,0,',',' ') ?> kr</div>
      <div class="stat-card__label">Totalt betalt</div>
    </div>
    <div class="stat-card">
      <div class="stat-card__value" style="color:var(--warning)"><?= number_format($totalUnpaid,0,',',' ') ?> kr</div>
      <div class="stat-card__label">Väntande utbetalning</div>
    </div>
  </div>

  <div class="card">
    <div class="card-header"><h3>Betalningshistorik</h3></div>
    <?php if ($reports): ?>
    <table class="portal-table">
      <thead><tr><th>Datum</th><th>Projekt</th><th>Timmar</th><th>Belopp</th><th>Status</th><th>Betald</th></tr></thead>
      <tbody>
      <?php foreach ($reports as $r): ?>
      <tr>
        <td style="white-space:nowrap"><?= e($r['report_date']) ?></td>
        <td><?= e($r['project_title']) ?></td>
        <td><?= $r['hours'] ? number_format($r['hours'],1,',','').' h' : '—' ?></td>
        <td style="font-weight:600"><?= number_format($r['amount'],0,',',' ') ?> kr</td>
        <td><span class="badge badge--<?= $r['paid_at'] ? 'success' : 'warning' ?>"><?= $r['paid_at'] ? 'Betald' : 'Väntar' ?></span></td>
        <td style="font-size:.8125rem;color:var(--steel)"><?= $r['paid_at'] ? substr($r['paid_at'],0,10) : '—' ?></td>
      </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
    <?php else: ?>
    <p style="padding:24px;color:var(--steel);font-size:.875rem">Inga betalningsposter ännu. Betalningar registreras när tidrapporter godkänns av M2.</p>
    <?php endif; ?>
  </div>
</main>
<?php supp_foot(); ?>
