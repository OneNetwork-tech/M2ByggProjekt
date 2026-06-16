<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/layout.php';
$pu  = portal_require();
$cid = (int)$pu['customer_id'];

$error = ''; $success = '';
$UPLOAD_DIR = __DIR__ . '/../data/portal-uploads/';
$MAX_SIZE   = 10 * 1024 * 1024; // 10MB
$ALLOWED    = ['application/pdf','image/jpeg','image/png','image/webp','application/msword',
               'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];

// Get customer's project IDs
$pidStmt = db()->prepare("SELECT id FROM projects WHERE customer_id = ?");
$pidStmt->execute([$cid]);
$pids = array_column($pidStmt->fetchAll(), 'id');

// Upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_FILES['file']['tmp_name'])) {
    $pid = (int)($_POST['project_id'] ?? 0);
    if (!in_array($pid, $pids)) { $error = 'Ogiltigt projekt.'; }
    elseif ($_FILES['file']['size'] > $MAX_SIZE) { $error = 'Filen är för stor (max 10 MB).'; }
    elseif (!in_array(mime_content_type($_FILES['file']['tmp_name']), $ALLOWED)) { $error = 'Otillåten filtyp.'; }
    else {
        $ext      = strtolower(pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION));
        $filename = uniqid('doc_') . '.' . $ext;
        if (!is_dir($UPLOAD_DIR)) mkdir($UPLOAD_DIR, 0755, true);
        if (move_uploaded_file($_FILES['file']['tmp_name'], $UPLOAD_DIR . $filename)) {
            db()->prepare(
                "INSERT INTO portal_documents (project_id,uploaded_by_type,uploaded_by_id,filename,original_name,mime_type,filesize,description)
                 VALUES (?,?,?,?,?,?,?,?)"
            )->execute([$pid,'customer',$cid,$filename,$_FILES['file']['name'],
                mime_content_type($UPLOAD_DIR . $filename),$_FILES['file']['size'],
                trim($_POST['description'] ?? '')]);
            $success = 'Filen laddades upp!';
        } else $error = 'Uppladdning misslyckades.';
    }
}

// Load documents
$docs = [];
if ($pids) {
    $in = implode(',', array_fill(0, count($pids), '?'));
    $s  = db()->prepare(
        "SELECT d.*, p.title AS project_title,
                CASE d.uploaded_by_type WHEN 'customer' THEN 'Du' ELSE 'M2 Bygg Team' END AS uploader
         FROM portal_documents d JOIN projects p ON p.id = d.project_id
         WHERE d.project_id IN ($in) ORDER BY d.created_at DESC"
    );
    $s->execute($pids);
    $docs = $s->fetchAll();
}

// Load projects for upload form
$projects = portal_projects($cid);

portal_head('Dokument', $pu);
portal_nav('/dokument.php');
?>
<main class="portal-main">
  <div class="portal-page-title"><h1>Dokument</h1><p>Ladda upp och ladda ned projektfiler.</p></div>

  <?php if ($error): ?><div class="alert alert--error"><?= e($error) ?></div><?php endif; ?>
  <?php if ($success): ?><div class="alert alert--success"><?= e($success) ?></div><?php endif; ?>

  <div class="grid-2" style="align-items:start">
    <!-- Upload form -->
    <div class="card">
      <h3 style="margin-bottom:16px">Ladda upp fil</h3>
      <form method="post" enctype="multipart/form-data">
        <div class="form-group">
          <label class="form-label">Projekt</label>
          <select class="form-control" name="project_id" required>
            <option value="">— Välj projekt —</option>
            <?php foreach ($projects as $p): ?>
            <option value="<?= $p['id'] ?>"><?= e($p['title']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group">
          <label class="form-label">Fil</label>
          <input class="form-control" type="file" name="file" required accept=".pdf,.jpg,.jpeg,.png,.webp,.doc,.docx">
          <p class="form-hint">PDF, JPG, PNG, WEBP, DOC/DOCX · max 10 MB</p>
        </div>
        <div class="form-group">
          <label class="form-label">Beskrivning (valfritt)</label>
          <input class="form-control" type="text" name="description" placeholder="T.ex. Besiktningsprotokoll">
        </div>
        <button type="submit" class="btn btn--primary">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:15px;height:15px"><polyline points="16,16 12,12 8,16"/><line x1="12" y1="12" x2="12" y2="21"/><path d="M20.39 18.39A5 5 0 0018 9h-1.26A8 8 0 103 16.3"/></svg>
          Ladda upp
        </button>
      </form>
    </div>

    <!-- Document list -->
    <div class="card">
      <h3 style="margin-bottom:16px">Filer (<?= count($docs) ?>)</h3>
      <?php if ($docs): ?>
      <div style="display:flex;flex-direction:column;gap:10px">
        <?php foreach ($docs as $doc):
          $icon = str_contains($doc['mime_type'] ?? '', 'pdf') ? '#dc2626' : (str_contains($doc['mime_type'] ?? '', 'image') ? '#0891b2' : '#374151');
        ?>
        <div class="doc-item">
          <div class="doc-item__icon" style="color:<?= $icon ?>">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14,2 14,8 20,8"/></svg>
          </div>
          <div style="flex:1;min-width:0">
            <div class="doc-item__name" style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap"><?= e($doc['original_name']) ?></div>
            <div class="doc-item__meta"><?= e($doc['project_title']) ?> · <?= e($doc['uploader']) ?> · <?= round(($doc['filesize'] ?? 0) / 1024) ?> KB</div>
            <?php if ($doc['description']): ?><div class="doc-item__meta" style="margin-top:2px"><?= e($doc['description']) ?></div><?php endif; ?>
          </div>
          <a href="/portal/download.php?id=<?= $doc['id'] ?>" class="btn btn--outline btn--sm">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:13px;height:13px"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="7,10 12,15 17,10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
          </a>
        </div>
        <?php endforeach; ?>
      </div>
      <?php else: ?>
        <p style="color:var(--steel);font-size:.875rem">Inga filer uppladdade än.</p>
      <?php endif; ?>
    </div>
  </div>
</main>
<?php portal_foot(); ?>
