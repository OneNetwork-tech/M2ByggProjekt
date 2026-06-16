<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/layout.php';
$pu  = portal_require();
$cid = (int)$pu['customer_id'];

$projects = portal_projects($cid);

// Single project view
$pid     = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$project = null;
if ($pid) {
    $s = db()->prepare("SELECT * FROM projects WHERE id = ? AND customer_id = ?");
    $s->execute([$pid, $cid]);
    $project = $s->fetch();
    if (!$project) { header('Location: /portal/projekt.php'); exit; }
}

// Load timeline for single project
$timeline = [];
if ($project) {
    $s = db()->prepare(
        "SELECT t.*, u.name AS staff_name FROM timeline t
         LEFT JOIN users u ON u.id = t.created_by
         WHERE t.entity_type = 'project' AND t.entity_id = ?
         ORDER BY t.created_at DESC LIMIT 30"
    );
    $s->execute([$pid]);
    $timeline = $s->fetchAll();
}

$steps = array_keys(PROJECT_STATUSES);
portal_head($project ? e($project['title']) : 'Projekt', $pu);
portal_nav('/projekt.php');
?>
<main class="portal-main">
<?php if ($project): ?>
  <!-- Single project detail -->
  <div class="portal-page-title">
    <div style="display:flex;align-items:center;gap:12px;flex-wrap:wrap">
      <a href="/portal/projekt.php" style="color:var(--steel);font-size:.85rem">← Alla projekt</a>
    </div>
    <h1 style="margin-top:8px"><?= e($project['title']) ?></h1>
    <p><?= e($project['address'] ?? '') ?><?= $project['city'] ? ', ' . e($project['city']) : '' ?></p>
  </div>

  <!-- Status steps -->
  <div class="card" style="margin-bottom:20px">
    <h3 style="margin-bottom:20px">Projektstatus</h3>
    <div class="status-steps">
      <?php
      $reached = false;
      foreach (PROJECT_STATUSES as $key => $meta):
        $isDone   = false;
        $isActive = $key === $project['status'];
        if ($isActive) $reached = true;
        if (!$reached && !$isActive) $isDone = true;
        $cls = $isActive ? 'status-step--active' : ($isDone ? 'status-step--done' : '');
      ?>
      <div class="status-step <?= $cls ?>">
        <div class="status-step__dot">
          <?php if ($isDone): ?><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" style="width:12px;height:12px"><polyline points="20,6 9,17 4,12"/></svg><?php else: ?><?= array_search($key, $steps) + 1 ?><?php endif; ?>
        </div>
        <div class="status-step__label"><?= e($meta['label']) ?></div>
      </div>
      <?php endforeach; ?>
    </div>
    <?php if ($project['next_step']): ?>
    <p style="font-size:.85rem;color:var(--steel);margin-top:16px;padding-top:16px;border-top:1px solid var(--border)">
      <strong>Nästa steg:</strong> <?= e($project['next_step']) ?>
    </p>
    <?php endif; ?>
  </div>

  <div class="grid-2">
    <!-- Project info -->
    <div class="card">
      <h3 style="margin-bottom:16px">Projektinfo</h3>
      <table style="width:100%;font-size:.875rem;border-collapse:collapse">
        <?php
        $rows = [
          'Projektnummer' => $project['project_no'] ?? '—',
          'Startdatum'    => $project['start_date'] ?? '—',
          'Slutdatum'     => $project['end_date'] ?? '—',
          'Budget'        => $project['budget'] ? number_format($project['budget'], 0, ',', ' ') . ' kr' : '—',
          'Förlopp'       => ($project['progress'] ?? 0) . '%',
        ];
        foreach ($rows as $lbl => $val): ?>
        <tr style="border-bottom:1px solid var(--border)">
          <td style="padding:8px 0;color:var(--steel);width:45%"><?= $lbl ?></td>
          <td style="padding:8px 0;font-weight:600"><?= e($val) ?></td>
        </tr>
        <?php endforeach; ?>
      </table>
    </div>

    <!-- Timeline -->
    <div class="card">
      <h3 style="margin-bottom:16px">Händelselogg</h3>
      <?php if ($timeline): ?>
      <div class="timeline">
        <?php foreach (array_slice($timeline, 0, 8) as $ev): ?>
        <div class="timeline-item">
          <div class="timeline-dot timeline-dot--active">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="4" fill="currentColor"/></svg>
          </div>
          <div class="timeline-content">
            <strong><?= e($ev['title']) ?></strong>
            <time><?= e($ev['created_at']) ?></time>
            <?php if ($ev['body']): ?><p><?= e($ev['body']) ?></p><?php endif; ?>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
      <?php else: ?>
        <p style="color:var(--steel);font-size:.875rem">Ingen historik ännu.</p>
      <?php endif; ?>
    </div>
  </div>

<?php else: ?>
  <!-- Project list -->
  <div class="portal-page-title">
    <h1>Projekt</h1>
    <p>Dina projekt hos M2 Bygg Team.</p>
  </div>

  <?php if ($projects): ?>
  <div style="display:flex;flex-direction:column;gap:12px">
    <?php foreach ($projects as $p):
      $pct = portal_status_pct($p['status']);
    ?>
    <a href="/portal/projekt.php?id=<?= $p['id'] ?>" class="card" style="text-decoration:none;color:inherit;display:block">
      <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:16px;flex-wrap:wrap">
        <div>
          <strong style="font-size:1rem"><?= e($p['title']) ?></strong>
          <div style="font-size:.82rem;color:var(--steel);margin-top:2px"><?= e($p['project_no'] ?? '') ?> · <?= e($p['address'] ?? '') ?></div>
        </div>
        <span class="badge badge--<?= in_array($p['status'],['completed','closed']) ? 'green' : (in_array($p['status'],['in_progress']) ? 'blue' : 'orange') ?>">
          <?= e(portal_status_label($p['status'])) ?>
        </span>
      </div>
      <div style="margin-top:14px">
        <div style="display:flex;justify-content:space-between;font-size:.78rem;color:var(--steel);margin-bottom:6px">
          <span>Förlopp</span><span><?= $pct ?>%</span>
        </div>
        <div class="progress-wrap"><div class="progress-bar" style="width:<?= $pct ?>%"></div></div>
      </div>
    </a>
    <?php endforeach; ?>
  </div>
  <?php else: ?>
  <div class="card" style="text-align:center;padding:48px 24px">
    <p style="color:var(--steel)">Inga projekt ännu. Kontakta oss för en offert!</p>
    <a href="mailto:info@m2team.se" class="btn btn--primary" style="margin-top:16px">Kontakta oss</a>
  </div>
  <?php endif; ?>
<?php endif; ?>
</main>
<?php portal_foot(); ?>
