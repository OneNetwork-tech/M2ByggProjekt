<?php
/**
 * CRM — Upload a document to a customer's portal
 * ?project=ID or ?customer=ID
 */
require_once __DIR__ . '/includes/auth.php';
$me = require_role(['super_admin','project','support']);
$pdo = db();

$pid = (int)($_GET['project'] ?? $_POST['project_id'] ?? 0);
$cid = (int)($_GET['customer'] ?? 0);

// Resolve project → customer or customer → projects
$project = null;
$projects = [];
if ($pid) {
    $s = $pdo->prepare("SELECT p.*, c.name AS customer_name FROM projects p LEFT JOIN customers c ON c.id=p.customer_id WHERE p.id=?");
    $s->execute([$pid]); $project = $s->fetch();
    if ($project) $cid = (int)$project['customer_id'];
}
if ($cid && !$project) {
    $s = $pdo->prepare("SELECT * FROM projects WHERE customer_id=? ORDER BY created_at DESC");
    $s->execute([$cid]); $projects = $s->fetchAll();
}

$ALLOWED_MIME = ['application/pdf','image/jpeg','image/png','image/webp',
                  'application/msword','application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
$UPLOAD_DIR   = dirname(__DIR__) . '/data/portal-uploads/';
if (!is_dir($UPLOAD_DIR)) mkdir($UPLOAD_DIR, 0750, true);

$error = ''; $success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    csrf_check();
    $uploadPid = (int)$_POST['project_id'];
    $desc      = trim($_POST['description'] ?? '');
    $f         = $_FILES['file'];

    if ($f['error'] !== UPLOAD_ERR_OK)             $error = 'Uppladdningsfel (' . $f['error'] . ').';
    elseif ($f['size'] > 10_485_760)               $error = 'Filen är för stor (max 10 MB).';
    elseif (!in_array(mime_content_type($f['tmp_name']), $ALLOWED_MIME)) $error = 'Filtypen är inte tillåten.';
    elseif (!$uploadPid)                            $error = 'Välj ett projekt.';
    else {
        $ext      = strtolower(pathinfo($f['name'], PATHINFO_EXTENSION));
        $safeName = preg_replace('/[^a-z0-9_\-]/i', '_', pathinfo($f['name'], PATHINFO_FILENAME));
        $stored   = 'p' . $uploadPid . '_' . time() . '_' . $safeName . '.' . $ext;
        move_uploaded_file($f['tmp_name'], $UPLOAD_DIR . $stored);

        $pdo->prepare(
            "INSERT INTO portal_documents (project_id, uploaded_by_type, uploaded_by_id, filename, original_name, mime_type, filesize, description)
             VALUES (?,?,?,?,?,?,?,?)"
        )->execute([$uploadPid, 'staff', $me['id'], $stored, $f['name'],
                    mime_content_type($UPLOAD_DIR.$stored), $f['size'], $desc]);

        audit('portal_doc_upload', 'project', $uploadPid, $f['name']);
        $success = 'Dokumentet har laddats upp till kundportalen.';

        // Reload project
        if (!$project) {
            $s = $pdo->prepare("SELECT p.*, c.name AS customer_name FROM projects p LEFT JOIN customers c ON c.id=p.customer_id WHERE p.id=?");
            $s->execute([$uploadPid]); $project = $s->fetch();
            $pid = $uploadPid;
        }
    }
}

// Existing docs for selected project
$docs = [];
if ($pid) {
    $s = $pdo->prepare("
        SELECT pd.*, u.name AS uploader
        FROM portal_documents pd LEFT JOIN users u ON u.id=pd.uploaded_by_id AND pd.uploaded_by_type='staff'
        WHERE pd.project_id=? ORDER BY pd.created_at DESC
    ");
    $s->execute([$pid]); $docs = $s->fetchAll();
}

$crm_title = 'Portal dokument';
$crm_page  = 'projekt';
require_once __DIR__ . '/includes/crm-header.php';
?>
<div class="topbar">
  <div>
    <h1>Ladda upp till kundportal</h1>
    <div class="topbar__sub"><?= $project ? e($project['customer_name'].' — '.$project['title']) : 'Välj projekt' ?></div>
  </div>
  <div class="topbar__actions">
    <?php if ($pid): ?><a href="projekt-detalj.php?id=<?= $pid ?>" class="btn btn--ghost">← Projekt</a><?php endif; ?>
  </div>
</div>
<?php flash(); ?>

<div class="detail-grid">
  <div>
    <div class="card card--pad">
      <h3 style="font-size:14.5px;margin-bottom:16px">Ladda upp dokument</h3>
      <?php if ($error): ?><div class="flash" style="margin-bottom:14px;border-color:#DC262633;background:#DC26260d;color:var(--red)"><?= e($error) ?></div><?php endif; ?>
      <?php if ($success): ?><div class="flash" style="margin-bottom:14px;border-color:#05966933;background:#0596690d;color:var(--green)"><?= e($success) ?></div><?php endif; ?>
      <form method="post" enctype="multipart/form-data">
        <?= csrf_field() ?>
        <?php if (!$project): ?>
        <div class="fg"><label>Projekt</label>
          <select class="fs" name="project_id" required>
            <option value="">— Välj projekt —</option>
            <?php foreach ($projects as $pr): ?>
            <option value="<?= $pr['id'] ?>"><?= e($pr['title']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <?php else: ?>
        <input type="hidden" name="project_id" value="<?= $pid ?>">
        <?php endif; ?>
        <div class="fg"><label>Fil (max 10 MB)</label>
          <input class="fi" type="file" name="file" accept=".pdf,.jpg,.jpeg,.png,.webp,.doc,.docx" required>
        </div>
        <div class="fg"><label>Beskrivning</label><input class="fi" name="description" placeholder="T.ex. Slutrapport, Ritning A3, Garanti..."></div>
        <button class="btn btn--primary">Ladda upp till portal</button>
      </form>
    </div>
  </div>

  <div>
    <?php if ($pid): ?>
    <div class="card card--pad">
      <h3 style="font-size:14.5px;margin-bottom:14px">Dokument i portalen</h3>
      <?php if ($docs): ?>
      <?php foreach ($docs as $d): ?>
      <div style="display:flex;align-items:center;gap:10px;padding:10px 0;border-bottom:1px solid #F3F4F6">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" style="width:18px;height:18px;color:var(--blue);flex-shrink:0"><path d="M22 19a2 2 0 01-2 2H4a2 2 0 01-2-2V5a2 2 0 012-2h5l2 3h9a2 2 0 012 2z"/></svg>
        <div style="flex:1;min-width:0">
          <div style="font-size:13px;font-weight:550;overflow:hidden;text-overflow:ellipsis;white-space:nowrap"><?= e($d['original_name']) ?></div>
          <div style="font-size:11px;color:var(--gray)"><?= e($d['description'] ?: '—') ?> · <?= e($d['uploader'] ?: 'Staff') ?> · <?= time_ago($d['created_at']) ?></div>
        </div>
        <span style="font-size:11px;color:var(--gray)"><?= number_format($d['filesize']/1024,0,'.',',') ?> KB</span>
      </div>
      <?php endforeach; ?>
      <?php else: ?>
      <p style="font-size:13px;color:var(--gray)">Inga dokument uppladdade till detta projekt ännu.</p>
      <?php endif; ?>
    </div>
    <?php endif; ?>
  </div>
</div>

<?php require_once __DIR__ . '/includes/crm-footer.php'; ?>
