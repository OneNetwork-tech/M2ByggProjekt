<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/helpers.php';
$me = require_login();
$pdo = db();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'create') {
    csrf_check();
    $stmt = $pdo->prepare("INSERT INTO customers (name,email,phone,address,city,postal_code,org_nr,type,notes) VALUES (?,?,?,?,?,?,?,?,?)");
    $stmt->execute([
        trim($_POST['name']), trim($_POST['email'] ?? ''), trim($_POST['phone'] ?? ''),
        trim($_POST['address'] ?? ''), trim($_POST['city'] ?? ''), trim($_POST['postal_code'] ?? ''),
        trim($_POST['org_nr'] ?? ''), $_POST['type'] ?? 'private', trim($_POST['notes'] ?? ''),
    ]);
    $cid = $pdo->lastInsertId();
    log_timeline('customer', $cid, 'system', 'Kund skapad', '', $me['id']);
    audit('customer_create', 'customer', $cid);
    flash('Kund skapad.');
    header("Location: kund.php?id=$cid"); exit;
}

$q = trim($_GET['q'] ?? '');
$where = '1=1'; $params = [];
if ($q) { $where = "(name LIKE ? OR phone LIKE ? OR email LIKE ? OR city LIKE ?)"; $params = array_fill(0, 4, "%$q%"); }

$stmt = $pdo->prepare("
  SELECT c.*,
    (SELECT COUNT(*) FROM projects p WHERE p.customer_id = c.id) AS project_count,
    (SELECT COALESCE(SUM(i.paid_amount),0) FROM invoices i WHERE i.customer_id = c.id) AS lifetime_value
  FROM customers c WHERE $where ORDER BY c.created_at DESC");
$stmt->execute($params);
$customers = $stmt->fetchAll();

$crm_title = 'Kunder';
$crm_page  = 'kunder';
require_once __DIR__ . '/includes/crm-header.php';
?>

<div class="topbar">
  <div>
    <h1>Kunder</h1>
    <div class="topbar__sub"><?= count($customers) ?> kunder registrerade</div>
  </div>
  <div class="topbar__actions">
    <form method="GET" class="search-box">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
      <input name="q" placeholder="Sök kund..." value="<?= e($q) ?>">
    </form>
    <button class="btn btn--primary" onclick="openModal('newCustModal')">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
      Ny kund
    </button>
  </div>
</div>

<?php flash(); ?>

<div class="card">
  <div class="table-wrap">
    <table class="data">
      <thead><tr><th>Kund</th><th>Kontakt</th><th>Stad</th><th>Typ</th><th>Projekt</th><th>Livstidsvärde</th><th>Skapad</th></tr></thead>
      <tbody>
        <?php if (!$customers): ?>
        <tr><td colspan="7" style="text-align:center;color:var(--gray);padding:36px">Inga kunder ännu. Konvertera en lead eller skapa manuellt.</td></tr>
        <?php endif; ?>
        <?php foreach ($customers as $c): ?>
        <tr data-href="kund.php?id=<?= $c['id'] ?>">
          <td>
            <div style="display:flex;align-items:center;gap:10px">
              <div class="avatar" style="width:30px;height:30px;font-size:11px"><?= e(initials($c['name'])) ?></div>
              <span style="font-weight:550"><?= e($c['name']) ?></span>
            </div>
          </td>
          <td style="font-size:12.5px;color:var(--gray)"><?= e($c['phone'] ?: $c['email'] ?: '–') ?></td>
          <td><?= e($c['city'] ?: '–') ?></td>
          <td><span class="badge" style="background:#F3F4F6;color:var(--gray)"><?= $c['type'] === 'company' ? 'Företag' : 'Privat' ?></span></td>
          <td><?= $c['project_count'] ?></td>
          <td style="font-weight:550"><?= money($c['lifetime_value']) ?></td>
          <td style="font-size:12.5px;color:var(--gray)"><?= dt($c['created_at']) ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<div class="modal-bg" id="newCustModal">
  <div class="modal">
    <h3>Ny kund</h3>
    <form method="POST">
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="create">
      <div class="fg"><label>Namn / Företag *</label><input class="fi" name="name" required></div>
      <div class="frow">
        <div class="fg"><label>Telefon</label><input class="fi" name="phone"></div>
        <div class="fg"><label>E-post</label><input class="fi" type="email" name="email"></div>
      </div>
      <div class="fg"><label>Adress</label><input class="fi" name="address"></div>
      <div class="frow">
        <div class="fg"><label>Postnr</label><input class="fi" name="postal_code"></div>
        <div class="fg"><label>Stad</label><input class="fi" name="city"></div>
      </div>
      <div class="frow">
        <div class="fg"><label>Typ</label>
          <select class="fs" name="type"><option value="private">Privatperson</option><option value="company">Företag</option></select>
        </div>
        <div class="fg"><label>Org.nr / Personnr</label><input class="fi" name="org_nr"></div>
      </div>
      <div class="fg"><label>Anteckningar</label><textarea class="fta" name="notes"></textarea></div>
      <div style="display:flex;gap:10px;justify-content:flex-end">
        <button type="button" class="btn btn--ghost" onclick="closeModal('newCustModal')">Avbryt</button>
        <button class="btn btn--primary">Skapa kund</button>
      </div>
    </form>
  </div>
</div>

<?php require_once __DIR__ . '/includes/crm-footer.php'; ?>
