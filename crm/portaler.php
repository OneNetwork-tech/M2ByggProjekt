<?php
/**
 * CRM — Portal access management: see who has customer/supplier portal access
 */
require_once __DIR__ . '/includes/auth.php';
require_role(['super_admin','sales','support']);
$pdo = db();

// Revoke access
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $action = $_POST['action'] ?? '';
    if ($action === 'revoke_customer') {
        $pdo->prepare("UPDATE portal_users SET active=0 WHERE id=?")->execute([(int)$_POST['id']]);
        flash('Kundportal-åtkomst återkallad.');
    } elseif ($action === 'restore_customer') {
        $pdo->prepare("UPDATE portal_users SET active=1 WHERE id=?")->execute([(int)$_POST['id']]);
        flash('Kundportal-åtkomst återställd.');
    } elseif ($action === 'revoke_supplier') {
        $pdo->prepare("UPDATE supplier_users SET active=0 WHERE id=?")->execute([(int)$_POST['id']]);
        flash('Leverantörsportal-åtkomst återkallad.');
    } elseif ($action === 'restore_supplier') {
        $pdo->prepare("UPDATE supplier_users SET active=1 WHERE id=?")->execute([(int)$_POST['id']]);
        flash('Leverantörsportal-åtkomst återställd.');
    }
    header('Location: portaler.php'); exit;
}

// Customer portal users
$customerUsers = $pdo->query("
    SELECT pu.*, c.name AS customer_name, c.email AS customer_email, c.id AS cid,
           (SELECT COUNT(*) FROM portal_messages WHERE project_id IN (SELECT id FROM projects WHERE customer_id=pu.customer_id) AND sender_type='customer') AS msg_count
    FROM portal_users pu JOIN customers c ON c.id=pu.customer_id
    ORDER BY pu.created_at DESC
")->fetchAll();

// Customers without portal access
$noPortal = $pdo->query("
    SELECT c.id, c.name, c.email FROM customers c
    WHERE c.id NOT IN (SELECT customer_id FROM portal_users)
    ORDER BY c.name
")->fetchAll();

// Supplier portal users
$supplierUsers = $pdo->query("
    SELECT su.*, s.company, s.email AS supplier_email, s.id AS sid
    FROM supplier_users su JOIN suppliers s ON s.id=su.supplier_id
    ORDER BY su.created_at DESC
")->fetchAll();

// Suppliers without portal access
$noSupplierPortal = $pdo->query("
    SELECT s.id, s.company, s.email FROM suppliers s
    WHERE s.id NOT IN (SELECT supplier_id FROM supplier_users)
    ORDER BY s.company
")->fetchAll();

$crm_title = 'Portalåtkomst';
$crm_page  = 'portaler';
require_once __DIR__ . '/includes/crm-header.php';
?>
<div class="topbar">
  <div>
    <h1>Portalåtkomst</h1>
    <div class="topbar__sub">Hantera åtkomst till kund- och leverantörsportalen</div>
  </div>
</div>
<?php flash(); ?>

<div class="detail-grid">

  <!-- Customer portal -->
  <div style="display:flex;flex-direction:column;gap:16px">
    <div class="card card--pad">
      <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px">
        <h3 style="font-size:15px;margin:0">Kundportal <span style="color:var(--gray);font-weight:400">(<?= count($customerUsers) ?> aktiva)</span></h3>
        <a href="portal-inbjudan.php" class="btn btn--primary btn--sm" style="font-size:11.5px">+ Bjud in kund</a>
      </div>
      <?php if ($customerUsers): ?>
      <table class="table" style="font-size:12.5px">
        <thead><tr><th>Kund</th><th>E-post</th><th>Inloggad</th><th>Meddelanden</th><th>Status</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($customerUsers as $u): ?>
        <tr style="<?= $u['active'] ? '' : 'opacity:.55' ?>">
          <td><a href="kund.php?id=<?= $u['cid'] ?>" style="font-weight:550"><?= e($u['customer_name']) ?></a></td>
          <td><?= e($u['email']) ?></td>
          <td style="color:var(--gray)"><?= $u['last_login'] ? substr($u['last_login'],0,10) : '—' ?></td>
          <td><?= $u['msg_count'] ? '<a href="meddelanden.php?view=portal">'.e($u['msg_count']).'</a>' : '—' ?></td>
          <td><span class="badge <?= $u['active'] ? 'badge-success' : 'badge-warning' ?>"><?= $u['active'] ? 'Aktiv' : 'Inaktiv' ?></span></td>
          <td>
            <form method="post" style="display:inline">
              <?= csrf_field() ?>
              <input type="hidden" name="id" value="<?= $u['id'] ?>">
              <?php if ($u['active']): ?>
              <button name="action" value="revoke_customer" class="btn btn--ghost btn--sm" onclick="return confirm('Återkalla portalåtkomst?')" style="color:var(--red)">Återkalla</button>
              <?php else: ?>
              <button name="action" value="restore_customer" class="btn btn--ghost btn--sm">Återställ</button>
              <?php endif; ?>
            </form>
            <a href="portal-inbjudan.php?customer=<?= $u['cid'] ?>" class="btn btn--ghost btn--sm">Ny länk</a>
          </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
      <?php else: ?>
      <p style="font-size:13px;color:var(--gray)">Inga kunder har portalåtkomst ännu.</p>
      <?php endif; ?>
    </div>

    <?php if ($noPortal): ?>
    <div class="card card--pad">
      <h3 style="font-size:14px;margin-bottom:12px">Kunder utan portalåtkomst</h3>
      <div style="display:flex;flex-direction:column;gap:6px">
      <?php foreach ($noPortal as $c): ?>
      <div style="display:flex;justify-content:space-between;align-items:center;padding:8px 0;border-bottom:1px solid #F3F4F6">
        <div>
          <div style="font-size:13px;font-weight:550"><?= e($c['name']) ?></div>
          <div style="font-size:11.5px;color:var(--gray)"><?= e($c['email'] ?: '—') ?></div>
        </div>
        <a href="portal-inbjudan.php?customer=<?= $c['id'] ?>" class="btn btn--primary btn--sm" style="font-size:11px">Bjud in</a>
      </div>
      <?php endforeach; ?>
      </div>
    </div>
    <?php endif; ?>
  </div>

  <!-- Supplier portal -->
  <div style="display:flex;flex-direction:column;gap:16px">
    <div class="card card--pad">
      <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px">
        <h3 style="font-size:15px;margin:0">Leverantörsportal <span style="color:var(--gray);font-weight:400">(<?= count($supplierUsers) ?> aktiva)</span></h3>
        <a href="leverantorer.php" class="btn btn--primary btn--sm" style="font-size:11.5px">Hantera leverantörer</a>
      </div>
      <?php if ($supplierUsers): ?>
      <table class="table" style="font-size:12.5px">
        <thead><tr><th>Leverantör</th><th>E-post</th><th>Inloggad</th><th>Status</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($supplierUsers as $u): ?>
        <tr style="<?= $u['active'] ? '' : 'opacity:.55' ?>">
          <td style="font-weight:550"><?= e($u['company']) ?></td>
          <td><?= e($u['email']) ?></td>
          <td style="color:var(--gray)"><?= $u['last_login'] ? substr($u['last_login'],0,10) : '—' ?></td>
          <td><span class="badge <?= $u['active'] ? 'badge-success' : 'badge-warning' ?>"><?= $u['active'] ? 'Aktiv' : 'Inaktiv' ?></span></td>
          <td>
            <form method="post" style="display:inline">
              <?= csrf_field() ?>
              <input type="hidden" name="id" value="<?= $u['id'] ?>">
              <?php if ($u['active']): ?>
              <button name="action" value="revoke_supplier" class="btn btn--ghost btn--sm" onclick="return confirm('Återkalla åtkomst?')" style="color:var(--red)">Återkalla</button>
              <?php else: ?>
              <button name="action" value="restore_supplier" class="btn btn--ghost btn--sm">Återställ</button>
              <?php endif; ?>
            </form>
            <a href="leverantor-inbjudan.php?supplier=<?= $u['sid'] ?>" class="btn btn--ghost btn--sm">Ny länk</a>
          </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
      <?php else: ?>
      <p style="font-size:13px;color:var(--gray)">Inga leverantörer har portalåtkomst ännu.</p>
      <?php endif; ?>
    </div>

    <?php if ($noSupplierPortal): ?>
    <div class="card card--pad">
      <h3 style="font-size:14px;margin-bottom:12px">Leverantörer utan portalåtkomst</h3>
      <div style="display:flex;flex-direction:column;gap:6px">
      <?php foreach ($noSupplierPortal as $s): ?>
      <div style="display:flex;justify-content:space-between;align-items:center;padding:8px 0;border-bottom:1px solid #F3F4F6">
        <div>
          <div style="font-size:13px;font-weight:550"><?= e($s['company']) ?></div>
          <div style="font-size:11.5px;color:var(--gray)"><?= e($s['email'] ?: '—') ?></div>
        </div>
        <a href="leverantor-inbjudan.php?supplier=<?= $s['id'] ?>" class="btn btn--primary btn--sm" style="font-size:11px">Bjud in</a>
      </div>
      <?php endforeach; ?>
      </div>
    </div>
    <?php endif; ?>
  </div>
</div>

<?php require_once __DIR__ . '/includes/crm-footer.php'; ?>
