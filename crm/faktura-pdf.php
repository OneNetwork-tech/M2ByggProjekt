<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/helpers.php';
require_login();
$pdo = db();

$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT i.*, c.name AS cname, c.address AS caddr, c.city AS ccity, c.postal_code AS czip, c.email AS cemail, c.phone AS cphone
  FROM invoices i LEFT JOIN customers c ON c.id=i.customer_id WHERE i.id=?");
$stmt->execute([$id]);
$inv = $stmt->fetch();
if (!$inv) die('Faktura saknas');

// Access check: staff session OR the logged-in portal customer who owns this invoice
require_once __DIR__ . '/../portal/includes/auth.php';
$portalUser = portal_user();
$staffUser  = current_user();
if (!$staffUser && (!$portalUser || (int)$portalUser['customer_id'] !== (int)$inv['customer_id'])) {
    http_response_code(403); die('Åtkomst nekad');
}

$items = $pdo->prepare("SELECT * FROM invoice_items WHERE invoice_id=?");
$items->execute([$id]); $items = $items->fetchAll();

$name  = $inv['cname'] ?: '–';
$addr  = $inv['caddr'] ?: '';
$city  = trim(($inv['czip'] ?? '') . ' ' . ($inv['ccity'] ?: ''));
$email = $inv['cemail'] ?: '';
$phone = $inv['cphone'] ?: '';
$remaining = (float)$inv['total'] - (float)$inv['paid_amount'];
?>
<!DOCTYPE html>
<html lang="sv">
<head>
<meta charset="UTF-8">
<title>Faktura <?= e($inv['invoice_no']) ?> – M2 Bygg Team AB</title>
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:"SF Pro Text",-apple-system,"Inter","Segoe UI",sans-serif;color:#111827;background:#F5F6F8;padding:32px;font-size:14px;line-height:1.55;-webkit-font-smoothing:antialiased}
.sheet{max-width:800px;margin:0 auto;background:#fff;border-radius:16px;padding:48px 52px;box-shadow:0 4px 24px rgba(16,24,40,.08)}
.head{display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:40px}
.logo{display:flex;align-items:center;gap:11px}
.logo-mark{width:46px;height:46px;background:#0B1220;border-radius:11px;display:flex;align-items:center;justify-content:center;color:#fff;font-weight:800;font-size:17px;letter-spacing:-1px}
.logo-name{font-weight:700;font-size:17px}
.logo-sub{font-size:11px;color:#9CA3AF;text-transform:uppercase;letter-spacing:.08em}
.doc-type{text-align:right}
.doc-type h1{font-size:30px;font-weight:800;letter-spacing:-.03em;line-height:1}
.doc-type .no{font-size:13px;color:#6B7280;margin-top:5px}
.parties{display:grid;grid-template-columns:1fr 1fr;gap:24px;margin-bottom:36px}
.party h4{font-size:10.5px;font-weight:600;letter-spacing:.1em;text-transform:uppercase;color:#9CA3AF;margin-bottom:7px}
.party p{font-size:13.5px;line-height:1.6}
.party .strong{font-weight:600}
table{width:100%;border-collapse:collapse;margin-bottom:8px}
th{text-align:left;padding:9px 12px;font-size:10.5px;text-transform:uppercase;letter-spacing:.07em;color:#9CA3AF;border-bottom:2px solid #111827}
td{padding:11px 12px;font-size:13.5px;border-bottom:1px solid #F3F4F6}
.num{text-align:right;white-space:nowrap}
.totals{margin-left:auto;width:300px;margin-top:14px}
.totals .row{display:flex;justify-content:space-between;padding:6px 0;font-size:13.5px}
.totals .row span:first-child{color:#6B7280}
.totals .rot{color:#059669}
.totals .grand{border-top:2px solid #111827;margin-top:6px;padding-top:10px;font-size:17px;font-weight:800}
.totals .remaining{margin-top:6px;padding-top:6px;font-size:13.5px;color:#D97706;font-weight:700}
.footer{margin-top:40px;padding-top:20px;border-top:1px solid #E5E7EB;display:flex;justify-content:space-between;font-size:11.5px;color:#9CA3AF;flex-wrap:wrap;gap:8px}
.print-bar{max-width:800px;margin:0 auto 16px;display:flex;justify-content:flex-end;gap:9px}
.btn{display:inline-flex;align-items:center;gap:6px;font-size:13.5px;font-weight:500;padding:9px 17px;border-radius:10px;border:none;cursor:pointer;text-decoration:none}
.btn-p{background:#0066FF;color:#fff}.btn-g{background:#fff;color:#374151;border:1px solid #E5E7EB}
@media print{body{background:#fff;padding:0}.sheet{box-shadow:none;border-radius:0;padding:24px}.print-bar{display:none}}
</style>
</head>
<body>
<div class="print-bar">
  <?php if ($staffUser): ?><a href="faktura.php?id=<?= $id ?>" class="btn btn-g">← Tillbaka</a><?php endif; ?>
  <button class="btn btn-p" onclick="print()">🖨 Skriv ut / Spara PDF</button>
</div>
<div class="sheet">
  <div class="head">
    <div class="logo">
      <div class="logo-mark">m2</div>
      <div><div class="logo-name">M2 Bygg Team AB</div><div class="logo-sub">Trygga byggtjänster · Fast pris</div></div>
    </div>
    <div class="doc-type">
      <h1>FAKTURA</h1>
      <div class="no"><?= e($inv['invoice_no']) ?></div>
    </div>
  </div>

  <div class="parties">
    <div class="party">
      <h4>Till</h4>
      <p class="strong"><?= e($name) ?></p>
      <?php if ($addr): ?><p><?= e($addr) ?></p><?php endif; ?>
      <?php if ($city): ?><p><?= e($city) ?></p><?php endif; ?>
      <?php if ($phone): ?><p><?= e($phone) ?></p><?php endif; ?>
      <?php if ($email): ?><p><?= e($email) ?></p><?php endif; ?>
    </div>
    <div class="party" style="text-align:right">
      <h4>Fakturainformation</h4>
      <p><span style="color:#9CA3AF">Fakturadatum:</span> <?= dt($inv['issue_date']) ?></p>
      <p><span style="color:#9CA3AF">Förfallodatum:</span> <strong><?= dt($inv['due_date']) ?></strong></p>
    </div>
  </div>

  <table>
    <thead><tr><th>Beskrivning</th><th class="num">Antal</th><th class="num">Enhet</th><th class="num">À-pris</th><th class="num">Summa</th></tr></thead>
    <tbody>
      <?php foreach ($items as $it): ?>
      <tr>
        <td><?= e($it['description']) ?></td>
        <td class="num"><?= rtrim(rtrim(number_format($it['qty'], 1, ',', ' '), '0'), ',') ?></td>
        <td class="num"><?= e($it['unit']) ?></td>
        <td class="num"><?= money($it['unit_price']) ?></td>
        <td class="num"><strong><?= money($it['total']) ?></strong></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>

  <div class="totals">
    <div class="row"><span>Delsumma (exkl. moms)</span><strong><?= money($inv['subtotal']) ?></strong></div>
    <div class="row"><span>Moms 25%</span><strong><?= money($inv['vat']) ?></strong></div>
    <?php if ($inv['rot_deduction'] > 0): ?>
    <div class="row rot"><span>ROT-avdrag</span><strong>−<?= money($inv['rot_deduction']) ?></strong></div>
    <?php endif; ?>
    <div class="row grand"><span>Totalt</span><span><?= money($inv['total']) ?></span></div>
    <?php if ($inv['paid_amount'] > 0): ?>
    <div class="row"><span>Betalt</span><strong style="color:#059669">−<?= money($inv['paid_amount']) ?></strong></div>
    <?php endif; ?>
    <?php if ($remaining > 0): ?>
    <div class="row remaining"><span>Att betala</span><span><?= money($remaining) ?></span></div>
    <?php endif; ?>
  </div>

  <div class="footer">
    <span>M2 Bygg Team AB · Lillhagsvägen 88, 442 43 Hisings Backa</span>
    <span>031-96 88 88 · info@m2team.se · www.m2team.se</span>
  </div>
</div>
</body>
</html>
