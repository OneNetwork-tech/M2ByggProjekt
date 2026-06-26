<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/layout.php';
$su  = supp_require();
$sid = (int)$su['supplier_id'];

// Accepted jobs for dropdown
$s = db()->prepare("
    SELECT ja.id, p.title AS project_title, ja.project_id
    FROM job_assignments ja JOIN projects p ON p.id=ja.project_id
    WHERE ja.supplier_id=? AND ja.status='accepted'
    ORDER BY p.title
");
$s->execute([$sid]);
$acceptedJobs = $s->fetchAll();

$error = ''; $success = '';
$preselect = (int)($_GET['job'] ?? 0);

// Submit report
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $jobId   = (int)$_POST['job_id'];
    $date    = $_POST['report_date'] ?? '';
    $hours   = (float)($_POST['hours'] ?? 0);
    $amount  = $_POST['amount'] !== '' ? (float)$_POST['amount'] : null;
    $desc    = trim($_POST['description'] ?? '');

    // Validate job belongs to supplier
    $valid = false;
    foreach ($acceptedJobs as $j) { if ($j['id'] === $jobId) { $valid = true; break; } }

    if (!$valid)         $error = 'Ogiltigt uppdrag.';
    elseif (!$date)      $error = 'Välj ett datum.';
    elseif ($hours <= 0) $error = 'Timmar måste vara större än 0.';
    else {
        // get project_id
        $s = db()->prepare("SELECT project_id FROM job_assignments WHERE id=? AND supplier_id=?");
        $s->execute([$jobId, $sid]);
        $row = $s->fetch();
        db()->prepare(
            "INSERT INTO time_reports (supplier_id, job_assignment_id, project_id, report_date, hours, amount, description) VALUES (?,?,?,?,?,?,?)"
        )->execute([$sid, $jobId, $row['project_id'], $date, $hours, $amount, $desc]);

        // Optional attached photo (e.g. completed work, materials used)
        if (!empty($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
            $f = $_FILES['photo'];
            $allowedMime = ['image/jpeg', 'image/png', 'image/webp', 'image/heic'];
            if ($f['size'] <= 15_728_640 && in_array(mime_content_type($f['tmp_name']), $allowedMime)) {
                $dir = dirname(__DIR__) . '/data/portal-uploads/job-photos/';
                if (!is_dir($dir)) mkdir($dir, 0750, true);
                $ext = strtolower(pathinfo($f['name'], PATHINFO_EXTENSION)) ?: 'jpg';
                $stored = 'job' . $jobId . '_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
                move_uploaded_file($f['tmp_name'], $dir . $stored);
                db()->prepare(
                    "INSERT INTO job_photos (job_assignment_id, supplier_id, stored_name, original_name, caption) VALUES (?,?,?,?,?)"
                )->execute([$jobId, $sid, $stored, $f['name'], 'Tidrapport ' . $date]);
            }
        }

        $success = 'Tidrapporten har sparats.';
    }
}

// All reports
$s = db()->prepare("
    SELECT tr.*, p.title AS project_title, ja.id AS job_id
    FROM time_reports tr
    JOIN projects p ON p.id=tr.project_id
    LEFT JOIN job_assignments ja ON ja.id=tr.job_assignment_id
    WHERE tr.supplier_id=?
    ORDER BY tr.report_date DESC
");
$s->execute([$sid]);
$reports = $s->fetchAll();

$totalHours = array_sum(array_column($reports, 'hours'));
$totalUnpaid = array_sum(array_column(array_filter($reports, fn($r) => !$r['paid_at']), 'amount'));

supp_head('Tidrapporter', $su);
supp_nav('/tidrapport.php');
?>
<main class="portal-main">
  <div class="portal-page-title"><h1>Tidrapporter</h1><p>Registrera arbetstid och material.</p></div>

  <?php if (!$acceptedJobs): ?>
  <div class="alert alert--warning">Du har inga aktiva uppdrag att rapportera tid på.</div>
  <?php else: ?>
  <div style="display:grid;grid-template-columns:1fr 1fr;gap:24px;margin-bottom:32px">
    <!-- New report form -->
    <div class="card">
      <div class="card-header"><h3>Ny tidrapport</h3></div>
      <div style="padding:20px">
        <?php if ($error): ?><div class="alert alert--error" style="margin-bottom:16px"><?= e($error) ?></div><?php endif; ?>
        <?php if ($success): ?><div class="alert alert--success" style="margin-bottom:16px"><?= e($success) ?></div><?php endif; ?>
        <form method="post" enctype="multipart/form-data">
          <div class="form-group">
            <label class="form-label">Uppdrag</label>
            <select class="form-control" name="job_id" required>
              <option value="">— Välj uppdrag —</option>
              <?php foreach ($acceptedJobs as $j): ?>
              <option value="<?= $j['id'] ?>" <?= $j['id'] === $preselect ? 'selected' : '' ?>><?= e($j['project_title']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-group">
            <label class="form-label">Datum</label>
            <input class="form-control" type="date" name="report_date" value="<?= date('Y-m-d') ?>" required>
          </div>
          <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
            <div class="form-group">
              <label class="form-label">Timmar</label>
              <input class="form-control" type="number" name="hours" min="0.5" step="0.5" required placeholder="8">
            </div>
            <div class="form-group">
              <label class="form-label">Belopp (kr, valfritt)</label>
              <input class="form-control" type="number" name="amount" min="0" step="1" placeholder="0">
            </div>
          </div>
          <div class="form-group">
            <label class="form-label">Beskrivning</label>
            <textarea class="form-control" name="description" rows="3" placeholder="Vad utfördes?"></textarea>
          </div>
          <div class="form-group">
            <label class="form-label">Bild (valfritt)</label>
            <input class="form-control" type="file" name="photo" accept="image/*" capture="environment" style="padding:8px">
          </div>
          <button type="submit" class="btn btn--primary">Spara rapport</button>
        </form>
      </div>
    </div>

    <!-- Totals -->
    <div>
      <div class="card" style="margin-bottom:16px">
        <div class="card-header"><h3>Sammanfattning</h3></div>
        <div style="padding:20px;display:flex;flex-direction:column;gap:12px">
          <div style="display:flex;justify-content:space-between">
            <span style="color:var(--steel)">Totalt rapporterade timmar</span>
            <strong><?= number_format($totalHours, 1, ',', ' ') ?> h</strong>
          </div>
          <div style="display:flex;justify-content:space-between">
            <span style="color:var(--steel)">Obetalt belopp</span>
            <strong style="color:var(--warning)"><?= number_format((float)$totalUnpaid, 0, ',', ' ') ?> kr</strong>
          </div>
        </div>
      </div>
    </div>
  </div>
  <?php endif; ?>

  <!-- Reports table -->
  <div class="card">
    <div class="card-header"><h3>Alla tidrapporter</h3></div>
    <?php if ($reports): ?>
    <table class="portal-table">
      <thead><tr><th>Datum</th><th>Projekt</th><th>Timmar</th><th>Belopp</th><th>Beskrivning</th><th>Status</th></tr></thead>
      <tbody>
      <?php foreach ($reports as $r): ?>
      <tr>
        <td style="font-size:.875rem;white-space:nowrap"><?= e($r['report_date']) ?></td>
        <td><?= e($r['project_title']) ?></td>
        <td><?= $r['hours'] ? number_format($r['hours'],1,',','') . ' h' : '—' ?></td>
        <td><?= $r['amount'] ? number_format($r['amount'],0,',',' ').' kr' : '—' ?></td>
        <td style="font-size:.8125rem;color:var(--steel);max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap"><?= e($r['description'] ?? '') ?></td>
        <td><span class="badge badge--<?= $r['paid_at'] ? 'success' : 'warning' ?>"><?= $r['paid_at'] ? 'Betald' : 'Obetald' ?></span></td>
      </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
    <?php else: ?>
    <p style="padding:24px;color:var(--steel);font-size:.875rem">Inga tidrapporter ännu.</p>
    <?php endif; ?>
  </div>
</main>
<?php supp_foot(); ?>
