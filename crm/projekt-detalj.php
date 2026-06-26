<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/mailer.php';
$me = require_login();
$pdo = db();

$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT p.*, c.name AS customer_name, c.phone AS customer_phone, c.email AS customer_email FROM projects p LEFT JOIN customers c ON c.id=p.customer_id WHERE p.id=?");
$stmt->execute([$id]);
$proj = $stmt->fetch();
if (!$proj) { header('Location: projekt.php'); exit; }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $action = $_POST['action'] ?? '';

    if ($action === 'status') {
        $status = $_POST['status'];
        if (isset(PROJECT_STATUSES[$status])) {
            // Auto progress based on status (blueprint: timeline events logged automatically)
            $progressMap = ['lead'=>0,'inspection'=>10,'planning'=>25,'scheduled'=>40,'in_progress'=>60,'quality'=>85,'completed'=>100,'closed'=>100];
            $pdo->prepare("UPDATE projects SET status=?, progress=? WHERE id=?")->execute([$status, $progressMap[$status] ?? $proj['progress'], $id]);
            log_timeline('project', $id, 'status', 'Status: ' . PROJECT_STATUSES[$status]['label'], '', $me['id']);
            audit('project_status', 'project', $id, $status);

            // AUTOMATION: Project Completed → Request Review (per blueprint)
            if ($status === 'completed') {
                log_timeline('project', $id, 'system', 'Automation: Be kunden om recension', 'Skicka recensionsförfrågan till ' . ($proj['customer_email'] ?: 'kunden'), $me['id']);
                notify_role('support', 'Be om recension: ' . $proj['title'], 'Projektet är slutfört – dags att be kunden om en recension.', "projekt-detalj.php?id=$id");

                if (!empty($proj['customer_email'])) {
                    $portalUrl = (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? 'm2team.se') . '/portal/projekt.php?id=' . $id;
                    crm_send_mail(
                        $proj['customer_email'], $proj['customer_name'] ?: $proj['customer_email'],
                        'Tack för att du valde M2 Bygg Team!',
                        '<p>Hej ' . htmlspecialchars($proj['customer_name'] ?? '', ENT_QUOTES, 'UTF-8') . '!</p><p>Ditt projekt <strong>' . htmlspecialchars($proj['title'], ENT_QUOTES, 'UTF-8') . '</strong> är nu slutfört. Vi hoppas att du är nöjd med resultatet!</p><p>Vi skulle bli väldigt glada om du tog en minut att lämna en recension i kundportalen – den visas på vår webbplats och hjälper andra kunder att hitta oss.</p>',
                        'project', $id, $portalUrl, 'Lämna en recension'
                    );
                }
            }
            flash('Status uppdaterad.');
        }
        header("Location: projekt-detalj.php?id=$id"); exit;
    }

    if ($action === 'update') {
        $pdo->prepare("UPDATE projects SET title=?, address=?, city=?, budget=?, start_date=?, end_date=?, progress=?, next_step=?, supplier_id=? WHERE id=?")
            ->execute([trim($_POST['title']), trim($_POST['address']), trim($_POST['city']), (float)$_POST['budget'],
                       $_POST['start_date'] ?: null, $_POST['end_date'] ?: null, min(100, max(0, (int)$_POST['progress'])),
                       trim($_POST['next_step']), $_POST['supplier_id'] ?: null, $id]);
        if (!empty($_POST['supplier_id']) && $_POST['supplier_id'] != $proj['supplier_id']) {
            $sn = $pdo->prepare("SELECT company FROM suppliers WHERE id=?"); $sn->execute([(int)$_POST['supplier_id']]);
            log_timeline('project', $id, 'system', 'Leverantör tilldelad: ' . ($sn->fetchColumn() ?: '–'), '', $me['id']);
        }
        flash('Projekt uppdaterat.');
        header("Location: projekt-detalj.php?id=$id"); exit;
    }

    if ($action === 'assign_supplier') {
        $suppId = (int)$_POST['supplier_id'];
        $note   = trim($_POST['crm_note'] ?? '');
        $start  = $_POST['start_date'] ?: null;
        $hours  = $_POST['estimated_hours'] !== '' ? (float)$_POST['estimated_hours'] : null;
        $rate   = $_POST['rate'] !== '' ? (float)$_POST['rate'] : null;
        if ($suppId) {
            $pdo->prepare(
                "INSERT INTO job_assignments (project_id, supplier_id, status, crm_note, start_date, estimated_hours, rate) VALUES (?,?,?,?,?,?,?)"
            )->execute([$id, $suppId, 'pending', $note, $start, $hours, $rate]);
            $sn = $pdo->prepare("SELECT company FROM suppliers WHERE id=?"); $sn->execute([$suppId]);
            $company = $sn->fetchColumn() ?: '–';
            log_timeline('project', $id, 'system', "Jobberbjudande skickat till $company", $note, $me['id']);
            flash("Jobberbjudande skapat för $company. Bjud in dem via leverantörsidan om de inte har portalåtkomst.");
        }
        header("Location: projekt-detalj.php?id=$id"); exit;
    }

    if ($action === 'rate_supplier') {
        $jaId    = (int)($_POST['job_assignment_id'] ?? 0);
        $rating  = max(1, min(5, (int)($_POST['rating'] ?? 5)));
        $note    = trim($_POST['note'] ?? '');
        $ja = $pdo->prepare("SELECT * FROM job_assignments WHERE id = ? AND project_id = ?");
        $ja->execute([$jaId, $id]);
        $jaRow = $ja->fetch();
        if ($jaRow) {
            $existing = $pdo->prepare("SELECT id FROM supplier_ratings WHERE job_assignment_id = ?");
            $existing->execute([$jaId]);
            if ($existing->fetchColumn()) {
                $pdo->prepare("UPDATE supplier_ratings SET rating=?, note=?, rated_by=?, created_at=datetime('now','localtime') WHERE job_assignment_id=?")
                    ->execute([$rating, $note, $me['id'], $jaId]);
            } else {
                $pdo->prepare("INSERT INTO supplier_ratings (job_assignment_id, supplier_id, project_id, rating, note, rated_by) VALUES (?,?,?,?,?,?)")
                    ->execute([$jaId, $jaRow['supplier_id'], $id, $rating, $note, $me['id']]);
            }
            // Keep suppliers.rating in sync as a running average across all rated jobs
            $avg = $pdo->prepare("SELECT AVG(rating) FROM supplier_ratings WHERE supplier_id = ?");
            $avg->execute([$jaRow['supplier_id']]);
            $pdo->prepare("UPDATE suppliers SET rating = ? WHERE id = ?")->execute([round((float)$avg->fetchColumn(), 2), $jaRow['supplier_id']]);
            audit('supplier_rate', 'job_assignment', $jaId, (string)$rating);
            flash('Betyg sparat.');
        }
        header("Location: projekt-detalj.php?id=$id"); exit;
    }

    if ($action === 'note') {
        log_timeline('project', $id, $_POST['type'] ?? 'note', trim($_POST['title']), trim($_POST['body'] ?? ''), $me['id']);
        flash('Sparad.');
        header("Location: projekt-detalj.php?id=$id"); exit;
    }
}

$tl = $pdo->prepare("SELECT * FROM timeline WHERE entity_type='project' AND entity_id=? ORDER BY created_at DESC");
$tl->execute([$id]); $timeline = $tl->fetchAll();
$suppliers = $pdo->query("SELECT id, company FROM suppliers WHERE status IN ('verified','active') ORDER BY company")->fetchAll();

$s = $pdo->prepare("
    SELECT ja.*, s.company AS supplier_company,
           (SELECT COUNT(*) FROM job_photos jp WHERE jp.job_assignment_id = ja.id) AS photo_count,
           sr.rating AS my_rating, sr.note AS my_rating_note
    FROM job_assignments ja JOIN suppliers s ON s.id=ja.supplier_id
    LEFT JOIN supplier_ratings sr ON sr.job_assignment_id = ja.id
    WHERE ja.project_id=? ORDER BY ja.created_at DESC
");
$s->execute([$id]); $jobAssignments = $s->fetchAll();

$s = $pdo->prepare("SELECT COUNT(*) FROM portal_documents WHERE project_id=?");
$s->execute([$id]); $portalDocCount = (int)$s->fetchColumn();

$s = $pdo->prepare("SELECT COUNT(*) FROM portal_messages WHERE project_id=? AND sender_type='customer' AND read_at IS NULL");
$s->execute([$id]); $unreadPortalCount = (int)$s->fetchColumn();
$invoices = $pdo->prepare("SELECT * FROM invoices WHERE project_id=? OR quote_id=? ORDER BY created_at DESC");
$invoices->execute([$id, $proj['quote_id'] ?: 0]); $invoices = $invoices->fetchAll();

$crm_title = $proj['title'];
$crm_page  = 'projekt';
require_once __DIR__ . '/includes/crm-header.php';
?>

<div class="topbar">
  <div>
    <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap">
      <a href="projekt.php" style="color:var(--gray-lt);display:flex"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:18px;height:18px"><path d="M19 12H5M12 19l-7-7 7-7"/></svg></a>
      <h1><?= e($proj['title']) ?></h1>
      <?= badge($proj['status'], PROJECT_STATUSES) ?>
    </div>
    <div class="topbar__sub"><?= e($proj['project_no']) ?> · <?= e($proj['customer_name'] ?: 'Ingen kund') ?> · Budget <?= money($proj['budget']) ?></div>
  </div>
  <div class="topbar__actions">
    <?php if ($proj['quote_id']): ?><a href="offert.php?id=<?= $proj['quote_id'] ?>" class="btn btn--ghost">Visa offert</a><?php endif; ?>
    <?php if ($proj['customer_id']): ?><a href="kund.php?id=<?= $proj['customer_id'] ?>" class="btn btn--ghost">Visa kund</a><?php endif; ?>
    <a href="kalender.php?project=<?= $id ?>" class="btn btn--ghost">📅 Boka besök</a>
    <a href="portal-dokument.php?project=<?= $id ?>" class="btn btn--ghost" title="Ladda upp till kundportal">📎 Portal dok.</a>
    <a href="meddelanden.php?view=portal" class="btn btn--ghost" title="Kundportal-meddelanden">💬 Portal msg</a>
  </div>
</div>

<?php flash(); ?>

<!-- STATUS PIPELINE (per blueprint: Lead→Inspection→Planning→Scheduled→In Progress→Quality→Completed→Closed) -->
<div class="card card--pad" style="margin-bottom:16px;overflow-x:auto">
  <form method="POST" id="psForm">
    <?= csrf_field() ?><input type="hidden" name="action" value="status"><input type="hidden" name="status" id="psInput">
    <div style="display:flex;gap:6px;min-width:840px">
      <?php
      $keys = array_keys(PROJECT_STATUSES);
      $curIdx = array_search($proj['status'], $keys);
      foreach (PROJECT_STATUSES as $key => $cfg):
        $idx = array_search($key, $keys);
        $isCur = $key === $proj['status']; $isPast = $idx < $curIdx;
      ?>
      <button type="button" onclick="document.getElementById('psInput').value='<?= $key ?>';document.getElementById('psForm').submit()"
        style="flex:1;padding:9px 5px;border-radius:9px;border:1.5px solid <?= $isCur ? $cfg['color'] : 'var(--border)' ?>;background:<?= $isCur ? $cfg['color'] : ($isPast ? $cfg['color'].'14' : 'var(--card)') ?>;color:<?= $isCur ? '#fff' : ($isPast ? $cfg['color'] : 'var(--gray)') ?>;font-size:11.5px;font-weight:550;cursor:pointer;white-space:nowrap">
        <?= e($cfg['label']) ?>
      </button>
      <?php endforeach; ?>
    </div>
  </form>
  <div style="display:flex;align-items:center;gap:12px;margin-top:14px">
    <div class="progress" style="flex:1"><div class="progress__bar" style="width:<?= (int)$proj['progress'] ?>%"></div></div>
    <strong style="font-size:13px"><?= (int)$proj['progress'] ?>%</strong>
  </div>
  <?php if ($proj['next_step']): ?>
  <div style="margin-top:12px;background:var(--blue-lt);border-radius:9px;padding:10px 14px;font-size:13px">
    <strong style="color:var(--blue)">Nästa steg:</strong> <?= e($proj['next_step']) ?>
    <?php if ($proj['start_date']): ?><span style="color:var(--gray)"> · Start <?= dt($proj['start_date']) ?></span><?php endif; ?>
  </div>
  <?php endif; ?>
</div>

<div class="detail-grid">
  <div style="display:flex;flex-direction:column;gap:16px">

    <!-- LOG EVENT -->
    <div class="card card--pad">
      <h3 style="font-size:14.5px;margin-bottom:12px">Logga händelse</h3>
      <form method="POST">
        <?= csrf_field() ?><input type="hidden" name="action" value="note">
        <div class="frow" style="grid-template-columns:140px 1fr">
          <div class="fg"><label>Typ</label>
            <select class="fs" name="type">
              <option value="note">Anteckning</option><option value="call">Samtal</option>
              <option value="meeting">Platsbesök</option><option value="email">E-post</option>
            </select>
          </div>
          <div class="fg"><label>Rubrik</label><input class="fi" name="title" required placeholder="T.ex. Material levererat"></div>
        </div>
        <div class="fg"><textarea class="fta" name="body" style="min-height:60px" placeholder="Detaljer..."></textarea></div>
        <div style="display:flex;justify-content:flex-end"><button class="btn btn--primary btn--sm">Spara</button></div>
      </form>
    </div>

    <!-- PROJECT TIMELINE -->
    <div class="card card--pad">
      <h3 style="font-size:14.5px;margin-bottom:18px">Projekttidslinje</h3>
      <div class="timeline">
        <?php if (!$timeline): ?><p style="font-size:13px;color:var(--gray)">Ingen aktivitet.</p><?php endif; ?>
        <?php foreach ($timeline as $ev): ?>
        <div class="tl-item">
          <div class="tl-dot <?= $ev['type'] === 'status' ? 'amber' : ($ev['type'] === 'system' ? 'gray' : '') ?>"></div>
          <div class="tl-title"><?= e($ev['title']) ?></div>
          <?php if ($ev['body']): ?><div class="tl-body"><?= e($ev['body']) ?></div><?php endif; ?>
          <div class="tl-meta"><?= e(user_name($ev['created_by'])) ?> · <?= dt($ev['created_at'], 'j M H:i') ?></div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>

  <div style="display:flex;flex-direction:column;gap:16px">

    <!-- EDIT DETAILS -->
    <div class="card card--pad">
      <h3 style="font-size:14.5px;margin-bottom:12px">Projektdetaljer</h3>
      <form method="POST">
        <?= csrf_field() ?><input type="hidden" name="action" value="update">
        <div class="fg"><label>Titel</label><input class="fi" name="title" value="<?= e($proj['title']) ?>"></div>
        <div class="fg"><label>Adress</label><input class="fi" name="address" value="<?= e($proj['address']) ?>"></div>
        <div class="fg"><label>Stad</label><input class="fi" name="city" value="<?= e($proj['city']) ?>"></div>
        <div class="frow">
          <div class="fg"><label>Budget (kr)</label><input class="fi" type="number" name="budget" value="<?= (float)$proj['budget'] ?>"></div>
          <div class="fg"><label>Förlopp (%)</label><input class="fi" type="number" name="progress" min="0" max="100" value="<?= (int)$proj['progress'] ?>"></div>
        </div>
        <div class="frow">
          <div class="fg"><label>Startdatum</label><input class="fi" type="date" name="start_date" value="<?= e($proj['start_date']) ?>"></div>
          <div class="fg"><label>Slutdatum</label><input class="fi" type="date" name="end_date" value="<?= e($proj['end_date']) ?>"></div>
        </div>
        <div class="fg"><label>Tilldelad leverantör</label>
          <select class="fs" name="supplier_id">
            <option value="">– Ingen –</option>
            <?php foreach ($suppliers as $s): ?>
            <option value="<?= $s['id'] ?>" <?= $proj['supplier_id'] == $s['id'] ? 'selected' : '' ?>><?= e($s['company']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="fg"><label>Nästa steg</label><input class="fi" name="next_step" value="<?= e($proj['next_step']) ?>"></div>
        <button class="btn btn--primary btn--sm" style="width:100%;justify-content:center">Spara</button>
      </form>
    </div>

    <!-- CUSTOMER -->
    <?php if ($proj['customer_id']): ?>
    <div class="card card--pad">
      <h3 style="font-size:14.5px;margin-bottom:10px">Kund</h3>
      <div style="display:flex;align-items:center;gap:10px;margin-bottom:10px">
        <div class="avatar"><?= e(initials($proj['customer_name'])) ?></div>
        <div>
          <div style="font-weight:550;font-size:14px"><?= e($proj['customer_name']) ?></div>
          <div style="font-size:12px;color:var(--gray-lt)"><?= e($proj['customer_phone'] ?: '') ?></div>
        </div>
      </div>
      <?php if ($proj['customer_phone']): ?>
      <a href="tel:<?= e(preg_replace('/\s+/','',$proj['customer_phone'])) ?>" class="btn btn--ghost btn--sm" style="width:100%;justify-content:center">Ring kund</a>
      <?php endif; ?>
    </div>
    <?php endif; ?>

    <!-- PORTAL QUICK STATS -->
    <?php if ($proj['customer_id'] && ($portalDocCount || $unreadPortalCount)): ?>
    <div class="card card--pad" style="display:flex;gap:16px">
      <?php if ($unreadPortalCount): ?>
      <a href="meddelanden.php?view=portal" style="flex:1;text-align:center;text-decoration:none">
        <div style="font-size:22px;font-weight:700;color:var(--blue)"><?= $unreadPortalCount ?></div>
        <div style="font-size:11.5px;color:var(--gray)">Olästa kundmeddelanden</div>
      </a>
      <?php endif; ?>
      <?php if ($portalDocCount): ?>
      <a href="portal-dokument.php?project=<?= $id ?>" style="flex:1;text-align:center;text-decoration:none">
        <div style="font-size:22px;font-weight:700;color:var(--ink)"><?= $portalDocCount ?></div>
        <div style="font-size:11.5px;color:var(--gray)">Portaldokument</div>
      </a>
      <?php endif; ?>
    </div>
    <?php endif; ?>

    <!-- JOB ASSIGNMENTS -->
    <div class="card card--pad">
      <h3 style="font-size:14.5px;margin-bottom:12px">Leverantörsuppdrag</h3>
      <?php if ($jobAssignments): ?>
      <?php foreach ($jobAssignments as $ja):
        $jaColors = ['pending'=>'#F59E0B','accepted'=>'#3B82F6','completed'=>'#10B981','declined'=>'#EF4444'];
        $jaLabels = ['pending'=>'Väntar','accepted'=>'Accepterat','completed'=>'Slutfört','declined'=>'Avböjt'];
      ?>
      <div style="display:flex;justify-content:space-between;align-items:center;padding:8px 0;border-bottom:1px solid #F3F4F6;font-size:13px">
        <div>
          <div style="font-weight:550"><?= e($ja['supplier_company']) ?></div>
          <?php if ($ja['crm_note']): ?><div style="font-size:11.5px;color:var(--gray)"><?= e(mb_strimwidth($ja['crm_note'],0,60,'…')) ?></div><?php endif; ?>
          <?php if ($ja['photo_count']): ?><a href="leverantor-foton.php?job=<?= $ja['id'] ?>" style="font-size:11px;color:var(--blue)">📷 <?= $ja['photo_count'] ?> bild<?= $ja['photo_count']>1?'er':'' ?></a><?php endif; ?>
          <?php if ($ja['my_rating']): ?><div style="font-size:11px;color:#D97706;margin-top:2px"><?= str_repeat('★', (int)$ja['my_rating']) . str_repeat('☆', 5 - (int)$ja['my_rating']) ?></div><?php endif; ?>
        </div>
        <div style="display:flex;align-items:center;gap:8px">
          <span style="font-size:11px;font-weight:600;color:<?= $jaColors[$ja['status']] ?? '#999' ?>;background:<?= $jaColors[$ja['status']] ?? '#999' ?>18;padding:3px 8px;border-radius:20px"><?= $jaLabels[$ja['status']] ?? $ja['status'] ?></span>
          <?php if ($ja['status'] === 'completed'): ?>
          <a href="#" onclick="event.preventDefault();openRatingModal(<?= $ja['id'] ?>, <?= (int)($ja['my_rating'] ?: 5) ?>, <?= htmlspecialchars(json_encode($ja['my_rating_note']), ENT_QUOTES) ?>)" style="font-size:11px">
            <?= $ja['my_rating'] ? 'Ändra betyg' : 'Betygsätt' ?>
          </a>
          <?php endif; ?>
        </div>
      </div>
      <?php endforeach; ?>
      <div style="margin-top:10px"></div>
      <?php endif; ?>
      <!-- New assignment form -->
      <form method="POST" style="display:flex;flex-direction:column;gap:8px;margin-top:4px">
        <?= csrf_field() ?><input type="hidden" name="action" value="assign_supplier">
        <div class="fg" style="margin:0"><label style="font-size:12px">Välj leverantör</label>
          <select class="fs" name="supplier_id" required>
            <option value="">— Välj —</option>
            <?php foreach ($suppliers as $s): ?>
            <option value="<?= $s['id'] ?>"><?= e($s['company']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="frow" style="gap:8px;margin:0">
          <div class="fg" style="margin:0"><label style="font-size:12px">Startdatum</label><input class="fi" type="date" name="start_date" style="font-size:12px"></div>
          <div class="fg" style="margin:0"><label style="font-size:12px">Est. timmar</label><input class="fi" type="number" name="estimated_hours" min="0" step="0.5" placeholder="—" style="font-size:12px"></div>
          <div class="fg" style="margin:0"><label style="font-size:12px">Timtaxa (kr)</label><input class="fi" type="number" name="rate" min="0" step="1" placeholder="—" style="font-size:12px"></div>
        </div>
        <div class="fg" style="margin:0"><label style="font-size:12px">Meddelande till leverantör</label><textarea class="fta" name="crm_note" rows="2" style="min-height:44px;font-size:12.5px" placeholder="Beskriv uppdraget…"></textarea></div>
        <button class="btn btn--primary btn--sm" style="align-self:flex-start">+ Skicka jobberbjudande</button>
      </form>
    </div>

    <!-- INVOICES -->
    <div class="card card--pad">
      <h3 style="font-size:14.5px;margin-bottom:10px">Fakturor</h3>
      <?php if (!$invoices): ?><p style="font-size:13px;color:var(--gray)">Inga fakturor kopplade.</p><?php endif; ?>
      <?php foreach ($invoices as $inv): ?>
      <a href="faktura.php?id=<?= $inv['id'] ?>" style="display:flex;justify-content:space-between;align-items:center;padding:8px 0;border-bottom:1px solid #F3F4F6;font-size:13px">
        <span style="font-weight:550"><?= e($inv['invoice_no']) ?></span>
        <?= badge($inv['status'], INVOICE_STATUSES) ?>
      </a>
      <?php endforeach; ?>
    </div>

  </div>
</div>

<div class="modal-bg" id="ratingModal">
  <div class="modal">
    <h3>Betygsätt leverantörens arbete</h3>
    <form method="post">
      <?= csrf_field() ?><input type="hidden" name="action" value="rate_supplier"><input type="hidden" name="job_assignment_id" id="sr_ja_id" value="">
      <div class="fg"><label>Betyg</label>
        <select class="fs" name="rating" id="sr_rating">
          <?php for ($i = 5; $i >= 1; $i--): ?><option value="<?= $i ?>"><?= str_repeat('★', $i) ?> (<?= $i ?>/5)</option><?php endfor; ?>
        </select>
      </div>
      <div class="fg"><label>Anteckning (intern, ej synlig för leverantören)</label>
        <textarea class="fi" name="note" id="sr_note" rows="3" placeholder="T.ex. kvalitet, punktlighet, kommunikation..."></textarea>
      </div>
      <div style="display:flex;gap:10px;justify-content:flex-end">
        <button type="button" class="btn btn--ghost" onclick="closeModal('ratingModal')">Avbryt</button>
        <button class="btn btn--primary">Spara betyg</button>
      </div>
    </form>
  </div>
</div>

<script>
function openRatingModal(jaId, rating, note) {
  document.getElementById('sr_ja_id').value = jaId;
  document.getElementById('sr_rating').value = rating;
  document.getElementById('sr_note').value = note || '';
  openModal('ratingModal');
}
</script>

<?php require_once __DIR__ . '/includes/crm-footer.php'; ?>
