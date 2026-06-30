<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/layout.php';
$su  = supp_require();
$sid = (int)$su['supplier_id'];

// Accepted jobs for the project dropdown
$s = db()->prepare("
    SELECT ja.id, p.id AS project_id, p.title AS project_title
    FROM job_assignments ja JOIN projects p ON p.id=ja.project_id
    WHERE ja.supplier_id=? AND ja.status='accepted'
    ORDER BY p.title
");
$s->execute([$sid]);
$acceptedJobs = $s->fetchAll();

$error = ''; $success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $projectId = (int)($_POST['project_id'] ?? 0) ?: null;
    $amount    = (float)($_POST['amount'] ?? 0);
    $vat       = round($amount * VAT_RATE, 2);
    $total     = $amount + $vat;
    $desc      = trim($_POST['description'] ?? '');
    $dueDate   = date('Y-m-d', strtotime('+30 days'));

    if ($amount <= 0) {
        $error = 'Beloppet måste vara större än 0.';
    } elseif ($desc === '') {
        $error = 'Beskriv vad fakturan avser.';
    } else {
        $invoiceNo = next_number('LF', 'supplier_invoices', 'invoice_no');
        db()->prepare(
            "INSERT INTO supplier_invoices (invoice_no, supplier_id, project_id, amount, vat, total, due_date, description) VALUES (?,?,?,?,?,?,?,?)"
        )->execute([$invoiceNo, $sid, $projectId, $amount, $vat, $total, $dueDate, $desc]);
        $newId = (int)db()->lastInsertId();
        log_timeline('supplier_invoice', $newId, 'system', 'Faktura inskickad av leverantör: ' . $invoiceNo);
        $success = 'Fakturan ' . $invoiceNo . ' har skickats in för granskning.';
    }
}

$s = db()->prepare("
    SELECT si.*, p.title AS project_title
    FROM supplier_invoices si LEFT JOIN projects p ON p.id=si.project_id
    WHERE si.supplier_id=? ORDER BY si.created_at DESC
");
$s->execute([$sid]);
$invoices = $s->fetchAll();

supp_head('Fakturor', $su);
supp_nav('/fakturor.php');
?>
<main class="portal-main">
  <div class="portal-page-title"><h1>Fakturor</h1><p>Skicka in fakturor för utfört arbete och följ status.</p></div>

  <?php if ($error): ?><div class="alert alert--error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
  <?php if ($success): ?><div class="alert alert--success"><?= htmlspecialchars($success) ?></div><?php endif; ?>

  <div class="card" style="margin-bottom:24px">
    <div class="card-header"><h3>Skicka in ny faktura</h3></div>
    <form method="post" style="padding:20px;display:flex;flex-direction:column;gap:14px">
      <div class="form-group">
        <label class="form-label">Projekt (valfritt)</label>
        <select class="form-control" name="project_id">
          <option value="">— Inget specifikt projekt —</option>
          <?php foreach ($acceptedJobs as $j): ?>
          <option value="<?= (int)$j['project_id'] ?>"><?= htmlspecialchars($j['project_title']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-group">
        <label class="form-label">Belopp exkl. moms (kr) *</label>
        <input class="form-control" type="number" step="1" min="1" name="amount" required>
      </div>
      <div class="form-group">
        <label class="form-label">Beskrivning *</label>
        <textarea class="form-control" name="description" rows="3" placeholder="Vad avser fakturan?" required></textarea>
      </div>
      <button type="submit" class="btn btn--primary" style="align-self:flex-start">Skicka in faktura</button>
    </form>
  </div>

  <div class="card">
    <div class="card-header"><h3>Mina fakturor</h3></div>
    <?php if ($invoices): ?>
    <table class="portal-table">
      <thead><tr><th>Nr</th><th>Projekt</th><th>Belopp</th><th>Status</th><th>Förfaller</th></tr></thead>
      <tbody>
      <?php foreach ($invoices as $inv):
        $cfg = SUPPLIER_INVOICE_STATUSES[$inv['status']] ?? ['label' => $inv['status'], 'color' => '#6B7280'];
      ?>
      <tr>
        <td style="font-weight:600"><?= htmlspecialchars($inv['invoice_no']) ?></td>
        <td><?= htmlspecialchars($inv['project_title'] ?: '—') ?></td>
        <td style="font-weight:600"><?= number_format($inv['total'],0,',',' ') ?> kr</td>
        <td><span class="badge" style="background:<?= $cfg['color'] ?>14;color:<?= $cfg['color'] ?>;border:1px solid <?= $cfg['color'] ?>33"><?= htmlspecialchars($cfg['label']) ?></span></td>
        <td style="font-size:.8125rem;color:var(--steel)"><?= htmlspecialchars(substr($inv['due_date'] ?? '', 0, 10)) ?></td>
      </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
    <?php else: ?>
    <p style="padding:24px;color:var(--steel);font-size:.875rem">Inga fakturor inskickade ännu.</p>
    <?php endif; ?>
  </div>
</main>
<?php supp_foot(); ?>
