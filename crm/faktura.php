<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/helpers.php';
$me = require_login();
$pdo = db();

$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT i.*, c.name AS cname, c.address AS caddr, c.city AS ccity, c.postal_code AS czip FROM invoices i LEFT JOIN customers c ON c.id=i.customer_id WHERE i.id=?");
$stmt->execute([$id]);
$inv = $stmt->fetch();
if (!$inv) { header('Location: fakturor.php'); exit; }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $action = $_POST['action'] ?? '';

    if ($action === 'status') {
        $status = $_POST['status'];
        if (isset(INVOICE_STATUSES[$status])) {
            $pdo->prepare("UPDATE invoices SET status=? WHERE id=?")->execute([$status, $id]);
            log_timeline('invoice', $id, 'status', 'Status: ' . INVOICE_STATUSES[$status]['label'], '', $me['id']);
            flash('Status uppdaterad.');
        }
        header("Location: faktura.php?id=$id"); exit;
    }

    // REGISTER PAYMENT (partial/full per blueprint)
    if ($action === 'payment') {
        $amount = (float)$_POST['amount'];
        if ($amount > 0) {
            $pdo->prepare("INSERT INTO payments (invoice_id, amount, method, paid_at, note) VALUES (?,?,?,?,?)")
                ->execute([$id, $amount, $_POST['method'] ?? 'Bankgiro', $_POST['paid_at'] ?: date('Y-m-d'), trim($_POST['note'] ?? '')]);
            $pdo->prepare("UPDATE invoices SET paid_amount = paid_amount + ? WHERE id=?")->execute([$amount, $id]);
            refresh_invoice_status($id);
            log_timeline('invoice', $id, 'system', 'Betalning registrerad: ' . money($amount), $_POST['method'] ?? '', $me['id']);
            audit('payment', 'invoice', $id, (string)$amount);
            flash('Betalning registrerad.');
        }
        header("Location: faktura.php?id=$id"); exit;
    }

    if ($action === 'due_date') {
        $pdo->prepare("UPDATE invoices SET due_date=?, issue_date=? WHERE id=?")->execute([$_POST['due_date'], $_POST['issue_date'], $id]);
        refresh_invoice_status($id);
        flash('Datum uppdaterade.');
        header("Location: faktura.php?id=$id"); exit;
    }
}

$items = $pdo->prepare("SELECT * FROM invoice_items WHERE invoice_id=?"); $items->execute([$id]); $items = $items->fetchAll();
$payments = $pdo->prepare("SELECT * FROM payments WHERE invoice_id=? ORDER BY paid_at DESC"); $payments->execute([$id]); $payments = $payments->fetchAll();
$tl = $pdo->prepare("SELECT * FROM timeline WHERE entity_type='invoice' AND entity_id=? ORDER BY created_at DESC"); $tl->execute([$id]); $timeline = $tl->fetchAll();
$remaining = max(0, $inv['total'] - $inv['paid_amount']);

$crm_title = $inv['invoice_no'];
$crm_page  = 'fakturor';
require_once __DIR__ . '/includes/crm-header.php';
?>

<div class="topbar">
  <div>
    <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap">
      <a href="fakturor.php" style="color:var(--gray-lt);display:flex"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:18px;height:18px"><path d="M19 12H5M12 19l-7-7 7-7"/></svg></a>
      <h1><?= e($inv['invoice_no']) ?></h1>
      <?= badge($inv['status'], INVOICE_STATUSES) ?>
    </div>
    <div class="topbar__sub"><?= e($inv['cname'] ?: 'Ingen kund') ?> · Förfaller <?= dt($inv['due_date']) ?></div>
  </div>
  <div class="topbar__actions">
    <button class="btn btn--ghost" onclick="print()">🖨 Skriv ut</button>
    <?php if ($inv['status'] === 'draft'): ?>
    <form method="POST" style="display:inline"><?= csrf_field() ?><input type="hidden" name="action" value="status"><input type="hidden" name="status" value="sent">
      <button class="btn btn--primary">Markera som skickad</button></form>
    <?php endif; ?>
    <?php if ($remaining > 0 && !in_array($inv['status'], ['draft','cancelled'])): ?>
    <button class="btn btn--green" onclick="openModal('payModal')">+ Registrera betalning</button>
    <?php endif; ?>
  </div>
</div>

<?php flash(); ?>

<div class="detail-grid">
  <!-- INVOICE DOCUMENT -->
  <div class="doc">
    <div style="display:flex;justify-content:space-between;margin-bottom:32px">
      <div style="display:flex;align-items:center;gap:10px">
        <div style="width:44px;height:44px;background:var(--navy);border-radius:10px;display:flex;align-items:center;justify-content:center;color:#fff;font-weight:800;font-size:16px">m2</div>
        <div><div style="font-weight:700">M2 Bygg Team AB</div><div style="font-size:11px;color:var(--gray-lt)">Lillhagsvägen 88, Hisings Backa</div></div>
      </div>
      <div style="text-align:right">
        <div style="font-size:26px;font-weight:800;letter-spacing:-.02em">FAKTURA</div>
        <div style="font-size:13px;color:var(--gray)"><?= e($inv['invoice_no']) ?></div>
      </div>
    </div>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:28px;font-size:13.5px">
      <div>
        <div style="font-size:10.5px;text-transform:uppercase;letter-spacing:.08em;color:var(--gray-lt);margin-bottom:6px">Faktureras till</div>
        <div style="font-weight:600"><?= e($inv['cname'] ?: '–') ?></div>
        <?php if ($inv['caddr']): ?><div><?= e($inv['caddr']) ?></div><?php endif; ?>
        <div><?= e(trim(($inv['czip'] ?? '') . ' ' . ($inv['ccity'] ?? ''))) ?></div>
      </div>
      <div style="text-align:right">
        <div><span style="color:var(--gray-lt)">Fakturadatum:</span> <?= dt($inv['issue_date']) ?></div>
        <div><span style="color:var(--gray-lt)">Förfallodatum:</span> <strong><?= dt($inv['due_date']) ?></strong></div>
        <div><span style="color:var(--gray-lt)">Betalningsvillkor:</span> 30 dagar</div>
      </div>
    </div>
    <table>
      <thead><tr><th>Beskrivning</th><th style="text-align:right">Antal</th><th style="text-align:right">À-pris</th><th style="text-align:right">Summa</th></tr></thead>
      <tbody>
        <?php foreach ($items as $it): ?>
        <tr>
          <td><?= e($it['description']) ?></td>
          <td style="text-align:right"><?= rtrim(rtrim(number_format($it['qty'],1,',',' '),'0'),',') ?> <?= e($it['unit']) ?></td>
          <td style="text-align:right"><?= money($it['unit_price']) ?></td>
          <td style="text-align:right;font-weight:600"><?= money($it['total']) ?></td>
        </tr>
        <?php endforeach; ?>
        <tr><td colspan="3" style="text-align:right;color:var(--gray)">Delsumma</td><td style="text-align:right"><?= money($inv['subtotal']) ?></td></tr>
        <tr><td colspan="3" style="text-align:right;color:var(--gray)">Moms 25%</td><td style="text-align:right"><?= money($inv['vat']) ?></td></tr>
        <tr><td colspan="3" style="text-align:right;color:var(--green)">ROT-avdrag</td><td style="text-align:right;color:var(--green)">−<?= money($inv['rot_deduction']) ?></td></tr>
        <tr class="tr-total"><td colspan="3" style="text-align:right">Att betala</td><td style="text-align:right"><?= money($inv['total']) ?></td></tr>
      </tbody>
    </table>
    <div style="margin-top:24px;font-size:12px;color:var(--gray-lt);display:flex;justify-content:space-between;flex-wrap:wrap;gap:8px">
      <span>Bankgiro: XXX-XXXX · Org.nr: XXXXXX-XXXX</span>
      <span>031-96 88 88 · info@m2team.se</span>
    </div>
  </div>

  <!-- RIGHT -->
  <div style="display:flex;flex-direction:column;gap:16px" class="no-print">
    <!-- PAYMENT STATUS -->
    <div class="card card--pad">
      <h3 style="font-size:14.5px;margin-bottom:12px">Betalningsstatus</h3>
      <div style="display:flex;flex-direction:column;gap:8px;font-size:13.5px">
        <div style="display:flex;justify-content:space-between"><span style="color:var(--gray)">Totalt</span><strong><?= money($inv['total']) ?></strong></div>
        <div style="display:flex;justify-content:space-between;color:var(--green)"><span>Betalt</span><strong><?= money($inv['paid_amount']) ?></strong></div>
        <div style="display:flex;justify-content:space-between;border-top:1px solid var(--border);padding-top:8px"><span style="font-weight:600">Kvar att betala</span><strong style="color:<?= $remaining > 0 ? 'var(--amber)' : 'var(--green)' ?>"><?= money($remaining) ?></strong></div>
      </div>
      <div class="progress" style="margin-top:12px"><div class="progress__bar" style="width:<?= $inv['total'] > 0 ? min(100, $inv['paid_amount']/$inv['total']*100) : 0 ?>%;background:var(--green)"></div></div>
      <?php if ($payments): ?>
      <div style="margin-top:14px;border-top:1px solid var(--border);padding-top:12px">
        <div style="font-size:11px;text-transform:uppercase;letter-spacing:.07em;color:var(--gray-lt);margin-bottom:8px">Betalningar</div>
        <?php foreach ($payments as $p): ?>
        <div style="display:flex;justify-content:space-between;font-size:12.5px;padding:5px 0">
          <span><?= dt($p['paid_at']) ?> · <?= e($p['method']) ?></span>
          <strong style="color:var(--green)"><?= money($p['amount']) ?></strong>
        </div>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>
    </div>

    <!-- DATES -->
    <div class="card card--pad">
      <h3 style="font-size:14.5px;margin-bottom:12px">Datum</h3>
      <form method="POST">
        <?= csrf_field() ?><input type="hidden" name="action" value="due_date">
        <div class="fg"><label>Fakturadatum</label><input class="fi" type="date" name="issue_date" value="<?= e($inv['issue_date']) ?>"></div>
        <div class="fg"><label>Förfallodatum</label><input class="fi" type="date" name="due_date" value="<?= e($inv['due_date']) ?>"></div>
        <button class="btn btn--ghost btn--sm" style="width:100%;justify-content:center">Uppdatera</button>
      </form>
    </div>

    <!-- ACTIONS -->
    <div class="card card--pad">
      <h3 style="font-size:14.5px;margin-bottom:12px">Åtgärder</h3>
      <div style="display:flex;flex-direction:column;gap:8px">
        <?php foreach (INVOICE_STATUSES as $k => $cfg): if ($k === $inv['status']) continue; ?>
        <form method="POST"><?= csrf_field() ?><input type="hidden" name="action" value="status"><input type="hidden" name="status" value="<?= $k ?>">
          <button class="btn btn--ghost btn--sm" style="width:100%;justify-content:center">Markera: <?= e($cfg['label']) ?></button>
        </form>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- TIMELINE -->
    <div class="card card--pad">
      <h3 style="font-size:14.5px;margin-bottom:14px">Aktivitet</h3>
      <div class="timeline">
        <?php foreach ($timeline as $ev): ?>
        <div class="tl-item">
          <div class="tl-dot gray"></div>
          <div class="tl-title"><?= e($ev['title']) ?></div>
          <div class="tl-meta"><?= time_ago($ev['created_at']) ?></div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</div>

<!-- PAYMENT MODAL -->
<div class="modal-bg" id="payModal">
  <div class="modal">
    <h3>Registrera betalning</h3>
    <form method="POST">
      <?= csrf_field() ?><input type="hidden" name="action" value="payment">
      <div class="fg"><label>Belopp (kr) – kvar: <?= money($remaining) ?></label>
        <input class="fi" type="number" step="1" name="amount" value="<?= $remaining ?>" max="<?= $remaining ?>" required>
      </div>
      <div class="frow">
        <div class="fg"><label>Metod</label>
          <select class="fs" name="method"><option>Bankgiro</option><option>Swish</option><option>Banköverföring</option><option>Kort</option></select>
        </div>
        <div class="fg"><label>Datum</label><input class="fi" type="date" name="paid_at" value="<?= date('Y-m-d') ?>"></div>
      </div>
      <div class="fg"><label>Anteckning</label><input class="fi" name="note" placeholder="Valfritt"></div>
      <div style="display:flex;gap:10px;justify-content:flex-end">
        <button type="button" class="btn btn--ghost" onclick="closeModal('payModal')">Avbryt</button>
        <button class="btn btn--green">Registrera</button>
      </div>
    </form>
  </div>
</div>

<?php require_once __DIR__ . '/includes/crm-footer.php'; ?>
