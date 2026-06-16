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
    if ($check->fetch() && in_array($action, ['accept','reject'])) {
        $newStatus = $action === 'accept' ? 'accepted' : 'rejected';
        $col = $action === 'accept' ? 'accepted_at' : null;
        $sql = "UPDATE quotes SET status = ?" . ($col ? ", {$col} = datetime('now','localtime')" : "") . " WHERE id = ?";
        db()->prepare($sql)->execute([$newStatus, $qid]);
        log_timeline('quote', $qid, 'status', $action === 'accept' ? 'Offert accepterad av kund' : 'Offert avvisad av kund', '', null);
        // If accepted: trigger project creation (handled by CRM automation, just notify staff)
        if ($action === 'accept') {
            notify_role('sales', 'Offert accepterad!',
                'Kund ' . $pu['name'] . ' accepterade offert #' . $qid,
                '/crm/offert.php?id=' . $qid);
        }
        $msg = $action === 'accept' ? 'Offerten är accepterad! Vi återkommer snart.' : 'Offerten är avvisad.';
        header('Location: /portal/offerter.php?msg=' . urlencode($msg));
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
        db()->prepare("UPDATE quotes SET status='viewed', viewed_at=datetime('now','localtime') WHERE id=?")->execute([$qid]);
        $quote['status'] = 'viewed';
    }
    $s2 = db()->prepare("SELECT * FROM quote_items WHERE quote_id = ? ORDER BY sort_order");
    $s2->execute([$qid]);
    $items = $s2->fetchAll();
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
  <div class="card" style="display:flex;gap:12px;align-items:center;flex-wrap:wrap">
    <div style="flex:1">
      <strong>Din åtgärd behövs</strong>
      <p style="font-size:.875rem;color:var(--steel);margin-top:4px">Acceptera eller avvisa offerten. Du kan alltid kontakta oss med frågor.</p>
    </div>
    <form method="post" style="display:flex;gap:10px">
      <input type="hidden" name="quote_id" value="<?= $quote['id'] ?>">
      <button name="action" value="reject" class="btn btn--danger" onclick="return confirm('Avvisa offerten?')">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:15px;height:15px"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
        Avvisa
      </button>
      <button name="action" value="accept" class="btn btn--success" onclick="return confirm('Acceptera offerten?')">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:15px;height:15px"><polyline points="20,6 9,17 4,12"/></svg>
        Acceptera offert
      </button>
    </form>
  </div>
  <?php elseif ($quote['status'] === 'accepted'): ?>
  <div class="alert alert--success">✓ Du accepterade denna offert <?= e($quote['accepted_at'] ?? '') ?>.</div>
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
