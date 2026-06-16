<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/helpers.php';
$me = require_login();
$pdo = db();

$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT q.*, c.name AS cname, c.address AS caddr, c.city AS ccity, c.postal_code AS czip, c.email AS cemail, c.phone AS cphone, l.name AS lname, l.address AS laddr, l.city AS lcity, l.email AS lemail, l.phone AS lphone
  FROM quotes q LEFT JOIN customers c ON c.id=q.customer_id LEFT JOIN leads l ON l.id=q.lead_id WHERE q.id=?");
$stmt->execute([$id]);
$q = $stmt->fetch();
if (!$q) die('Offert saknas');

$items = $pdo->prepare("SELECT * FROM quote_items WHERE quote_id=? ORDER BY sort_order");
$items->execute([$id]); $items = $items->fetchAll();

$name  = $q['cname'] ?: $q['lname'] ?: '–';
$addr  = $q['caddr'] ?: $q['laddr'] ?: '';
$city  = trim(($q['czip'] ?? '') . ' ' . ($q['ccity'] ?: $q['lcity'] ?: ''));
$email = $q['cemail'] ?: $q['lemail'] ?: '';
$phone = $q['cphone'] ?: $q['lphone'] ?: '';
?>
<!DOCTYPE html>
<html lang="sv">
<head>
<meta charset="UTF-8">
<title>Offert <?= e($q['quote_no']) ?> – M2 Bygg Team AB</title>
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
.terms{margin-top:36px;background:#F5F6F8;border-radius:12px;padding:20px 24px}
.terms h4{font-size:11px;font-weight:600;letter-spacing:.08em;text-transform:uppercase;color:#6B7280;margin-bottom:8px}
.terms p{font-size:13px;color:#374151;white-space:pre-line;line-height:1.7}
.badges{display:flex;gap:8px;margin-top:24px;flex-wrap:wrap}
.bdg{display:inline-flex;align-items:center;gap:5px;font-size:12px;font-weight:600;padding:6px 13px;border-radius:99px;background:#E8F0FE;color:#0066FF}
.footer{margin-top:40px;padding-top:20px;border-top:1px solid #E5E7EB;display:flex;justify-content:space-between;font-size:11.5px;color:#9CA3AF;flex-wrap:wrap;gap:8px}
.print-bar{max-width:800px;margin:0 auto 16px;display:flex;justify-content:flex-end;gap:9px}
.btn{display:inline-flex;align-items:center;gap:6px;font-size:13.5px;font-weight:500;padding:9px 17px;border-radius:10px;border:none;cursor:pointer;text-decoration:none}
.btn-p{background:#0066FF;color:#fff}.btn-g{background:#fff;color:#374151;border:1px solid #E5E7EB}
@media print{body{background:#fff;padding:0}.sheet{box-shadow:none;border-radius:0;padding:24px}.print-bar{display:none}}
</style>
</head>
<body>
<div class="print-bar">
  <a href="offert.php?id=<?= $id ?>" class="btn btn-g">← Tillbaka</a>
  <button class="btn btn-p" onclick="print()">🖨 Skriv ut / Spara PDF</button>
</div>
<div class="sheet">
  <div class="head">
    <div class="logo">
      <div class="logo-mark">m2</div>
      <div><div class="logo-name">M2 Bygg Team AB</div><div class="logo-sub">Trygga byggtjänster · Fast pris</div></div>
    </div>
    <div class="doc-type">
      <h1>OFFERT</h1>
      <div class="no"><?= e($q['quote_no']) ?></div>
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
      <h4>Offertinformation</h4>
      <p><span style="color:#9CA3AF">Datum:</span> <?= dt($q['created_at']) ?></p>
      <p><span style="color:#9CA3AF">Giltig till:</span> <strong><?= dt($q['valid_until']) ?></strong></p>
      <p><span style="color:#9CA3AF">Projekt:</span> <?= e($q['title']) ?></p>
    </div>
  </div>

  <table>
    <thead><tr><th>Beskrivning</th><th class="num">Antal</th><th class="num">Enhet</th><th class="num">À-pris</th><th class="num">Summa</th></tr></thead>
    <tbody>
      <?php foreach ($items as $it): ?>
      <tr>
        <td><?= e($it['description']) ?><?= $it['is_work'] ? ' <span style="font-size:10px;color:#059669;font-weight:600">ROT</span>' : '' ?></td>
        <td class="num"><?= rtrim(rtrim(number_format($it['qty'], 1, ',', ' '), '0'), ',') ?></td>
        <td class="num"><?= e($it['unit']) ?></td>
        <td class="num"><?= money($it['unit_price']) ?></td>
        <td class="num"><strong><?= money($it['total']) ?></strong></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>

  <div class="totals">
    <div class="row"><span>Delsumma (exkl. moms)</span><strong><?= money($q['subtotal']) ?></strong></div>
    <div class="row"><span>Moms 25%</span><strong><?= money($q['vat']) ?></strong></div>
    <div class="row rot"><span>ROT-avdrag</span><strong>−<?= money($q['rot_deduction']) ?></strong></div>
    <div class="row grand"><span>Att betala</span><span><?= money($q['total']) ?></span></div>
  </div>

  <div class="badges">
    <span class="bdg">✓ Fast pris – prisgaranti</span>
    <span class="bdg">✓ 5 år garanti</span>
    <span class="bdg">✓ Vi hanterar ROT-ansökan</span>
    <span class="bdg">✓ Försäkrad verksamhet</span>
  </div>

  <?php if ($q['notes']): ?>
  <div class="terms">
    <h4>Villkor</h4>
    <p><?= e($q['notes']) ?></p>
  </div>
  <?php endif; ?>

  <div class="footer">
    <span>M2 Bygg Team AB · Lillhagsvägen 88, 442 43 Hisings Backa</span>
    <span>031-96 88 88 · info@m2team.se · www.m2team.se</span>
  </div>
</div>
</body>
</html>
