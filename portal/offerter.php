<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/layout.php';
$pu  = portal_require();
$cid = (int)$pu['customer_id'];

// Accept / reject action
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $qid    = (int)($_POST['quote_id'] ?? 0);
    $action = $_POST['action'] ?? '';
    // Verify this quote belongs to this customer
    $check = db()->prepare("SELECT id FROM quotes WHERE id = ? AND customer_id = ?");
    $check->execute([$qid, $cid]);
    $belongsToCustomer = (bool)$check->fetch();
    $signError = '';

    if ($belongsToCustomer && $action === 'accept') {
        $signerName = trim($_POST['signer_name'] ?? '');
        $signature  = trim($_POST['signature_data'] ?? '');
        $consent    = !empty($_POST['consent']);

        if ($signerName === '')                          $signError = 'Ange ditt fullständiga namn.';
        elseif (!$consent)                                $signError = 'Du måste bekräfta godkännandet.';
        elseif (strpos($signature, 'data:image/png') !== 0) $signError = 'Signaturen saknas. Rita din signatur i fältet.';
        else {
            $consentText = 'Jag, ' . $signerName . ', godkänner härmed offert #' . $qid . ' elektroniskt i enlighet med villkoren ovan.';
            db()->prepare(
                "INSERT INTO quote_signatures (quote_id, signer_name, signer_email, signature_data, consent_text, ip_address, user_agent)
                 VALUES (?,?,?,?,?,?,?)"
            )->execute([$qid, $signerName, $pu['email'] ?? null, $signature, $consentText, $_SERVER['REMOTE_ADDR'] ?? '', $_SERVER['HTTP_USER_AGENT'] ?? '']);

            db()->prepare("UPDATE quotes SET status='accepted', accepted_at=" . now_expr() . " WHERE id=?")->execute([$qid]);
            log_timeline('quote', $qid, 'status', 'Offert signerad och accepterad av kund (' . $signerName . ')', '', null);
            notify_role('sales', 'Offert signerad och accepterad!',
                'Kund ' . $pu['name'] . ' signerade och accepterade offert #' . $qid,
                '/crm/offert.php?id=' . $qid);

            header('Location: /portal/offerter.php?id=' . $qid . '&msg=' . urlencode('Offerten är signerad och accepterad! Vi återkommer snart.'));
            exit;
        }
    } elseif ($belongsToCustomer && $action === 'reject') {
        db()->prepare("UPDATE quotes SET status='rejected' WHERE id=?")->execute([$qid]);
        log_timeline('quote', $qid, 'status', 'Offert avvisad av kund', '', null);
        header('Location: /portal/offerter.php?id=' . $qid . '&msg=' . urlencode('Offerten är avvisad.'));
        exit;
    }
}

// Load quotes
$quotes = db()->prepare(
    "SELECT q.*, p.title AS project_title
     FROM quotes q
     LEFT JOIN projects p ON p.quote_id = q.id
     WHERE q.customer_id = ? ORDER BY q.created_at DESC"
);
$quotes->execute([$cid]);
$quotes = $quotes->fetchAll();

// Single quote
$qid   = (int)($_GET['id'] ?? 0);
$quote = null;
$items = [];
if ($qid) {
    $s = db()->prepare("SELECT * FROM quotes WHERE id = ? AND customer_id = ?");
    $s->execute([$qid, $cid]);
    $quote = $s->fetch();
    if (!$quote) { header('Location: /portal/offerter.php'); exit; }
    // Mark as viewed
    if ($quote['status'] === 'sent') {
        db()->prepare("UPDATE quotes SET status='viewed', viewed_at=" . now_expr() . " WHERE id=?")->execute([$qid]);
        $quote['status'] = 'viewed';
    }
    $s2 = db()->prepare("SELECT * FROM quote_items WHERE quote_id = ? ORDER BY sort_order");
    $s2->execute([$qid]);
    $items = $s2->fetchAll();

    $s3 = db()->prepare("SELECT * FROM quote_signatures WHERE quote_id = ?");
    $s3->execute([$qid]);
    $signature = $s3->fetch() ?: null;
}

portal_head($quote ? 'Offert ' . e($quote['quote_no'] ?? '') : 'Offerter', $pu);
portal_nav('/offerter.php');
?>
<main class="portal-main">
<?php if (isset($_GET['msg'])): ?>
<div class="alert alert--success"><?= e($_GET['msg']) ?></div>
<?php endif; ?>

<?php if ($quote): ?>
  <div class="portal-page-title">
    <a href="/portal/offerter.php" style="color:var(--steel);font-size:.85rem">← Alla offerter</a>
    <h1 style="margin-top:8px"><?= e($quote['title']) ?></h1>
    <p>Offertnummer: <strong><?= e($quote['quote_no'] ?? '—') ?></strong> · Giltig till: <?= e($quote['valid_until'] ?? '—') ?></p>
  </div>

  <div class="card" style="margin-bottom:20px">
    <!-- Items table -->
    <table class="tbl">
      <thead><tr><th>Beskrivning</th><th>Typ</th><th style="text-align:right">Antal</th><th style="text-align:right">Á-pris</th><th style="text-align:right">Summa</th></tr></thead>
      <tbody>
      <?php foreach ($items as $item): ?>
      <tr>
        <td><?= e($item['description']) ?></td>
        <td><?= $item['is_work'] ? 'Arbete' : 'Material' ?></td>
        <td style="text-align:right"><?= $item['qty'] ?> <?= e($item['unit']) ?></td>
        <td style="text-align:right"><?= number_format($item['unit_price'], 0, ',', ' ') ?> kr</td>
        <td style="text-align:right;font-weight:600"><?= number_format($item['total'], 0, ',', ' ') ?> kr</td>
      </tr>
      <?php endforeach; ?>
      </tbody>
    </table>

    <!-- Totals -->
    <div style="margin-top:20px;border-top:2px solid var(--border);padding-top:20px;max-width:320px;margin-left:auto">
      <?php
      $rows = [
        ['Nettosumma', number_format($quote['subtotal'] ?? 0, 0, ',', ' ') . ' kr'],
        ['Moms 25%',   number_format($quote['vat'] ?? 0, 0, ',', ' ') . ' kr'],
      ];
      if (($quote['rot_deduction'] ?? 0) > 0) {
        $rows[] = ['ROT-avdrag (30%)', '−' . number_format($quote['rot_deduction'], 0, ',', ' ') . ' kr'];
      }
      foreach ($rows as [$lbl, $val]): ?>
      <div style="display:flex;justify-content:space-between;font-size:.875rem;padding:5px 0;border-bottom:1px solid var(--border)">
        <span style="color:var(--steel)"><?= $lbl ?></span>
        <span><?= $val ?></span>
      </div>
      <?php endforeach; ?>
      <div style="display:flex;justify-content:space-between;font-size:1.1rem;font-weight:700;padding:12px 0 0">
        <span>Att betala</span>
        <span><?= number_format($quote['total'] ?? 0, 0, ',', ' ') ?> kr</span>
      </div>
    </div>
  </div>

  <?php if (in_array($quote['status'], ['sent','viewed'])): ?>

  <?php if (!empty($signError)): ?>
  <div class="alert alert--error"><?= e($signError) ?></div>
  <?php endif; ?>

  <div class="card">
    <div style="margin-bottom:16px">
      <strong>Din åtgärd behövs</strong>
      <p style="font-size:.875rem;color:var(--steel);margin-top:4px">Signera och acceptera offerten nedan, eller avvisa den. Du kan alltid kontakta oss med frågor.</p>
    </div>

    <!-- Reject (no signature needed) -->
    <form method="post" style="margin-bottom:20px;padding-bottom:20px;border-bottom:1px solid var(--border)">
      <input type="hidden" name="quote_id" value="<?= $quote['id'] ?>">
      <button name="action" value="reject" class="btn btn--danger btn--sm" onclick="return confirm('Avvisa offerten?')">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:14px;height:14px"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
        Avvisa offert
      </button>
    </form>

    <!-- Sign & accept -->
    <form method="post" id="signForm">
      <input type="hidden" name="quote_id" value="<?= $quote['id'] ?>">
      <input type="hidden" name="action" value="accept">
      <input type="hidden" name="signature_data" id="signatureData">

      <div class="form-group">
        <label class="form-label">Fullständigt namn</label>
        <input class="form-control" type="text" name="signer_name" value="<?= e($pu['name'] ?? '') ?>" required>
      </div>

      <div class="form-group">
        <label class="form-label">Signatur — rita med mus eller finger</label>
        <div style="border:2px dashed var(--border);border-radius:var(--r-md);background:#fff;position:relative">
          <canvas id="sigCanvas" width="500" height="160" style="width:100%;height:160px;touch-action:none;cursor:crosshair;display:block"></canvas>
          <button type="button" onclick="clearSignature()" style="position:absolute;top:8px;right:8px;background:none;border:1px solid var(--border);border-radius:6px;padding:4px 10px;font-size:.75rem;color:var(--steel);cursor:pointer">Rensa</button>
        </div>
      </div>

      <div class="form-group" style="display:flex;align-items:flex-start;gap:8px">
        <input type="checkbox" name="consent" id="consentBox" required style="margin-top:3px">
        <label for="consentBox" style="font-size:.8125rem;color:var(--steel);font-weight:400">
          Jag godkänner offerten elektroniskt och bekräftar att min signatur ovan utgör mitt bindande godkännande, i enlighet med svensk lag om elektroniska signaturer (eIDAS-förordningen).
        </label>
      </div>

      <button type="submit" class="btn btn--success" id="signSubmitBtn" disabled>
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:15px;height:15px"><polyline points="20,6 9,17 4,12"/></svg>
        Signera och acceptera offert
      </button>
    </form>
  </div>

  <script>
  (function () {
    var canvas = document.getElementById('sigCanvas');
    var ctx = canvas.getContext('2d');
    var drawing = false, hasDrawn = false;

    function resize() {
      var rect = canvas.getBoundingClientRect();
      canvas.width = rect.width;
      canvas.height = 160;
      ctx.strokeStyle = '#111318';
      ctx.lineWidth = 2;
      ctx.lineCap = 'round';
    }
    resize();
    window.addEventListener('resize', resize);

    function pos(e) {
      var rect = canvas.getBoundingClientRect();
      var x = (e.touches ? e.touches[0].clientX : e.clientX) - rect.left;
      var y = (e.touches ? e.touches[0].clientY : e.clientY) - rect.top;
      return { x: x, y: y };
    }
    function start(e) { drawing = true; hasDrawn = true; var p = pos(e); ctx.beginPath(); ctx.moveTo(p.x, p.y); checkReady(); e.preventDefault(); }
    function move(e) { if (!drawing) return; var p = pos(e); ctx.lineTo(p.x, p.y); ctx.stroke(); e.preventDefault(); }
    function end() { drawing = false; }

    canvas.addEventListener('mousedown', start);
    canvas.addEventListener('mousemove', move);
    canvas.addEventListener('mouseup', end);
    canvas.addEventListener('mouseleave', end);
    canvas.addEventListener('touchstart', start);
    canvas.addEventListener('touchmove', move);
    canvas.addEventListener('touchend', end);

    window.clearSignature = function () {
      ctx.clearRect(0, 0, canvas.width, canvas.height);
      hasDrawn = false;
      checkReady();
    };

    var consentBox = document.getElementById('consentBox');
    var nameInput = document.querySelector('input[name="signer_name"]');
    var submitBtn = document.getElementById('signSubmitBtn');
    function checkReady() {
      submitBtn.disabled = !(hasDrawn && consentBox.checked && nameInput.value.trim());
    }
    consentBox.addEventListener('change', checkReady);
    nameInput.addEventListener('input', checkReady);

    document.getElementById('signForm').addEventListener('submit', function () {
      document.getElementById('signatureData').value = canvas.toDataURL('image/png');
    });
  })();
  </script>

  <?php elseif ($quote['status'] === 'accepted'): ?>
  <div class="alert alert--success">✓ Du accepterade denna offert <?= e($quote['accepted_at'] ?? '') ?>.</div>
  <?php if (!empty($signature)): ?>
  <div class="card" style="margin-top:16px">
    <h3 style="font-size:1rem;margin-bottom:12px">Signatur</h3>
    <div style="display:flex;gap:20px;align-items:flex-start;flex-wrap:wrap">
      <img src="<?= e($signature['signature_data']) ?>" alt="Signatur" style="border:1px solid var(--border);border-radius:8px;max-width:300px;background:#fff">
      <div style="font-size:.8125rem;color:var(--steel);line-height:1.7">
        <div><strong style="color:var(--ink)"><?= e($signature['signer_name']) ?></strong></div>
        <div>Signerad: <?= e($signature['signed_at']) ?></div>
        <div>IP: <?= e($signature['ip_address'] ?? '—') ?></div>
      </div>
    </div>
  </div>
  <?php endif; ?>
  <?php elseif ($quote['status'] === 'rejected'): ?>
  <div class="alert alert--error">Denna offert avvisades. Kontakta oss om du vill diskutera alternativ.</div>
  <?php endif; ?>

<?php else: ?>
  <!-- Quote list -->
  <div class="portal-page-title"><h1>Offerter</h1><p>Dina offerter från M2 Bygg Team.</p></div>
  <?php if ($quotes): ?>
  <div style="display:flex;flex-direction:column;gap:10px">
    <?php foreach ($quotes as $q):
      $statusMap = ['draft'=>['gray','Utkast'],'sent'=>['blue','Inväntar svar'],'viewed'=>['orange','Visad — väntar svar'],'accepted'=>['green','Accepterad'],'rejected'=>['red','Avvisad']];
      [$col, $lbl] = $statusMap[$q['status']] ?? ['gray', $q['status']];
    ?>
    <a href="/portal/offerter.php?id=<?= $q['id'] ?>" class="card" style="text-decoration:none;color:inherit;display:flex;align-items:center;gap:16px;flex-wrap:wrap">
      <div style="flex:1">
        <strong><?= e($q['title']) ?></strong>
        <div style="font-size:.8rem;color:var(--steel);margin-top:2px"><?= e($q['quote_no'] ?? '—') ?> · Giltig till: <?= e($q['valid_until'] ?? '—') ?></div>
      </div>
      <span style="font-size:1.1rem;font-weight:700"><?= number_format($q['total'] ?? 0, 0, ',', ' ') ?> kr</span>
      <span class="badge badge--<?= $col ?>"><?= $lbl ?></span>
    </a>
    <?php endforeach; ?>
  </div>
  <?php else: ?>
  <div class="card" style="text-align:center;padding:48px"><p style="color:var(--steel)">Inga offerter ännu.</p></div>
  <?php endif; ?>
<?php endif; ?>
</main>
<?php portal_foot(); ?>
