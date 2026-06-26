<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/helpers.php';
$me = require_role(['project']);
$pdo = db();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    if (($_POST['action'] ?? '') === 'create') {
        $pdo->prepare("INSERT INTO suppliers (company, contact, email, phone, specialty, org_nr, status, notes) VALUES (?,?,?,?,?,?,?,?)")
            ->execute([trim($_POST['company']), trim($_POST['contact'] ?? ''), trim($_POST['email'] ?? ''), trim($_POST['phone'] ?? ''),
                       $_POST['specialty'] ?? '', trim($_POST['org_nr'] ?? ''), $_POST['status'] ?? 'pending', trim($_POST['notes'] ?? '')]);
        audit('supplier_create', 'supplier', $pdo->lastInsertId());
        flash('Leverantör tillagd.');
        header('Location: leverantorer.php'); exit;
    }
    if (($_POST['action'] ?? '') === 'status') {
        $pdo->prepare("UPDATE suppliers SET status=? WHERE id=?")->execute([$_POST['status'], (int)$_POST['id']]);
        flash('Status uppdaterad.');
        header('Location: leverantorer.php'); exit;
    }
}

$suppliers = $pdo->query("
  SELECT s.*, (SELECT COUNT(*) FROM projects p WHERE p.supplier_id = s.id) AS project_count
  FROM suppliers s ORDER BY s.created_at DESC")->fetchAll();

$crm_title = 'Leverantörer';
$crm_page  = 'leverantorer';
require_once __DIR__ . '/includes/crm-header.php';
?>

<div class="topbar">
  <div>
    <h1>Leverantörer</h1>
    <div class="topbar__sub"><?= count($suppliers) ?> registrerade underleverantörer</div>
  </div>
  <div class="topbar__actions">
    <button class="btn btn--primary" onclick="openModal('newSupModal')">+ Ny leverantör</button>
  </div>
</div>

<?php flash(); ?>

<div class="card">
  <div class="table-wrap">
    <table class="data">
      <thead><tr><th>Företag</th><th>Kontakt</th><th>Specialitet</th><th>Status</th><th>Projekt</th><th>Betyg</th><th>Ändra status</th></tr></thead>
      <tbody>
        <?php if (!$suppliers): ?>
        <tr><td colspan="7" style="text-align:center;color:var(--gray);padding:36px">Inga leverantörer ännu. Ansökningar via webben hamnar här.</td></tr>
        <?php endif; ?>
        <?php foreach ($suppliers as $s): ?>
        <tr>
          <td><div style="font-weight:550"><?= e($s['company']) ?></div><div style="font-size:11.5px;color:var(--gray-lt)"><?= e($s['org_nr'] ?: '–') ?></div></td>
          <td style="font-size:12.5px"><?= e($s['contact'] ?: '–') ?><br><span style="color:var(--gray-lt)"><?= e($s['phone'] ?: $s['email'] ?: '') ?></span></td>
          <td><?= e($s['specialty'] ?: '–') ?></td>
          <td><?= badge($s['status'], SUPPLIER_STATUSES) ?></td>
          <td><?= $s['project_count'] ?></td>
          <td style="color:#D97706;font-size:12px;white-space:nowrap"><?= $s['rating'] > 0 ? number_format($s['rating'], 1, ',', '') . ' ★' : '<span style="color:var(--gray)">—</span>' ?></td>
          <td>
            <form method="POST" style="display:flex;gap:6px">
              <?= csrf_field() ?><input type="hidden" name="action" value="status"><input type="hidden" name="id" value="<?= $s['id'] ?>">
              <select class="fs" name="status" style="padding:5px 9px;font-size:12px;width:auto">
                <?php foreach (SUPPLIER_STATUSES as $k => $cfg): ?>
                <option value="<?= $k ?>" <?= $s['status']===$k?'selected':'' ?>><?= e($cfg['label']) ?></option>
                <?php endforeach; ?>
              </select>
              <button class="btn btn--ghost btn--sm">OK</button>
            </form>
            <a href="leverantor-inbjudan.php?supplier=<?= $s['id'] ?>" class="btn btn--ghost btn--sm" title="Bjud in till portal">🔗</a>
            <?php if ($me['role'] === 'super_admin'): ?>
            <a href="gdpr.php?export=supplier&id=<?= $s['id'] ?>" class="btn btn--ghost btn--sm" title="GDPR-export">📄</a>
            <?php endif; ?>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<div class="modal-bg" id="newSupModal">
  <div class="modal">
    <h3>Ny leverantör</h3>
    <form method="POST">
      <?= csrf_field() ?><input type="hidden" name="action" value="create">
      <div class="fg"><label>Företag *</label><input class="fi" name="company" required></div>
      <div class="frow">
        <div class="fg"><label>Kontaktperson</label><input class="fi" name="contact"></div>
        <div class="fg"><label>Org.nr</label><input class="fi" name="org_nr"></div>
      </div>
      <div class="frow">
        <div class="fg"><label>Telefon</label><input class="fi" name="phone"></div>
        <div class="fg"><label>E-post</label><input class="fi" type="email" name="email"></div>
      </div>
      <div class="frow">
        <div class="fg"><label>Specialitet</label>
          <select class="fs" name="specialty"><option value="">Välj...</option>
            <?php foreach (SERVICES as $s): ?><option><?= e($s) ?></option><?php endforeach; ?>
          </select>
        </div>
        <div class="fg"><label>Status</label>
          <select class="fs" name="status">
            <?php foreach (SUPPLIER_STATUSES as $k => $cfg): ?><option value="<?= $k ?>"><?= e($cfg['label']) ?></option><?php endforeach; ?>
          </select>
        </div>
      </div>
      <div class="fg"><label>Anteckningar</label><textarea class="fta" name="notes"></textarea></div>
      <div style="display:flex;gap:10px;justify-content:flex-end">
        <button type="button" class="btn btn--ghost" onclick="closeModal('newSupModal')">Avbryt</button>
        <button class="btn btn--primary">Spara</button>
      </div>
    </form>
  </div>
</div>

<?php require_once __DIR__ . '/includes/crm-footer.php'; ?>
