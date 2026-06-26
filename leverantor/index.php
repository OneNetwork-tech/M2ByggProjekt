<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/layout.php';
$su  = supp_require();
$sid = (int)$su['supplier_id'];

// Stats
$pending = db()->prepare("SELECT COUNT(*) FROM job_assignments WHERE supplier_id=? AND status='pending'")->execute([$sid])
    ? (function() use ($sid) { $s=db()->prepare("SELECT COUNT(*) FROM job_assignments WHERE supplier_id=? AND status='pending'"); $s->execute([$sid]); return $s->fetchColumn(); })() : 0;
$active = (function() use ($sid) { $s=db()->prepare("SELECT COUNT(*) FROM job_assignments WHERE supplier_id=? AND status='accepted'"); $s->execute([$sid]); return $s->fetchColumn(); })();
$completed = (function() use ($sid) { $s=db()->prepare("SELECT COUNT(*) FROM job_assignments WHERE supplier_id=? AND status='completed'"); $s->execute([$sid]); return $s->fetchColumn(); })();
$unpaidAmt = (function() use ($sid) { $s=db()->prepare("SELECT COALESCE(SUM(amount),0) FROM time_reports WHERE supplier_id=? AND paid_at IS NULL"); $s->execute([$sid]); return $s->fetchColumn(); })();

// Recent assignments
$s = db()->prepare("
    SELECT ja.*, p.title AS project_title, p.address AS project_address
    FROM job_assignments ja
    JOIN projects p ON p.id = ja.project_id
    WHERE ja.supplier_id = ?
    ORDER BY ja.created_at DESC LIMIT 5
");
$s->execute([$sid]);
$recentJobs = $s->fetchAll();

// Recent time reports
$s = db()->prepare("
    SELECT tr.*, p.title AS project_title
    FROM time_reports tr
    JOIN projects p ON p.id = tr.project_id
    WHERE tr.supplier_id = ?
    ORDER BY tr.created_at DESC LIMIT 5
");
$s->execute([$sid]);
$recentReports = $s->fetchAll();

supp_head(t('nav.dashboard'), $su);
supp_nav('/');
?>
<main class="portal-main">
  <div class="portal-page-title">
    <h1><?= e(sprintf(t('dash.supplier_welcome'), $su['company'])) ?></h1>
    <p><?= e(t('dash.supplier_overview')) ?></p>
  </div>

  <!-- Stats -->
  <div class="stats-grid" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:16px;margin-bottom:32px">
    <div class="stat-card">
      <div class="stat-card__value"><?= (int)$pending ?></div>
      <div class="stat-card__label"><?= e(t('dash.pending_offers')) ?></div>
    </div>
    <div class="stat-card">
      <div class="stat-card__value"><?= (int)$active ?></div>
      <div class="stat-card__label"><?= e(t('dash.active_jobs')) ?></div>
    </div>
    <div class="stat-card">
      <div class="stat-card__value"><?= (int)$completed ?></div>
      <div class="stat-card__label"><?= e(t('dash.completed_jobs')) ?></div>
    </div>
    <div class="stat-card">
      <div class="stat-card__value"><?= number_format((float)$unpaidAmt, 0, ',', ' ') ?> kr</div>
      <div class="stat-card__label"><?= e(t('dash.unpaid_amount')) ?></div>
    </div>
  </div>

  <?php if ($pending > 0): ?>
  <div class="alert alert--warning" style="margin-bottom:24px">
    <strong>Du har <?= (int)$pending ?> jobberbjudande<?= $pending > 1 ? 'n' : '' ?> att besvara.</strong>
    <a href="/leverantor/jobb.php" style="margin-left:8px">Visa erbjudanden →</a>
  </div>
  <?php endif; ?>

  <div style="display:grid;grid-template-columns:1fr 1fr;gap:24px">
    <!-- Recent jobs -->
    <div class="card">
      <div class="card-header" style="display:flex;justify-content:space-between;align-items:center">
        <h3>Senaste uppdrag</h3>
        <a href="/leverantor/jobb.php" class="btn btn--outline btn--sm">Alla uppdrag</a>
      </div>
      <?php if ($recentJobs): ?>
      <table class="portal-table">
        <thead><tr><th>Projekt</th><th>Status</th><th>Datum</th></tr></thead>
        <tbody>
        <?php foreach ($recentJobs as $j):
          $badge = match($j['status']) {
            'pending'   => 'warning',
            'accepted'  => 'info',
            'completed' => 'success',
            'declined'  => 'error',
            default     => 'default'
          };
          $label = match($j['status']) {
            'pending'   => 'Väntande',
            'accepted'  => 'Accepterat',
            'completed' => 'Slutfört',
            'declined'  => 'Avböjt',
            default     => $j['status']
          };
        ?>
        <tr>
          <td><a href="/leverantor/jobb.php?id=<?= $j['id'] ?>"><?= e($j['project_title']) ?></a></td>
          <td><span class="badge badge--<?= $badge ?>"><?= $label ?></span></td>
          <td style="font-size:.8125rem;color:var(--steel)"><?= substr($j['created_at'],0,10) ?></td>
        </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
      <?php else: ?>
      <p style="padding:24px;color:var(--steel);font-size:.875rem">Inga uppdrag ännu.</p>
      <?php endif; ?>
    </div>

    <!-- Recent time reports -->
    <div class="card">
      <div class="card-header" style="display:flex;justify-content:space-between;align-items:center">
        <h3>Senaste tidrapporter</h3>
        <a href="/leverantor/tidrapport.php" class="btn btn--outline btn--sm">Alla rapporter</a>
      </div>
      <?php if ($recentReports): ?>
      <table class="portal-table">
        <thead><tr><th>Projekt</th><th>Tim/Belopp</th><th>Status</th></tr></thead>
        <tbody>
        <?php foreach ($recentReports as $r): ?>
        <tr>
          <td><?= e($r['project_title']) ?></td>
          <td style="font-size:.875rem"><?= $r['hours'] ? $r['hours'].'h' : '' ?><?= $r['amount'] ? ' · '.number_format($r['amount'],0,',',' ').' kr' : '' ?></td>
          <td><span class="badge badge--<?= $r['paid_at'] ? 'success' : 'warning' ?>"><?= $r['paid_at'] ? 'Betald' : 'Obetald' ?></span></td>
        </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
      <?php else: ?>
      <p style="padding:24px;color:var(--steel);font-size:.875rem">Inga rapporter ännu.</p>
      <?php endif; ?>
    </div>
  </div>
</main>
<?php supp_foot(); ?>
