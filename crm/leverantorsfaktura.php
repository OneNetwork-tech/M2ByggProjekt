<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/mailer.php';
$me = require_login();
$pdo = db();

$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT si.*, s.company AS sname, s.email AS semail FROM supplier_invoices si LEFT JOIN suppliers s ON s.id=si.supplier_id WHERE si.id=?");
$stmt->execute([$id]);
$inv = $stmt->fetch();
if (!$inv) { header('Location: leverantorsfakturor.php'); exit; }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $action = $_POST['action'] ?? '';

    if ($action === 'status') {
        $status = $_POST['status'];
        if (isset(SUPPLIER_INVOICE_STATUSES[$status])) {
            $pdo->prepare("UPDATE supplier_invoices SET status=? WHERE id=?")->execute([$status, $id]);
            log_timeline('supplier_invoice', $id, 'status', 'Status: ' . SUPPLIER_INVOICE_STATUSES[$status]['label'], '', $me['id']);

            if ($status === 'approved' && !empty($inv['semail'])) {
                crm_send_mail(
                    $inv['semail'], $inv['sname'] ?: $inv['semail'],
                    'Faktura godkänd — ' . $inv['invoice_no'],
                    '<p>Hej ' . htmlspecialchars($inv['sname'] ?? '', ENT_QUOTES, 'UTF-8') . '!</p><p>Din faktura <strong>' . htmlspecialchars($inv['invoice_no'], ENT_QUOTES, 'UTF-8') . '</strong> på ' . number_format($inv['total'],0,',',' ') . ' kr har godkänts och betalas ut enligt avtalade villkor.</p>',
                    'supplier_invoice', $id,
                    (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . '/leverantor/fakturor.php',
                    'Visa i leverantörsportalen'
                );
            } elseif ($status === 'rejected' && !empty($inv['semail'])) {
                crm_send_mail(
                    $inv['semail'], $inv['sname'] ?: $inv['semail'],
                    'Faktura avvisad — ' . $inv['invoice_no'],
                    '<p>Hej ' . htmlspecialchars($inv['sname'] ?? '', ENT_QUOTES, 'UTF-8') . '!</p><p>Din faktura <strong>' . htmlspecialchars($inv['invoice_no'], ENT_QUOTES, 'UTF-8') . '</strong> har avvisats. Kontakta oss för mer information.</p>',
                    'supplier_invoice', $id,
                    (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . '/leverantor/fakturor.php',
                    'Visa i leverantörsportalen'
                );
            } elseif ($status === 'paid' && !empty($inv['semail'])) {
                crm_send_mail(
                    $inv['semail'], $inv['sname'] ?: $inv['semail'],
                    'Faktura betald — ' . $inv['invoice_no'],
                    '<p>Hej ' . htmlspecialchars($inv['sname'] ?? '', ENT_QUOTES, 'UTF-8') . '!</p><p>Din faktura <strong>' . htmlspecialchars($inv['invoice_no'], ENT_QUOTES, 'UTF-8') . '</strong> på ' . number_format($inv['total'],0,',',' ') . ' kr har betalats ut.</p>',
                    'supplier_invoice', $id,
                    (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . '/leverantor/fakturor.php',
                    'Visa i leverantörsportalen'
                );
            }

            flash('Status uppdaterad.');
        }
        header("Location: leverantorsfaktura.php?id=$id"); exit;
    }

    if ($action === 'payment') {
        $amount = (float)$_POST['amount'];
        if ($amount > 0) {
            $pdo->prepare("INSERT INTO supplier_payments (supplier_invoice_id, amount, method, paid_at, note) VALUES (?,?,?,?,?)")
                ->execute([$id, $amount, $_POST['method'] ?? 'Bankgiro', $_POST['paid_at'] ?: date('Y-m-d'), trim($_POST['note'] ?? '')]);
            $pdo->prepare("UPDATE supplier_invoices SET paid_amount = paid_amount + ? WHERE id=?")->execute([$amount, $id]);
            refresh_supplier_invoice_status($id);
            log_timeline('supplier_invoice', $id, 'system', 'Betalning registrerad: ' . money($amount), $_POST['method'] ?? '', $me['id']);
            audit('supplier_payment', 'supplier_invoice', $id, (string)$amount);
            flash('Betalning registrerad.');

            if (!empty($inv['semail'])) {
                crm_send_mail(
                    $inv['semail'], $inv['sname'] ?: $inv['semail'],
                    'Betalning registrerad — ' . $inv['invoice_no'],
                    '<p>Hej ' . htmlspecialchars($inv['sname'] ?? '', ENT_QUOTES, 'UTF-8') . '!</p><p>En betalning på ' . number_format($amount,0,',',' ') . ' kr har registrerats för faktura <strong>' . htmlspecialchars($inv['invoice_no'], ENT_QUOTES, 'UTF-8') . '</strong>.</p>',
                    'supplier_invoice', $id
                );
            }
        }
        header("Location: leverantorsfaktura.php?id=$id"); exit;
    }

    if ($action === 'due_date') {
        $pdo->prepare("UPDATE supplier_invoices SET due_date=? WHERE id=?")->execute([$_POST['due_date'], $id]);
        flash('Datum uppdaterat.');
        header("Location: leverantorsfaktura.php?id=$id"); exit;
    }
}

$payments = $pdo->prepare("SELECT * FROM supplier_payments WHERE supplier_invoice_id=? ORDER BY paid_at DESC"); $payments->execute([$id]); $payments = $payments->fetchAll();
$tl = $pdo->prepare("SELECT * FROM timeline WHERE entity_type='supplier_invoice' AND entity_id=? ORDER BY created_at DESC"); $tl->execute([$id]); $timeline = $tl->fetchAll();
$remaining = max(0, $inv['total'] - $inv['paid_amount']);

$crm_title = $inv['invoice_no'];
$crm_page  = 'leverantorsfakturor';
require_once __DIR__ . '/includes/crm-header.php';
?>

<div class="topbar">
  <div>
    <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap">
      <a href="leverantorsfakturor.php" style="color:var(--gray-lt);display:flex"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:18px;height:18px"><path d="M19 12H5M12 19l-7-7 7-7"/></svg></a>
      <h1><?= e($inv['invoice_no']) ?></h1>
      <?= badge($inv['status'], SUPPLIER_INVOICE_STATUSES) ?>
    </div>
    <div class="topbar__sub"><?= e($inv['sname'] ?: 'Ingen leverantör') ?> · Förfaller <?= dt($inv['due_date']) ?></div>
  </div>
  <div class="topbar__actions">
    <?php if ($remaining > 0 && $inv['status'] === 'approved'): ?>
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
        <div style="font-size:26px;font-weight:800;letter-spacing:-.02em">LEVERANTÖRSFAKTURA</div>
        <div style="font-size:13px;color:var(--gray)"><?= e($inv['invoice_no']) ?></div>
      </div>
    </div>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:28px;font-size:13.5px">
      <div>
        <div style="font-size:10.5px;text-transform:uppercase;letter-spacing:.08em;color:var(--gray-lt);margin-bottom:6px">Från leverantör</div>
        <div style="font-weight:600"><?= e($inv['sname'] ?: '–') ?></div>
        <div><?= e($inv['semail'] ?? '') ?></div>
      </div>
      <div style="text-align:right">
        <div><span style="color:var(--gray-lt)">Skapad:</span> <?= dt($inv['created_at']) ?></div>
        <div><span style="color:var(--gray-lt)">Förfallodatum:</span> <strong><?= dt($inv['due_date']) ?></strong></div>
      </div>
    </div>
    <div style="margin-bottom:20px;font-size:13.5px;color:var(--ink-soft)"><?= nl2br(e($inv['description'] ?? '')) ?></div>
    <table>
      <thead><tr><th>Beskrivning</th><th style="text-align:right">Summa</th></tr></thead>
      <tbody>
        <tr><td colspan="1" style="text-align:right;color:var(--gray)">Delsumma</td><td style="text-align:right"><?= money($inv['amount']) ?></td></tr>
        <tr><td colspan="1" style="text-align:right;color:var(--gray)">Moms</td><td style="text-align:right"><?= money($inv['vat']) ?></td></tr>
        <tr class="tr-total"><td style="text-align:right">Att betala</td><td style="text-align:right"><?= money($inv['total']) ?></td></tr>
      </tbody>
    </table>
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

    <!-- DUE DATE -->
    <div class="card card--pad">
      <h3 style="font-size:14.5px;margin-bottom:12px">Förfallodatum</h3>
      <form method="POST">
        <?= csrf_field() ?><input type="hidden" name="action" value="due_date">
        <div class="fg"><input class="fi" type="date" name="due_date" value="<?= e($inv['due_date']) ?>"></div>
        <button class="btn btn--ghost btn--sm" style="width:100%;justify-content:center">Uppdatera</button>
      </form>
    </div>

    <!-- ACTIONS -->
    <div class="card card--pad">
      <h3 style="font-size:14.5px;margin-bottom:12px">Åtgärder</h3>
      <div style="display:flex;flex-direction:column;gap:8px">
        <?php foreach (SUPPLIER_INVOICE_STATUSES as $k => $cfg): if ($k === $inv['status']) continue; ?>
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
          <select class="fs" name="method"><option>Bankgiro</option><option>Swish</option><option>Banköverföring</option></select>
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
