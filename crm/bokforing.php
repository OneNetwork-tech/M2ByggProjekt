<?php
/**
 * CRM — Accounting integration (Fortnox / Visma eEkonomi)
 */
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/fortnox.php';
require_once __DIR__ . '/includes/visma.php';
$me = require_role(['super_admin']);
$pdo = db();

$redirectBase = (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $action = $_POST['action'] ?? '';

    if ($action === 'disconnect') {
        $provider = $_POST['provider'] ?? '';
        foreach (['access_token', 'refresh_token', 'token_expires'] as $k) {
            db()->prepare("DELETE FROM settings WHERE skey = ?")->execute(["{$provider}_{$k}"]);
        }
        flash('Frånkopplad.');
        header('Location: bokforing.php'); exit;
    }

    if ($action === 'sync_now') {
        $provider = $_POST['provider'] ?? '';
        $syncFn = $provider === 'fortnox' ? 'fortnox_sync_invoice' : 'visma_sync_invoice';
        $connected = $provider === 'fortnox' ? fortnox_connected() : visma_connected();

        if (!$connected) {
            flash('Inte ansluten till ' . ucfirst($provider) . '.', 'error');
        } else {
            $invoices = $pdo->query("
                SELECT i.*, c.name, c.email, c.phone, c.address, c.city, c.postal_code, c.org_nr, c.id AS cid
                FROM invoices i JOIN customers c ON c.id = i.customer_id
                WHERE i.status NOT IN ('draft','cancelled')
                ORDER BY i.created_at DESC LIMIT 25
            ")->fetchAll();

            $synced = 0; $failed = 0;
            foreach ($invoices as $inv) {
                $itemsStmt = $pdo->prepare("SELECT * FROM invoice_items WHERE invoice_id = ?");
                $itemsStmt->execute([$inv['id']]);
                $items = $itemsStmt->fetchAll();
                $customer = ['id' => $inv['cid'], 'name' => $inv['name'], 'email' => $inv['email'], 'phone' => $inv['phone'], 'address' => $inv['address'], 'city' => $inv['city'], 'postal_code' => $inv['postal_code'], 'org_nr' => $inv['org_nr']];

                $result = $syncFn($inv, $items, $customer);
                $result ? $synced++ : $failed++;
            }
            flash("Synkronisering klar: $synced lyckade, $failed misslyckade.", $failed ? 'error' : 'success');
        }
        header('Location: bokforing.php'); exit;
    }
}

$syncLog = $pdo->query("SELECT * FROM accounting_sync_log ORDER BY created_at DESC LIMIT 30")->fetchAll();

$crm_title = 'Bokföring';
$crm_page  = 'bokforing';
require_once __DIR__ . '/includes/crm-header.php';
?>
<div class="topbar">
  <div>
    <h1>Bokföringsintegration</h1>
    <div class="topbar__sub">Synka kunder och fakturor med Fortnox eller Visma eEkonomi</div>
  </div>
</div>

<?php flash(); ?>

<div class="detail-grid">
  <!-- FORTNOX -->
  <div class="card card--pad">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:14px">
      <h3 style="font-size:15px;margin:0">Fortnox</h3>
      <?php if (!fortnox_enabled()): ?>
      <span class="badge" style="background:#F3F4F6;color:var(--gray)">Ej konfigurerad</span>
      <?php elseif (fortnox_connected()): ?>
      <span class="badge badge-success">Ansluten</span>
      <?php else: ?>
      <span class="badge badge-warning">Inte ansluten</span>
      <?php endif; ?>
    </div>

    <?php if (!fortnox_enabled()): ?>
    <p style="font-size:13px;color:var(--gray);margin-bottom:12px">Lägg till <code>FORTNOX_CLIENT_ID</code> och <code>FORTNOX_CLIENT_SECRET</code> i <code>crm/config.php</code> (skaffas via Fortnox Developer Portal) för att aktivera.</p>
    <?php elseif (!fortnox_connected()): ?>
    <p style="font-size:13px;color:var(--gray);margin-bottom:14px">Klicka för att ansluta ditt Fortnox-konto via OAuth.</p>
    <a href="<?= e(fortnox_authorize_url($redirectBase . '/crm/bokforing-callback.php?provider=fortnox')) ?>" class="btn btn--primary btn--sm">Anslut till Fortnox</a>
    <?php else: ?>
    <p style="font-size:13px;color:var(--gray);margin-bottom:14px">Ansluten. Fakturor som skickas eller markeras betalda kan synkas till Fortnox.</p>
    <div style="display:flex;gap:8px">
      <form method="post"><?= csrf_field() ?><input type="hidden" name="action" value="sync_now"><input type="hidden" name="provider" value="fortnox">
        <button class="btn btn--primary btn--sm">Synka senaste 25 fakturor</button>
      </form>
      <form method="post" onsubmit="return confirm('Koppla bort Fortnox?')"><?= csrf_field() ?><input type="hidden" name="action" value="disconnect"><input type="hidden" name="provider" value="fortnox">
        <button class="btn btn--ghost btn--sm">Koppla bort</button>
      </form>
    </div>
    <?php endif; ?>
  </div>

  <!-- VISMA -->
  <div class="card card--pad">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:14px">
      <h3 style="font-size:15px;margin:0">Visma eEkonomi</h3>
      <?php if (!visma_enabled()): ?>
      <span class="badge" style="background:#F3F4F6;color:var(--gray)">Ej konfigurerad</span>
      <?php elseif (visma_connected()): ?>
      <span class="badge badge-success">Ansluten</span>
      <?php else: ?>
      <span class="badge badge-warning">Inte ansluten</span>
      <?php endif; ?>
    </div>

    <?php if (!visma_enabled()): ?>
    <p style="font-size:13px;color:var(--gray);margin-bottom:12px">Lägg till <code>VISMA_CLIENT_ID</code> och <code>VISMA_CLIENT_SECRET</code> i <code>crm/config.php</code> (skaffas via Visma Developer Portal) för att aktivera.</p>
    <?php elseif (!visma_connected()): ?>
    <p style="font-size:13px;color:var(--gray);margin-bottom:14px">Klicka för att ansluta ditt Visma-konto via OAuth.</p>
    <a href="<?= e(visma_authorize_url($redirectBase . '/crm/bokforing-callback.php?provider=visma')) ?>" class="btn btn--primary btn--sm">Anslut till Visma</a>
    <?php else: ?>
    <p style="font-size:13px;color:var(--gray);margin-bottom:14px">Ansluten. Fakturor som skickas eller markeras betalda kan synkas till Visma.</p>
    <div style="display:flex;gap:8px">
      <form method="post"><?= csrf_field() ?><input type="hidden" name="action" value="sync_now"><input type="hidden" name="provider" value="visma">
        <button class="btn btn--primary btn--sm">Synka senaste 25 fakturor</button>
      </form>
      <form method="post" onsubmit="return confirm('Koppla bort Visma?')"><?= csrf_field() ?><input type="hidden" name="action" value="disconnect"><input type="hidden" name="provider" value="visma">
        <button class="btn btn--ghost btn--sm">Koppla bort</button>
      </form>
    </div>
    <?php endif; ?>
  </div>
</div>

<!-- SYNC LOG -->
<div class="card card--pad" style="margin-top:20px">
  <h3 style="font-size:14.5px;margin-bottom:14px">Synkroniseringslogg</h3>
  <?php if ($syncLog): ?>
  <table class="data">
    <thead><tr><th>Tid</th><th>Leverantör</th><th>Typ</th><th>Objekt-ID</th><th>Externt ID</th><th>Status</th></tr></thead>
    <tbody>
    <?php foreach ($syncLog as $l): ?>
    <tr style="cursor:default">
      <td style="font-size:12px;color:var(--gray);white-space:nowrap"><?= dt($l['created_at'], 'j M H:i') ?></td>
      <td><span class="badge" style="background:#F3F4F6;color:var(--gray)"><?= e($l['provider']) ?></span></td>
      <td style="font-size:12.5px"><?= e($l['entity_type']) ?> · <?= e($l['action']) ?></td>
      <td style="font-size:12.5px">#<?= $l['entity_id'] ?></td>
      <td style="font-size:12.5px"><?= e($l['external_id'] ?: '—') ?></td>
      <td><?php if ($l['status'] === 'success'): ?><span class="badge badge-success">Lyckad</span><?php else: ?><span class="badge badge-danger" title="<?= e($l['response'] ?? '') ?>">Misslyckad</span><?php endif; ?></td>
    </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
  <?php else: ?>
  <p style="font-size:13px;color:var(--gray)">Ingen synkronisering ännu.</p>
  <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/crm-footer.php'; ?>
