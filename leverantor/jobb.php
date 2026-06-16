<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/layout.php';
$su  = supp_require();
$sid = (int)$su['supplier_id'];

// Single job view / respond
$detail = null;
if (!empty($_GET['id'])) {
    $s = db()->prepare("
        SELECT ja.*, p.title AS project_title, p.address, p.description AS project_desc, p.status AS project_status
        FROM job_assignments ja JOIN projects p ON p.id=ja.project_id
        WHERE ja.id=? AND ja.supplier_id=?
    ");
    $s->execute([(int)$_GET['id'], $sid]);
    $detail = $s->fetch() ?: null;
}

// Accept / decline
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['respond'])) {
    $jid    = (int)$_POST['job_id'];
    $action = $_POST['action'] === 'accept' ? 'accepted' : 'declined';
    $note   = trim($_POST['note'] ?? '');
    $s = db()->prepare("SELECT id FROM job_assignments WHERE id=? AND supplier_id=?");
    $s->execute([$jid, $sid]);
    if ($s->fetch()) {
        db()->prepare("UPDATE job_assignments SET status=?, supplier_note=?, responded_at=datetime('now','localtime') WHERE id=?")
           ->execute([$action, $note, $jid]);
    }
    header('Location: /leverantor/jobb.php?id='.$jid); exit;
}

// Job list
$s = db()->prepare("
    SELECT ja.*, p.title AS project_title, p.address
    FROM job_assignments ja JOIN projects p ON p.id=ja.project_id
    WHERE ja.supplier_id=?
    ORDER BY CASE ja.status WHEN 'pending' THEN 0 WHEN 'accepted' THEN 1 ELSE 2 END, ja.created_at DESC
");
$s->execute([$sid]);
$jobs = $s->fetchAll();

supp_head('Jobberbjudanden', $su);
supp_nav('/jobb.php');
?>
<main class="portal-main">
<?php if ($detail): ?>
  <!-- Single job -->
  <div style="margin-bottom:16px"><a href="/leverantor/jobb.php" style="color:var(--steel);text-decoration:none">← Alla uppdrag</a></div>
  <div class="portal-page-title">
    <h1><?= e($detail['project_title']) ?></h1>
    <p><?= e($detail['address'] ?? '') ?></p>
  </div>

  <div style="display:grid;grid-template-columns:2fr 1fr;gap:24px">
    <div>
      <div class="card" style="margin-bottom:20px">
        <div class="card-header"><h3>Uppdragsinformation</h3></div>
        <div style="padding:20px">
          <?php if ($detail['description']): ?>
          <p style="margin-bottom:16px"><?= nl2br(e($detail['description'])) ?></p>
          <?php endif; ?>
          <table style="width:100%;border-collapse:collapse;font-size:.875rem">
            <tr><td style="padding:6px 0;color:var(--steel);width:140px">Plats</td><td><?= e($detail['address'] ?? '—') ?></td></tr>
            <tr><td style="padding:6px 0;color:var(--steel)">Skickat</td><td><?= e($detail['created_at']) ?></td></tr>
            <?php if ($detail['start_date']): ?><tr><td style="padding:6px 0;color:var(--steel)">Startdatum</td><td><?= e($detail['start_date']) ?></td></tr><?php endif; ?>
            <?php if ($detail['estimated_hours']): ?><tr><td style="padding:6px 0;color:var(--steel)">Uppskattad tid</td><td><?= e($detail['estimated_hours']) ?> timmar</td></tr><?php endif; ?>
            <?php if ($detail['rate']): ?><tr><td style="padding:6px 0;color:var(--steel)">Timersättning</td><td><?= number_format($detail['rate'],0,',',' ') ?> kr/h</td></tr><?php endif; ?>
          </table>
        </div>
      </div>

      <?php if ($detail['crm_note']): ?>
      <div class="card" style="margin-bottom:20px">
        <div class="card-header"><h3>Meddelande från M2 Bygg Team</h3></div>
        <div style="padding:20px;font-size:.875rem"><?= nl2br(e($detail['crm_note'])) ?></div>
      </div>
      <?php endif; ?>

      <?php if ($detail['status'] === 'pending'): ?>
      <div class="card">
        <div class="card-header"><h3>Svara på erbjudandet</h3></div>
        <div style="padding:20px">
          <form method="post">
            <input type="hidden" name="respond" value="1">
            <input type="hidden" name="job_id" value="<?= $detail['id'] ?>">
            <div class="form-group">
              <label class="form-label">Meddelande (valfritt)</label>
              <textarea class="form-control" name="note" rows="3" placeholder="Eventuell kommentar till M2..."></textarea>
            </div>
            <div style="display:flex;gap:12px">
              <button type="submit" name="action" value="accept" class="btn btn--primary">Acceptera uppdrag</button>
              <button type="submit" name="action" value="decline" class="btn btn--outline" style="color:var(--error)">Avböj</button>
            </div>
          </form>
        </div>
      </div>
      <?php endif; ?>

      <?php if ($detail['supplier_note'] && $detail['status'] !== 'pending'): ?>
      <div class="card">
        <div class="card-header"><h3>Ditt svar</h3></div>
        <div style="padding:20px;font-size:.875rem"><?= nl2br(e($detail['supplier_note'])) ?></div>
      </div>
      <?php endif; ?>
    </div>

    <div>
      <div class="card">
        <div class="card-header"><h3>Status</h3></div>
        <div style="padding:20px">
          <?php
          $badge = match($detail['status']) {
            'pending'   => 'warning',
            'accepted'  => 'info',
            'completed' => 'success',
            'declined'  => 'error',
            default     => 'default'
          };
          $label = match($detail['status']) {
            'pending'   => 'Väntande svar',
            'accepted'  => 'Accepterat',
            'completed' => 'Slutfört',
            'declined'  => 'Avböjt',
            default     => $detail['status']
          };
          ?>
          <span class="badge badge--<?= $badge ?>" style="font-size:1rem;padding:8px 16px"><?= $label ?></span>
          <?php if ($detail['responded_at']): ?>
          <p style="font-size:.8125rem;color:var(--steel);margin-top:12px">Svarade: <?= e($detail['responded_at']) ?></p>
          <?php endif; ?>
        </div>
      </div>

      <?php if ($detail['status'] === 'accepted'): ?>
      <div class="card" style="margin-top:16px">
        <div class="card-header"><h3>Snabblänkar</h3></div>
        <div style="padding:16px;display:flex;flex-direction:column;gap:10px">
          <a href="/leverantor/tidrapport.php?job=<?= $detail['id'] ?>" class="btn btn--primary btn--sm">+ Ny tidrapport</a>
          <a href="/leverantor/tidrapport.php?job=<?= $detail['id'] ?>" class="btn btn--outline btn--sm">Visa tidrapporter</a>
        </div>
      </div>
      <?php endif; ?>
    </div>
  </div>

<?php else: ?>
  <!-- List -->
  <div class="portal-page-title"><h1>Jobberbjudanden</h1><p>Uppdrag från M2 Bygg Team.</p></div>

  <?php if ($jobs): ?>
  <div style="display:flex;flex-direction:column;gap:12px">
  <?php foreach ($jobs as $j):
    $badge = match($j['status']) { 'pending'=>'warning','accepted'=>'info','completed'=>'success','declined'=>'error',default=>'default' };
    $label = match($j['status']) { 'pending'=>'Väntande','accepted'=>'Accepterat','completed'=>'Slutfört','declined'=>'Avböjt',default=>$j['status'] };
  ?>
  <a href="/leverantor/jobb.php?id=<?= $j['id'] ?>" class="card" style="display:block;padding:20px;text-decoration:none;color:inherit;transition:box-shadow .2s" onmouseover="this.style.boxShadow='0 0 0 2px var(--brand)'" onmouseout="this.style.boxShadow=''">
    <div style="display:flex;justify-content:space-between;align-items:flex-start">
      <div>
        <div style="font-weight:600;margin-bottom:4px"><?= e($j['project_title']) ?></div>
        <div style="font-size:.8125rem;color:var(--steel)"><?= e($j['address'] ?? '') ?> · <?= substr($j['created_at'],0,10) ?></div>
      </div>
      <span class="badge badge--<?= $badge ?>"><?= $label ?></span>
    </div>
  </a>
  <?php endforeach; ?>
  </div>
  <?php else: ?>
  <div class="card" style="text-align:center;padding:48px">
    <p style="color:var(--steel)">Inga jobberbjudanden ännu.</p>
  </div>
  <?php endif; ?>
<?php endif; ?>
</main>
<?php supp_foot(); ?>
