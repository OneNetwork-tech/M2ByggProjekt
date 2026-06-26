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

// Review submission
$reviewError = '';
if ($project && $_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'submit_review') {
    $existing = db()->prepare("SELECT id FROM reviews WHERE project_id = ?");
    $existing->execute([$pid]);
    if (!in_array($project['status'], ['completed', 'closed'], true)) {
        $reviewError = 'Recensioner kan endast lämnas för avslutade projekt.';
    } elseif ($existing->fetch()) {
        $reviewError = 'Du har redan lämnat en recension för detta projekt.';
    } else {
        $rating = max(1, min(5, (int)($_POST['rating'] ?? 5)));
        $body   = trim($_POST['body'] ?? '');
        if ($body === '') {
            $reviewError = 'Skriv en kommentar innan du skickar in din recension.';
        } else {
            db()->prepare(
                "INSERT INTO reviews (project_id, customer_id, portal_user_id, rating, body) VALUES (?,?,?,?,?)"
            )->execute([$pid, $cid, $pu['id'] ?? null, $rating, $body]);
            notify_role('support', 'Ny kundrecension', $pu['name'] . ' lämnade en recension (' . $rating . '/5) för "' . $project['title'] . '".', '/crm/recensioner.php');
            header('Location: /portal/projekt.php?id=' . $pid . '&msg=' . urlencode('Tack för din recension!'));
            exit;
        }
    }
}

$myReview = null;
if ($project) {
    $rs = db()->prepare("SELECT * FROM reviews WHERE project_id = ? AND customer_id = ?");
    $rs->execute([$pid, $cid]);
    $myReview = $rs->fetch() ?: null;
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

  <?php if (in_array($project['status'], ['completed', 'closed'], true)): ?>
  <div class="card" style="margin-top:20px">
    <h3 style="margin-bottom:16px">Din recension</h3>
    <?php if ($myReview): ?>
      <div class="review-card__stars" style="color:var(--gold);font-size:1.1rem;margin-bottom:8px"><?= str_repeat('★', (int)$myReview['rating']) . str_repeat('☆', 5 - (int)$myReview['rating']) ?></div>
      <p style="color:var(--steel)">"<?= e($myReview['body']) ?>"</p>
      <p style="font-size:.8rem;color:var(--steel);margin-top:10px"><?= $myReview['visible'] ? 'Synlig på webbplatsen.' : 'Väntar på publicering / dold av M2.' ?></p>
      <?php if ($myReview['reply_body']): ?>
      <div style="margin-top:14px;padding:12px 14px;background:var(--surface);border-radius:8px;border-left:3px solid var(--copper)">
        <div style="font-size:.78rem;font-weight:600;color:var(--copper);margin-bottom:4px">Svar från M2 Bygg Team</div>
        <div style="font-size:.85rem;color:var(--steel)"><?= e($myReview['reply_body']) ?></div>
      </div>
      <?php endif; ?>
    <?php else: ?>
      <?php if ($reviewError): ?><p style="color:#c0392b;margin-bottom:12px"><?= e($reviewError) ?></p><?php endif; ?>
      <form method="post" style="max-width:480px">
        <input type="hidden" name="action" value="submit_review">
        <div style="margin-bottom:14px">
          <label style="display:block;font-size:.85rem;color:var(--steel);margin-bottom:6px">Betyg</label>
          <select name="rating" class="input" style="width:auto">
            <?php for ($i = 5; $i >= 1; $i--): ?>
            <option value="<?= $i ?>"><?= str_repeat('★', $i) ?> (<?= $i ?>/5)</option>
            <?php endfor; ?>
          </select>
        </div>
        <div style="margin-bottom:14px">
          <label style="display:block;font-size:.85rem;color:var(--steel);margin-bottom:6px">Din kommentar</label>
          <textarea name="body" rows="4" class="input" style="width:100%" placeholder="Berätta om din upplevelse..." required></textarea>
        </div>
        <button type="submit" class="btn btn--primary">Skicka recension</button>
      </form>
    <?php endif; ?>
  </div>
  <?php endif; ?>

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
