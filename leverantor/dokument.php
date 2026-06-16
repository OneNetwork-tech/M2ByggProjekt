<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/layout.php';
$su  = supp_require();
$sid = (int)$su['supplier_id'];

$error = ''; $success = '';
$ALLOWED_MIME = ['application/pdf','image/jpeg','image/png','image/webp','application/msword','application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
$UPLOAD_DIR   = dirname(__DIR__) . '/data/portal-uploads/supplier/';
if (!is_dir($UPLOAD_DIR)) mkdir($UPLOAD_DIR, 0750, true);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    $f    = $_FILES['file'];
    $cat  = trim($_POST['category'] ?? 'Övrigt');
    $note = trim($_POST['note'] ?? '');

    if ($f['error'] !== UPLOAD_ERR_OK) {
        $error = 'Uppladdningsfel (' . $f['error'] . ').';
    } elseif ($f['size'] > 10_485_760) {
        $error = 'Filen är för stor (max 10 MB).';
    } elseif (!in_array(mime_content_type($f['tmp_name']), $ALLOWED_MIME)) {
        $error = 'Filtypen är inte tillåten. Tillåtna: PDF, JPG, PNG, WEBP, DOC, DOCX.';
    } else {
        $ext      = strtolower(pathinfo($f['name'], PATHINFO_EXTENSION));
        $safeName = preg_replace('/[^a-z0-9_\-]/i', '_', pathinfo($f['name'], PATHINFO_FILENAME));
        $stored   = 'supp_' . $sid . '_' . time() . '_' . $safeName . '.' . $ext;
        move_uploaded_file($f['tmp_name'], $UPLOAD_DIR . $stored);
        db()->prepare(
            "INSERT INTO supplier_documents (supplier_id, original_name, stored_name, mime_type, size_bytes, category, note) VALUES (?,?,?,?,?,?,?)"
        )->execute([$sid, $f['name'], $stored, mime_content_type($UPLOAD_DIR.$stored), $f['size'], $cat, $note]);
        $success = 'Dokumentet har laddats upp.';
    }
}

// Load docs
$s = db()->prepare("SELECT * FROM supplier_documents WHERE supplier_id=? ORDER BY created_at DESC");
$s->execute([$sid]);
$docs = $s->fetchAll();

supp_head('Dokument', $su);
supp_nav('/dokument.php');
?>
<main class="portal-main">
  <div class="portal-page-title"><h1>Dokument</h1><p>Ladda upp certifikat, försäkringar och andra dokument.</p></div>

  <div style="display:grid;grid-template-columns:1fr 1fr;gap:24px">
    <!-- Upload form -->
    <div class="card">
      <div class="card-header"><h3>Ladda upp dokument</h3></div>
      <div style="padding:20px">
        <?php if ($error): ?><div class="alert alert--error" style="margin-bottom:16px"><?= e($error) ?></div><?php endif; ?>
        <?php if ($success): ?><div class="alert alert--success" style="margin-bottom:16px"><?= e($success) ?></div><?php endif; ?>
        <form method="post" enctype="multipart/form-data">
          <div class="form-group">
            <label class="form-label">Kategori</label>
            <select class="form-control" name="category">
              <option>Försäkring</option>
              <option>Certifikat</option>
              <option>ID-handling</option>
              <option>Skattebevis</option>
              <option>Övrigt</option>
            </select>
          </div>
          <div class="form-group">
            <label class="form-label">Fil (max 10 MB)</label>
            <input class="form-control" type="file" name="file" accept=".pdf,.jpg,.jpeg,.png,.webp,.doc,.docx" required>
          </div>
          <div class="form-group">
            <label class="form-label">Anteckning (valfritt)</label>
            <input class="form-control" type="text" name="note" placeholder="T.ex. giltigt till 2026-12-31">
          </div>
          <button type="submit" class="btn btn--primary">Ladda upp</button>
        </form>
      </div>
    </div>

    <!-- Existing docs -->
    <div class="card">
      <div class="card-header"><h3>Uppladdade dokument</h3></div>
      <?php if ($docs): ?>
      <div style="display:flex;flex-direction:column">
        <?php foreach ($docs as $d): ?>
        <div style="display:flex;align-items:center;gap:12px;padding:14px 20px;border-bottom:1px solid var(--border)">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" style="width:20px;height:20px;flex-shrink:0;color:var(--brand)"><path d="M22 19a2 2 0 01-2 2H4a2 2 0 01-2-2V5a2 2 0 012-2h5l2 3h9a2 2 0 012 2z"/></svg>
          <div style="flex:1;min-width:0">
            <div style="font-weight:500;white-space:nowrap;overflow:hidden;text-overflow:ellipsis"><?= e($d['original_name']) ?></div>
            <div style="font-size:.75rem;color:var(--steel)"><?= e($d['category']) ?><?= $d['note'] ? ' · '.e($d['note']) : '' ?> · <?= number_format($d['size_bytes']/1024,0,',','.').' KB' ?></div>
          </div>
          <a href="/leverantor/download.php?id=<?= $d['id'] ?>" class="btn btn--outline btn--sm">↓</a>
        </div>
        <?php endforeach; ?>
      </div>
      <?php else: ?>
      <p style="padding:24px;color:var(--steel);font-size:.875rem">Inga dokument uppladdade ännu.</p>
      <?php endif; ?>
    </div>
  </div>
</main>
<?php supp_foot(); ?>
