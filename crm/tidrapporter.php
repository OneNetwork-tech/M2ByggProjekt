<?php
/**
 * CRM — Supplier time report management: approve, mark paid
 */
require_once __DIR__ . '/includes/auth.php';
require_role(['super_admin','project','finance']);
$pdo = db();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $action = $_POST['action'] ?? '';
    $ids    = array_map('intval', (array)($_POST['ids'] ?? []));
    if ($ids) {
        if ($action === 'approve') {
            $pdo->prepare("UPDATE time_reports SET approved=1 WHERE id IN (" . implode(',', $ids) . ")")->execute();
            flash(count($ids) . ' rapport(er) godkänd(a).');
        } elseif ($action === 'pay') {
            $pdo->prepare("UPDATE time_reports SET paid_at=datetime('now','localtime') WHERE id IN (" . implode(',', $ids) . ") AND approved=1")->execute();
            flash(count($ids) . ' rapport(er) markerad(e) som betald(a).');
        } elseif ($action === 'unapprove') {
            $pdo->prepare("UPDATE time_reports SET approved=0 WHERE id IN (" . implode(',', $ids) . ")")->execute();
        }
    }
    header('Location: tidrapporter.php'); exit;
}

$filter = $_GET['filter'] ?? 'pending';
$where = match($filter) {
    'pending'  => "tr.approved=0",
    'approved' => "tr.approved=1 AND tr.paid_at IS NULL",
    'paid'     => "tr.paid_at IS NOT NULL",
    default    => "1=1",
};

$reports = $pdo->query("
    SELECT tr.*, s.company AS supplier_company, p.title AS project_title
    FROM time_reports tr
    JOIN suppliers s ON s.id=tr.supplier_id
    JOIN projects p ON p.id=tr.project_id
    WHERE $where
    ORDER BY tr.report_date DESC
")->fetchAll();

// Totals
$totPending  = $pdo->query("SELECT COALESCE(SUM(amount),0) FROM time_reports WHERE approved=0 AND amount IS NOT NULL")->fetchColumn();
$totApproved = $pdo->query("SELECT COALESCE(SUM(amount),0) FROM time_reports WHERE approved=1 AND paid_at IS NULL AND amount IS NOT NULL")->fetchColumn();
$totPaid     = $pdo->query("SELECT COALESCE(SUM(amount),0) FROM time_reports WHERE paid_at IS NOT NULL AND amount IS NOT NULL")->fetchColumn();

$crm_title = 'Tidrapporter';
$crm_page  = 'leverantorer';
require_once __DIR__ . '/includes/crm-header.php';
?>
<div class="topbar">
  <div>
    <h1>Leverantörs tidrapporter</h1>
    <div class="topbar__sub">Godkänn och registrera betalningar till underleverantörer</div>
  </div>
</div>
<?php flash(); ?>

<!-- Summary -->
<div style="display:grid;grid-template-columns:repeat(3,1fr);gap:16px;margin-bottom:20px">
  <div class="card card--pad" style="text-align:center">
    <div style="font-size:22px;font-weight:700;color:var(--amber)"><?= number_format((float)$totPending,0,',',' ') ?> kr</div>
    <div style="font-size:12px;color:var(--gray);margin-top:4px">Väntar godkännande</div>
  </div>
  <div class="card card--pad" style="text-align:center">
    <div style="font-size:22px;font-weight:700;color:var(--blue)"><?= number_format((float)$totApproved,0,',',' ') ?> kr</div>
    <div style="font-size:12px;color:var(--gray);margin-top:4px">Godkänd, obetald</div>
  </div>
  <div class="card card--pad" style="text-align:center">
    <div style="font-size:22px;font-weight:700;color:var(--green)"><?= number_format((float)$totPaid,0,',',' ') ?> kr</div>
    <div style="font-size:12px;color:var(--gray);margin-top:4px">Totalt utbetalt</div>
  </div>
</div>

<!-- Filter tabs -->
<div class="tabs" style="margin-bottom:0">
  <a href="?filter=pending"  class="tab <?= $filter==='pending' ?'active':'' ?>">Väntar <?php $c=$pdo->query("SELECT COUNT(*) FROM time_reports WHERE approved=0")->fetchColumn(); if($c) echo "<span class='nav-count'>$c</span>"; ?></a>
  <a href="?filter=approved" class="tab <?= $filter==='approved'?'active':'' ?>">Godkänd, obetald <?php $c=$pdo->query("SELECT COUNT(*) FROM time_reports WHERE approved=1 AND paid_at IS NULL")->fetchColumn(); if($c) echo "<span class='nav-count'>$c</span>"; ?></a>
  <a href="?filter=paid"     class="tab <?= $filter==='paid'    ?'active':'' ?>">Betalda</a>
  <a href="?filter=all"      class="tab <?= $filter==='all'     ?'active':'' ?>">Alla</a>
</div>

<form method="POST">
  <?= csrf_field() ?>
  <div class="card" style="overflow:hidden">
    <?php if ($reports): ?>
    <table class="table">
      <thead>
        <tr>
          <th style="width:32px"><input type="checkbox" id="chk-all" onclick="document.querySelectorAll('.chk-row').forEach(c=>c.checked=this.checked)"></th>
          <th>Datum</th>
          <th>Leverantör</th>
          <th>Projekt</th>
          <th>Timmar</th>
          <th>Belopp</th>
          <th>Beskrivning</th>
          <th>Status</th>
        </tr>
      </thead>
      <tbody>
      <?php foreach ($reports as $r): ?>
      <tr>
        <td><input type="checkbox" class="chk-row" name="ids[]" value="<?= $r['id'] ?>"></td>
        <td style="white-space:nowrap;font-size:12.5px"><?= e($r['report_date']) ?></td>
        <td style="font-weight:550;font-size:13px"><?= e($r['supplier_company']) ?></td>
        <td style="font-size:12.5px"><?= e($r['project_title']) ?></td>
        <td style="font-size:12.5px"><?= $r['hours'] ? number_format($r['hours'],1,',','').' h' : '—' ?></td>
        <td style="font-weight:600;font-size:13px"><?= $r['amount'] ? number_format($r['amount'],0,',',' ').' kr' : '—' ?></td>
        <td style="font-size:12px;color:var(--gray);max-width:180px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap"><?= e($r['description'] ?? '') ?></td>
        <td>
          <?php if ($r['paid_at']): ?>
            <span class="badge badge-success">Betald</span>
          <?php elseif ($r['approved']): ?>
            <span class="badge badge-info">Godkänd</span>
          <?php else: ?>
            <span class="badge badge-warning">Väntar</span>
          <?php endif; ?>
        </td>
      </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
    <?php else: ?>
    <p style="padding:24px;color:var(--gray);font-size:13px">Inga rapporter i detta filter.</p>
    <?php endif; ?>
  </div>

  <?php if ($reports): ?>
  <div style="display:flex;gap:10px;margin-top:14px;flex-wrap:wrap">
    <?php if ($filter === 'pending' || $filter === 'all'): ?>
    <button type="submit" name="action" value="approve" class="btn btn--primary btn--sm">✓ Godkänn valda</button>
    <?php endif; ?>
    <?php if ($filter === 'approved' || $filter === 'all'): ?>
    <button type="submit" name="action" value="pay" class="btn btn--primary btn--sm" style="background:var(--green)">💳 Markera betalda</button>
    <?php endif; ?>
    <button type="submit" name="action" value="unapprove" class="btn btn--ghost btn--sm">Ångra godkännande</button>
  </div>
  <?php endif; ?>
</form>

<?php require_once __DIR__ . '/includes/crm-footer.php'; ?>
