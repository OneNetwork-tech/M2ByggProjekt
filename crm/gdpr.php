<?php
/**
 * CRM — GDPR requests: export & erasure (anonymization)
 */
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/gdpr.php';
$me = require_role(['super_admin']);
$pdo = db();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $action = $_POST['action'] ?? '';
    $reqId  = (int)($_POST['request_id'] ?? 0);

    if ($action === 'resolve_anonymize' || $action === 'resolve_reject') {
        $s = $pdo->prepare("SELECT * FROM gdpr_requests WHERE id=?"); $s->execute([$reqId]);
        $req = $s->fetch();
        if ($req) {
            if ($action === 'resolve_anonymize') {
                if ($req['entity_type'] === 'customer') gdpr_anonymize_customer($req['entity_id']);
                else gdpr_anonymize_supplier($req['entity_id']);
                $pdo->prepare("UPDATE gdpr_requests SET status='completed', resolved_at=" . now_expr() . ", resolved_by=? WHERE id=?")
                    ->execute([$me['id'], $reqId]);
                flash('Begäran genomförd — personuppgifter anonymiserade.');
            } else {
                $pdo->prepare("UPDATE gdpr_requests SET status='rejected', notes=?, resolved_at=" . now_expr() . ", resolved_by=? WHERE id=?")
                    ->execute([trim($_POST['reject_reason'] ?? ''), $me['id'], $reqId]);
                flash('Begäran avvisad.');
            }
        }
    }

    header('Location: gdpr.php'); exit;
}

// Direct export download (staff-triggered, no request record needed)
if (isset($_GET['export'])) {
    $type = $_GET['export'];
    $eid  = (int)($_GET['id'] ?? 0);
    $data = $type === 'customer' ? gdpr_export_customer($eid) : gdpr_export_supplier($eid);
    audit('gdpr_export', $type, $eid, 'Staff-triggered export');
    header('Content-Type: application/json');
    header('Content-Disposition: attachment; filename="' . $type . '-' . $eid . '-gdpr-export.json"');
    echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;
}

$requests = $pdo->query("
    SELECT g.*,
           CASE g.entity_type WHEN 'customer' THEN (SELECT name FROM customers WHERE id=g.entity_id) ELSE (SELECT company FROM suppliers WHERE id=g.entity_id) END AS entity_name,
           u.name AS resolved_by_name
    FROM gdpr_requests g LEFT JOIN users u ON u.id = g.resolved_by
    ORDER BY g.status = 'pending' DESC, g.requested_at DESC
")->fetchAll();

$customers = $pdo->query("SELECT id, name FROM customers ORDER BY name")->fetchAll();
$suppliers = $pdo->query("SELECT id, company FROM suppliers ORDER BY company")->fetchAll();

$crm_title = 'GDPR';
$crm_page  = 'gdpr';
require_once __DIR__ . '/includes/crm-header.php';
?>
<div class="topbar">
  <div>
    <h1>GDPR — dataexport & radering</h1>
    <div class="topbar__sub">Hantera begäranden om dataexport och radering av personuppgifter</div>
  </div>
</div>

<?php flash(); ?>

<div class="card card--pad" style="margin-bottom:20px">
  <h3 style="font-size:14.5px;margin-bottom:14px">Begäranden</h3>
  <?php if ($requests): ?>
  <table class="data">
    <thead><tr><th>Datum</th><th>Typ</th><th>Objekt</th><th>Begärd av</th><th>Status</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($requests as $r): ?>
    <tr style="cursor:default">
      <td style="font-size:12px;color:var(--gray);white-space:nowrap"><?= dt($r['requested_at'], 'j M H:i') ?></td>
      <td><span class="badge" style="background:#F3F4F6;color:var(--gray)"><?= $r['type'] === 'export' ? 'Export' : 'Radering' ?></span></td>
      <td style="font-size:12.5px;font-weight:550"><?= e($r['entity_name'] ?: '—') ?> <span style="color:var(--gray);font-weight:400">(<?= e($r['entity_type']) ?>)</span></td>
      <td style="font-size:12px;color:var(--gray)"><?= $r['requested_by'] === 'self' ? 'Kund/leverantör själv' : 'Personal' ?></td>
      <td>
        <?php if ($r['status'] === 'pending'): ?><span class="badge badge-warning">Väntar</span>
        <?php elseif ($r['status'] === 'completed'): ?><span class="badge badge-success">Genomförd</span>
        <?php else: ?><span class="badge badge-danger" title="<?= e($r['notes'] ?? '') ?>">Avvisad</span><?php endif; ?>
      </td>
      <td>
        <?php if ($r['status'] === 'pending'): ?>
        <div style="display:flex;gap:6px">
          <?php if ($r['type'] === 'export'): ?>
          <a href="gdpr.php?export=<?= $r['entity_type'] ?>&id=<?= $r['entity_id'] ?>" class="btn btn--primary btn--sm">Exportera</a>
          <form method="post"><?= csrf_field() ?><input type="hidden" name="action" value="resolve_reject"><input type="hidden" name="request_id" value="<?= $r['id'] ?>">
            <button class="btn btn--ghost btn--sm">Markera klar</button>
          </form>
          <?php else: ?>
          <form method="post" onsubmit="return confirm('Anonymisera personuppgifter permanent? Detta kan inte ångras.')"><?= csrf_field() ?><input type="hidden" name="action" value="resolve_anonymize"><input type="hidden" name="request_id" value="<?= $r['id'] ?>">
            <button class="btn btn--danger btn--sm">Anonymisera</button>
          </form>
          <button class="btn btn--ghost btn--sm" onclick="document.getElementById('rejectId<?= $r['id'] ?>').classList.toggle('open')">Avvisa</button>
          <?php endif; ?>
        </div>
        <?php if ($r['type'] === 'erasure'): ?>
        <div id="rejectId<?= $r['id'] ?>" class="modal-bg">
          <div class="modal">
            <h3>Avvisa raderingsbegäran</h3>
            <form method="post">
              <?= csrf_field() ?><input type="hidden" name="action" value="resolve_reject"><input type="hidden" name="request_id" value="<?= $r['id'] ?>">
              <div class="fg"><label>Anledning</label><textarea class="fta" name="reject_reason" required></textarea></div>
              <div style="display:flex;gap:10px;justify-content:flex-end">
                <button type="button" class="btn btn--ghost" onclick="document.getElementById('rejectId<?= $r['id'] ?>').classList.remove('open')">Avbryt</button>
                <button class="btn btn--primary">Avvisa</button>
              </div>
            </form>
          </div>
        </div>
        <?php endif; ?>
        <?php endif; ?>
      </td>
    </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
  <?php else: ?>
  <p style="font-size:13px;color:var(--gray)">Inga begäranden ännu.</p>
  <?php endif; ?>
</div>

<!-- Manual staff-triggered tools -->
<div class="detail-grid">
  <div class="card card--pad">
    <h3 style="font-size:14.5px;margin-bottom:12px">Manuell export — kund</h3>
    <form method="get" target="_blank">
      <input type="hidden" name="export" value="customer">
      <div class="fg"><label>Kund</label>
        <select class="fs" name="id" required><option value="">— Välj —</option>
          <?php foreach ($customers as $c): ?><option value="<?= $c['id'] ?>"><?= e($c['name']) ?></option><?php endforeach; ?>
        </select>
      </div>
      <button class="btn btn--primary btn--sm">Ladda ner JSON-export</button>
    </form>
  </div>
  <div class="card card--pad">
    <h3 style="font-size:14.5px;margin-bottom:12px">Manuell export — leverantör</h3>
    <form method="get" target="_blank">
      <input type="hidden" name="export" value="supplier">
      <div class="fg"><label>Leverantör</label>
        <select class="fs" name="id" required><option value="">— Välj —</option>
          <?php foreach ($suppliers as $s): ?><option value="<?= $s['id'] ?>"><?= e($s['company']) ?></option><?php endforeach; ?>
        </select>
      </div>
      <button class="btn btn--primary btn--sm">Ladda ner JSON-export</button>
    </form>
  </div>
</div>

<p style="font-size:11.5px;color:var(--gray-lt);margin-top:16px">
  Radering anonymiserar personuppgifter (namn, e-post, telefon, adress) men behåller fakturor och belopp i 7 år enligt bokföringslagen.
</p>

<?php require_once __DIR__ . '/includes/crm-footer.php'; ?>
