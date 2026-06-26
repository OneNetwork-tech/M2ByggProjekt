<?php
/**
 * CRM — View site photos uploaded by a supplier for a job assignment
 */
require_once __DIR__ . '/includes/auth.php';
$me = require_login();
$pdo = db();

$jobId = (int)($_GET['job'] ?? 0);
$s = $pdo->prepare("
    SELECT ja.*, s.company AS supplier_company, p.title AS project_title, p.id AS project_id
    FROM job_assignments ja JOIN suppliers s ON s.id=ja.supplier_id JOIN projects p ON p.id=ja.project_id
    WHERE ja.id=?
");
$s->execute([$jobId]);
$job = $s->fetch();
if (!$job) { header('Location: projekt.php'); exit; }

$ps = $pdo->prepare("SELECT * FROM job_photos WHERE job_assignment_id=? ORDER BY created_at DESC");
$ps->execute([$jobId]);
$photos = $ps->fetchAll();

$crm_title = 'Platsbilder';
$crm_page  = 'projekt';
require_once __DIR__ . '/includes/crm-header.php';
?>
<div class="topbar">
  <div>
    <h1>Platsbilder — <?= e($job['supplier_company']) ?></h1>
    <div class="topbar__sub"><?= e($job['project_title']) ?></div>
  </div>
  <div class="topbar__actions">
    <a href="projekt-detalj.php?id=<?= $job['project_id'] ?>" class="btn btn--ghost">← Projekt</a>
  </div>
</div>

<?php if ($photos): ?>
<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:16px">
  <?php foreach ($photos as $p): ?>
  <div class="card" style="overflow:hidden">
    <a href="leverantor-foto.php?id=<?= $p['id'] ?>" target="_blank">
      <img src="leverantor-foto.php?id=<?= $p['id'] ?>" alt="<?= e($p['caption'] ?: '') ?>" loading="lazy" style="width:100%;aspect-ratio:4/3;object-fit:cover;display:block">
    </a>
    <div style="padding:10px 12px">
      <?php if ($p['caption']): ?><div style="font-size:12.5px;font-weight:550;margin-bottom:2px"><?= e($p['caption']) ?></div><?php endif; ?>
      <div style="font-size:11px;color:var(--gray)"><?= dt($p['created_at'], 'j M H:i') ?></div>
    </div>
  </div>
  <?php endforeach; ?>
</div>
<?php else: ?>
<div class="card card--pad" style="text-align:center;padding:48px">
  <p style="color:var(--gray)">Inga bilder uppladdade ännu.</p>
</div>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/crm-footer.php'; ?>
