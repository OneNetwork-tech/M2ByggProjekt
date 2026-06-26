<?php
/**
 * CRM — Scheduling & calendar (site visits, meetings, project starts, supplier job starts)
 */
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/mailer.php';
$me = require_login();
$pdo = db();

// ── ACTIONS ──────────────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $action = $_POST['action'] ?? '';

    if ($action === 'create_visit') {
        $pdo->prepare(
            "INSERT INTO site_visits (title, visit_date, visit_time, lead_id, customer_id, project_id, supplier_id, assigned_to, notes, created_by)
             VALUES (?,?,?,?,?,?,?,?,?,?)"
        )->execute([
            trim($_POST['title']), $_POST['visit_date'], $_POST['visit_time'] ?: null,
            $_POST['lead_id'] ?: null, $_POST['customer_id'] ?: null, $_POST['project_id'] ?: null,
            $_POST['supplier_id'] ?: null, $_POST['assigned_to'] ?: null, trim($_POST['notes'] ?? ''), $me['id'],
        ]);
        flash('Bokning skapad.');
        header('Location: kalender.php?month=' . ($_POST['ref_month'] ?? date('Y-m'))); exit;
    }

    if ($action === 'delete_visit') {
        $pdo->prepare("DELETE FROM site_visits WHERE id=?")->execute([(int)$_POST['id']]);
        flash('Bokning borttagen.');
        header('Location: kalender.php?month=' . ($_POST['ref_month'] ?? date('Y-m'))); exit;
    }

    if ($action === 'create_meeting') {
        $contactType = $_POST['contact_type'] ?? 'contact';
        $contactId = $_POST['contact_id'] ?: null;
        $contactName = trim($_POST['contact_name'] ?? '');
        $contactEmail = trim($_POST['contact_email'] ?? '');
        $contactPhone = trim($_POST['contact_phone'] ?? '');

        if ($contactType === 'customer' && $contactId) {
            $c = $pdo->prepare("SELECT name, email, phone FROM customers WHERE id=?");
            $c->execute([$contactId]);
            if ($c = $c->fetch()) { $contactName = $c['name']; $contactEmail = $c['email']; $contactPhone = $c['phone']; }
        } elseif ($contactType === 'supplier' && $contactId) {
            $s = $pdo->prepare("SELECT company, email, phone FROM suppliers WHERE id=?");
            $s->execute([$contactId]);
            if ($s = $s->fetch()) { $contactName = $s['company']; $contactEmail = $s['email']; $contactPhone = $s['phone']; }
        }

        if ($contactName === '') {
            flash('Kontaktnamn krävs.', 'error');
        } else {
            $pdo->prepare(
                "INSERT INTO meetings (title, contact_type, contact_id, contact_name, contact_email, contact_phone, location, meeting_date, start_time, end_time, notes, created_by)
                 VALUES (?,?,?,?,?,?,?,?,?,?,?,?)"
            )->execute([
                trim($_POST['title']), $contactType, $contactId ?: null, $contactName, $contactEmail, $contactPhone,
                trim($_POST['location'] ?? ''), $_POST['meeting_date'], $_POST['start_time'] ?: null, $_POST['end_time'] ?: null,
                trim($_POST['notes'] ?? ''), $me['id'],
            ]);
            $meetingId = $pdo->lastInsertId();
            audit('meeting_create', 'meeting', $meetingId);

            if ($contactEmail && filter_var($contactEmail, FILTER_VALIDATE_EMAIL)) {
                $when = date('j M Y', strtotime($_POST['meeting_date'])) . ($_POST['start_time'] ? ' kl. ' . $_POST['start_time'] : '');
                $subject = 'Mötesbokning: ' . trim($_POST['title']);
                $body = "<p>Hej " . htmlspecialchars($contactName, ENT_QUOTES, 'UTF-8') . ",</p>"
                      . "<p>Vi har bokat in ett möte:</p>"
                      . "<p><strong>" . htmlspecialchars(trim($_POST['title']), ENT_QUOTES, 'UTF-8') . "</strong><br>"
                      . htmlspecialchars($when, ENT_QUOTES, 'UTF-8')
                      . ($_POST['location'] ? '<br>Plats: ' . htmlspecialchars(trim($_POST['location']), ENT_QUOTES, 'UTF-8') : '') . "</p>"
                      . ($_POST['notes'] ? '<p>' . nl2br(htmlspecialchars(trim($_POST['notes']), ENT_QUOTES, 'UTF-8')) . '</p>' : '')
                      . "<p>Vid frågor, kontakta oss på 031-96 88 88.</p>";
                $ok = crm_send_mail($contactEmail, $contactName, $subject, $body, 'meeting', $meetingId);
                $pdo->prepare("INSERT INTO emails (to_email, to_name, entity_type, entity_id, subject, body, status, sent_by) VALUES (?,?,?,?,?,?,?,?)")
                    ->execute([$contactEmail, $contactName, 'meeting', $meetingId, $subject, strip_tags($body), $ok ? 'sent' : 'failed', $me['id']]);
            }

            flash('Möte bokat' . ($contactEmail ? ' och bekräftelse skickad.' : '.'));
        }
        header('Location: kalender.php?month=' . ($_POST['ref_month'] ?? date('Y-m'))); exit;
    }

    if ($action === 'delete_meeting') {
        $pdo->prepare("DELETE FROM meetings WHERE id=?")->execute([(int)$_POST['id']]);
        flash('Möte borttaget.');
        header('Location: kalender.php?month=' . ($_POST['ref_month'] ?? date('Y-m'))); exit;
    }
}

// ── MONTH NAVIGATION ─────────────────────────────────────────────────────────
$monthParam = $_GET['month'] ?? date('Y-m');
if (!preg_match('/^\d{4}-\d{2}$/', $monthParam)) $monthParam = date('Y-m');
$refDate   = DateTime::createFromFormat('Y-m-d', $monthParam . '-01');
$year      = (int)$refDate->format('Y');
$month     = (int)$refDate->format('n');
$firstDay  = new DateTime("$year-$month-01");
$daysInMonth = (int)$firstDay->format('t');
$startWeekday = (int)$firstDay->format('N'); // 1=Mon..7=Sun
$prevMonth = (clone $firstDay)->modify('-1 month')->format('Y-m');
$nextMonth = (clone $firstDay)->modify('+1 month')->format('Y-m');
$monthNames = ['','Januari','Februari','Mars','April','Maj','Juni','Juli','Augusti','September','Oktober','November','December'];

$rangeStart = $firstDay->format('Y-m-d');
$rangeEnd   = (clone $firstDay)->modify('+1 month')->format('Y-m-d');

// ── DATA SOURCES FOR THE MONTH ────────────────────────────────────────────────
// Site visits
$visits = $pdo->prepare("
    SELECT sv.*, l.name AS lead_name, c.name AS customer_name, p.title AS project_title, s.company AS supplier_company, u.name AS assigned_name
    FROM site_visits sv
    LEFT JOIN leads l ON l.id = sv.lead_id
    LEFT JOIN customers c ON c.id = sv.customer_id
    LEFT JOIN projects p ON p.id = sv.project_id
    LEFT JOIN suppliers s ON s.id = sv.supplier_id
    LEFT JOIN users u ON u.id = sv.assigned_to
    WHERE sv.visit_date >= ? AND sv.visit_date < ?
    ORDER BY sv.visit_date, sv.visit_time
");
$visits->execute([$rangeStart, $rangeEnd]);
$visits = $visits->fetchAll();

// Project starts
$projStarts = $pdo->prepare("
    SELECT p.id, p.title, p.start_date, c.name AS customer_name
    FROM projects p LEFT JOIN customers c ON c.id = p.customer_id
    WHERE p.start_date >= ? AND p.start_date < ?
");
$projStarts->execute([$rangeStart, $rangeEnd]);
$projStarts = $projStarts->fetchAll();

// Supplier job assignment starts
$jobStarts = $pdo->prepare("
    SELECT ja.id, ja.start_date, s.company AS supplier_company, p.title AS project_title, ja.status
    FROM job_assignments ja
    JOIN suppliers s ON s.id = ja.supplier_id
    JOIN projects p ON p.id = ja.project_id
    WHERE ja.start_date >= ? AND ja.start_date < ?
");
$jobStarts->execute([$rangeStart, $rangeEnd]);
$jobStarts = $jobStarts->fetchAll();

// Meetings
$meetings = $pdo->prepare("
    SELECT m.*, u.name AS created_by_name
    FROM meetings m LEFT JOIN users u ON u.id = m.created_by
    WHERE m.meeting_date >= ? AND m.meeting_date < ?
    ORDER BY m.meeting_date, m.start_time
");
$meetings->execute([$rangeStart, $rangeEnd]);
$meetings = $meetings->fetchAll();

// Build day => events map
$dayEvents = [];
foreach ($visits as $v) {
    $d = substr($v['visit_date'], 0, 10);
    $dayEvents[$d][] = ['type' => 'visit', 'color' => '#0066FF', 'label' => $v['title'], 'data' => $v];
}
foreach ($projStarts as $p) {
    $d = substr($p['start_date'], 0, 10);
    $dayEvents[$d][] = ['type' => 'project', 'color' => '#059669', 'label' => 'Start: ' . $p['title'], 'data' => $p];
}
foreach ($jobStarts as $j) {
    $d = substr($j['start_date'], 0, 10);
    $dayEvents[$d][] = ['type' => 'job', 'color' => '#7C3AED', 'label' => $j['supplier_company'] . ' — ' . $j['project_title'], 'data' => $j];
}
foreach ($meetings as $m) {
    $d = substr($m['meeting_date'], 0, 10);
    $dayEvents[$d][] = ['type' => 'meeting', 'color' => '#DB2777', 'label' => 'Möte: ' . $m['title'] . ' (' . $m['contact_name'] . ')', 'data' => $m];
}

// Conflict detection: flag days where a supplier has 2+ events (visit + job start combined)
$supplierDayCount = [];
foreach ($visits as $v) {
    if ($v['supplier_id']) {
        $d = substr($v['visit_date'], 0, 10);
        $supplierDayCount[$d][$v['supplier_id']] = ($supplierDayCount[$d][$v['supplier_id']] ?? 0) + 1;
    }
}
$conflictDays = [];
foreach ($supplierDayCount as $d => $bySupplier) {
    foreach ($bySupplier as $count) {
        if ($count > 1) { $conflictDays[$d] = true; break; }
    }
}

// Dropdowns for booking form
$leads     = $pdo->query("SELECT id, name FROM leads WHERE stage NOT IN ('won','lost') ORDER BY created_at DESC LIMIT 100")->fetchAll();
$customers = $pdo->query("SELECT id, name FROM customers ORDER BY name")->fetchAll();
$projects  = $pdo->query("SELECT id, title FROM projects WHERE status NOT IN ('completed','closed') ORDER BY created_at DESC")->fetchAll();
$suppliers = $pdo->query("SELECT id, company FROM suppliers WHERE status IN ('verified','active') ORDER BY company")->fetchAll();
$staff     = $pdo->query("SELECT id, name FROM users WHERE active=1 ORDER BY name")->fetchAll();

$today = date('Y-m-d');

$crm_title = 'Kalender';
$crm_page  = 'kalender';
require_once __DIR__ . '/includes/crm-header.php';
?>
<div class="topbar">
  <div>
    <h1>Kalender</h1>
    <div class="topbar__sub">Platsbesök, projektstarter och leverantörsuppdrag</div>
  </div>
  <div class="topbar__actions">
    <button class="btn btn--ghost" onclick="document.getElementById('meetingDate').value='<?= e($today) ?>';openModal('meetingModal')">+ Nytt möte</button>
    <button class="btn btn--primary" onclick="document.getElementById('visitDate').value='<?= e($today) ?>';openModal('visitModal')">+ Boka besök</button>
  </div>
</div>

<?php flash(); ?>

<?php if (!empty($_GET['lead']) || !empty($_GET['project'])): ?>
<script>
document.addEventListener('DOMContentLoaded', function () {
  document.getElementById('visitDate').value = '<?= e($today) ?>';
  <?php if (!empty($_GET['lead'])): ?>document.querySelector('select[name="lead_id"]').value = '<?= (int)$_GET['lead'] ?>';<?php endif; ?>
  <?php if (!empty($_GET['project'])): ?>document.querySelector('select[name="project_id"]').value = '<?= (int)$_GET['project'] ?>';<?php endif; ?>
  openModal('visitModal');
});
</script>
<?php endif; ?>

<!-- Legend + nav -->
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:14px">
  <div style="display:flex;gap:16px;font-size:12px;color:var(--gray)">
    <span><span style="display:inline-block;width:9px;height:9px;background:#0066FF;border-radius:50%;margin-right:5px"></span>Platsbesök</span>
    <span><span style="display:inline-block;width:9px;height:9px;background:#059669;border-radius:50%;margin-right:5px"></span>Projektstart</span>
    <span><span style="display:inline-block;width:9px;height:9px;background:#7C3AED;border-radius:50%;margin-right:5px"></span>Leverantörsstart</span>
    <span><span style="display:inline-block;width:9px;height:9px;background:#DB2777;border-radius:50%;margin-right:5px"></span>Möte</span>
  </div>
  <div style="display:flex;align-items:center;gap:10px">
    <a href="?month=<?= $prevMonth ?>" class="btn btn--ghost btn--sm">← Föreg.</a>
    <strong style="font-size:14px;min-width:140px;text-align:center"><?= $monthNames[$month] ?> <?= $year ?></strong>
    <a href="?month=<?= $nextMonth ?>" class="btn btn--ghost btn--sm">Nästa →</a>
  </div>
</div>

<!-- Calendar grid -->
<div class="card" style="overflow:hidden">
  <div style="display:grid;grid-template-columns:repeat(7,1fr);background:#F9FAFB;border-bottom:1px solid var(--border)">
    <?php foreach (['Mån','Tis','Ons','Tor','Fre','Lör','Sön'] as $wd): ?>
    <div style="padding:8px;text-align:center;font-size:11px;font-weight:600;color:var(--gray)"><?= $wd ?></div>
    <?php endforeach; ?>
  </div>
  <div style="display:grid;grid-template-columns:repeat(7,1fr)">
    <?php
    // Leading blanks
    for ($i = 1; $i < $startWeekday; $i++): ?>
    <div style="min-height:110px;border:1px solid #F3F4F6;background:#FAFAFA"></div>
    <?php endfor;

    for ($day = 1; $day <= $daysInMonth; $day++):
        $dateStr = sprintf('%04d-%02d-%02d', $year, $month, $day);
        $isToday = $dateStr === $today;
        $events = $dayEvents[$dateStr] ?? [];
    ?>
    <div style="min-height:110px;border:1px solid #F3F4F6;padding:6px;<?= $isToday ? 'background:#EFF6FF' : '' ?>;position:relative">
      <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:4px">
        <span style="font-size:12px;font-weight:<?= $isToday ? '700' : '550' ?>;color:<?= $isToday ? 'var(--blue)' : 'var(--ink)' ?>">
          <?= $day ?>
          <?php if (!empty($conflictDays[$dateStr])): ?><span title="Schemakonflikt: en leverantör är dubbelbokad" style="color:var(--amber)">⚠</span><?php endif; ?>
        </span>
        <button type="button" onclick="document.getElementById('visitDate').value='<?= $dateStr ?>';openModal('visitModal')" style="border:none;background:none;color:var(--gray-lt);cursor:pointer;font-size:13px;padding:0 4px" title="Boka besök">+</button>
      </div>
      <?php foreach (array_slice($events, 0, 3) as $ev): ?>
      <div style="font-size:10.5px;padding:2px 5px;margin-bottom:2px;border-radius:4px;background:<?= $ev['color'] ?>14;color:<?= $ev['color'] ?>;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;cursor:default"
           title="<?= e($ev['label']) ?>">
        <?= e(mb_strimwidth($ev['label'], 0, 22, '…')) ?>
      </div>
      <?php endforeach; ?>
      <?php if (count($events) > 3): ?>
      <div style="font-size:10px;color:var(--gray-lt)">+<?= count($events) - 3 ?> till</div>
      <?php endif; ?>
    </div>
    <?php endfor;

    // Trailing blanks to complete the grid
    $totalCells = $startWeekday - 1 + $daysInMonth;
    $trailing = (7 - ($totalCells % 7)) % 7;
    for ($i = 0; $i < $trailing; $i++): ?>
    <div style="min-height:110px;border:1px solid #F3F4F6;background:#FAFAFA"></div>
    <?php endfor; ?>
  </div>
</div>

<!-- Upcoming list (next 14 days) -->
<div class="card card--pad" style="margin-top:20px">
  <h3 style="font-size:14.5px;margin-bottom:14px">Kommande bokade besök</h3>
  <?php
  $upcoming = $pdo->prepare("
      SELECT sv.*, l.name AS lead_name, c.name AS customer_name, p.title AS project_title, s.company AS supplier_company, u.name AS assigned_name
      FROM site_visits sv
      LEFT JOIN leads l ON l.id = sv.lead_id
      LEFT JOIN customers c ON c.id = sv.customer_id
      LEFT JOIN projects p ON p.id = sv.project_id
      LEFT JOIN suppliers s ON s.id = sv.supplier_id
      LEFT JOIN users u ON u.id = sv.assigned_to
      WHERE sv.visit_date >= date('now') AND sv.visit_date < date('now','+14 days')
      ORDER BY sv.visit_date, sv.visit_time LIMIT 20
  ")->fetchAll();
  ?>
  <?php if ($upcoming): ?>
  <table class="data">
    <thead><tr><th>Datum</th><th>Tid</th><th>Titel</th><th>Relaterat</th><th>Ansvarig</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($upcoming as $u): ?>
    <tr style="cursor:default">
      <td style="font-size:12.5px;white-space:nowrap"><?= dt($u['visit_date']) ?></td>
      <td style="font-size:12.5px"><?= e($u['visit_time'] ?: '—') ?></td>
      <td style="font-weight:550;font-size:13px"><?= e($u['title']) ?></td>
      <td style="font-size:12px;color:var(--gray)">
        <?= e($u['customer_name'] ?: $u['lead_name'] ?: '—') ?>
        <?= $u['project_title'] ? ' · '.e($u['project_title']) : '' ?>
        <?= $u['supplier_company'] ? ' · '.e($u['supplier_company']) : '' ?>
      </td>
      <td style="font-size:12px;color:var(--gray)"><?= e($u['assigned_name'] ?: '—') ?></td>
      <td>
        <form method="POST" onsubmit="return confirm('Ta bort bokningen?')">
          <?= csrf_field() ?><input type="hidden" name="action" value="delete_visit">
          <input type="hidden" name="id" value="<?= $u['id'] ?>">
          <input type="hidden" name="ref_month" value="<?= $monthParam ?>">
          <button class="btn btn--ghost btn--sm" style="color:var(--red)">✕</button>
        </form>
      </td>
    </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
  <?php else: ?>
  <p style="font-size:13px;color:var(--gray)">Inga bokade besök de kommande 14 dagarna.</p>
  <?php endif; ?>
</div>

<!-- Upcoming meetings list (next 14 days) -->
<div class="card card--pad" style="margin-top:20px">
  <h3 style="font-size:14.5px;margin-bottom:14px">Kommande möten</h3>
  <?php
  $upcomingMeetings = $pdo->query("
      SELECT m.*, u.name AS created_by_name
      FROM meetings m LEFT JOIN users u ON u.id = m.created_by
      WHERE m.meeting_date >= date('now') AND m.meeting_date < date('now','+14 days')
      ORDER BY m.meeting_date, m.start_time LIMIT 20
  ")->fetchAll();
  $contactTypeLabels = ['customer' => 'Kund', 'supplier' => 'Leverantör', 'contact' => 'Kontakt'];
  ?>
  <?php if ($upcomingMeetings): ?>
  <table class="data">
    <thead><tr><th>Datum</th><th>Tid</th><th>Titel</th><th>Kontakt</th><th>Plats</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($upcomingMeetings as $m): ?>
    <tr style="cursor:default">
      <td style="font-size:12.5px;white-space:nowrap"><?= dt($m['meeting_date']) ?></td>
      <td style="font-size:12.5px"><?= e($m['start_time'] ?: '—') ?><?= $m['end_time'] ? '–'.e($m['end_time']) : '' ?></td>
      <td style="font-weight:550;font-size:13px"><?= e($m['title']) ?></td>
      <td style="font-size:12px;color:var(--gray)">
        <?= e($m['contact_name']) ?>
        <span class="badge" style="background:#FCE7F3;color:#DB2777;font-size:10px;margin-left:6px"><?= $contactTypeLabels[$m['contact_type']] ?? $m['contact_type'] ?></span>
      </td>
      <td style="font-size:12px;color:var(--gray)"><?= e($m['location'] ?: '—') ?></td>
      <td>
        <form method="POST" onsubmit="return confirm('Ta bort mötet?')">
          <?= csrf_field() ?><input type="hidden" name="action" value="delete_meeting">
          <input type="hidden" name="id" value="<?= $m['id'] ?>">
          <input type="hidden" name="ref_month" value="<?= $monthParam ?>">
          <button class="btn btn--ghost btn--sm" style="color:var(--red)">✕</button>
        </form>
      </td>
    </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
  <?php else: ?>
  <p style="font-size:13px;color:var(--gray)">Inga bokade möten de kommande 14 dagarna.</p>
  <?php endif; ?>
</div>

<!-- BOOKING MODAL -->
<div class="modal-bg" id="visitModal">
  <div class="modal">
    <h3>Boka platsbesök</h3>
    <form method="POST">
      <?= csrf_field() ?><input type="hidden" name="action" value="create_visit">
      <input type="hidden" name="ref_month" value="<?= $monthParam ?>">
      <div class="fg"><label>Titel</label><input class="fi" name="title" required placeholder="T.ex. Besiktning villa Hisings Backa"></div>
      <div class="frow">
        <div class="fg"><label>Datum</label><input class="fi" type="date" id="visitDate" name="visit_date" required></div>
        <div class="fg"><label>Tid</label><input class="fi" type="time" name="visit_time"></div>
      </div>
      <div class="fg"><label>Lead (valfritt)</label>
        <select class="fs" name="lead_id"><option value="">—</option>
          <?php foreach ($leads as $l): ?><option value="<?= $l['id'] ?>"><?= e($l['name']) ?></option><?php endforeach; ?>
        </select>
      </div>
      <div class="fg"><label>Kund (valfritt)</label>
        <select class="fs" name="customer_id"><option value="">—</option>
          <?php foreach ($customers as $c): ?><option value="<?= $c['id'] ?>"><?= e($c['name']) ?></option><?php endforeach; ?>
        </select>
      </div>
      <div class="fg"><label>Projekt (valfritt)</label>
        <select class="fs" name="project_id"><option value="">—</option>
          <?php foreach ($projects as $p): ?><option value="<?= $p['id'] ?>"><?= e($p['title']) ?></option><?php endforeach; ?>
        </select>
      </div>
      <div class="frow">
        <div class="fg"><label>Leverantör (valfritt)</label>
          <select class="fs" name="supplier_id"><option value="">—</option>
            <?php foreach ($suppliers as $s): ?><option value="<?= $s['id'] ?>"><?= e($s['company']) ?></option><?php endforeach; ?>
          </select>
        </div>
        <div class="fg"><label>Ansvarig</label>
          <select class="fs" name="assigned_to"><option value="">—</option>
            <?php foreach ($staff as $u): ?><option value="<?= $u['id'] ?>" <?= $u['id']===$me['id']?'selected':'' ?>><?= e($u['name']) ?></option><?php endforeach; ?>
          </select>
        </div>
      </div>
      <div class="fg"><label>Anteckningar</label><textarea class="fta" name="notes" rows="2"></textarea></div>
      <div style="display:flex;gap:10px;justify-content:flex-end">
        <button type="button" class="btn btn--ghost" onclick="closeModal('visitModal')">Avbryt</button>
        <button class="btn btn--primary">Boka</button>
      </div>
    </form>
  </div>
</div>

<!-- MEETING MODAL -->
<div class="modal-bg" id="meetingModal">
  <div class="modal">
    <h3>Nytt möte</h3>
    <form method="POST">
      <?= csrf_field() ?><input type="hidden" name="action" value="create_meeting">
      <input type="hidden" name="ref_month" value="<?= $monthParam ?>">
      <div class="fg"><label>Titel</label><input class="fi" name="title" required placeholder="T.ex. Avstämningsmöte"></div>
      <div class="fg"><label>Kontakttyp</label>
        <select class="fs" name="contact_type" id="meetingContactType" onchange="toggleMeetingContact(this.value)">
          <option value="customer">Kund</option>
          <option value="supplier">Leverantör</option>
          <option value="contact" selected>Annan kontakt</option>
        </select>
      </div>
      <div class="fg" id="meetingCustomerField" style="display:none">
        <label>Kund</label>
        <select class="fs" name="contact_id_customer" onchange="document.getElementById('meetingContactIdHidden').value=this.value">
          <option value="">— Välj kund —</option>
          <?php foreach ($customers as $c): ?><option value="<?= $c['id'] ?>"><?= e($c['name']) ?></option><?php endforeach; ?>
        </select>
      </div>
      <div class="fg" id="meetingSupplierField" style="display:none">
        <label>Leverantör</label>
        <select class="fs" name="contact_id_supplier" onchange="document.getElementById('meetingContactIdHidden').value=this.value">
          <option value="">— Välj leverantör —</option>
          <?php foreach ($suppliers as $s): ?><option value="<?= $s['id'] ?>"><?= e($s['company']) ?></option><?php endforeach; ?>
        </select>
      </div>
      <input type="hidden" name="contact_id" id="meetingContactIdHidden" value="">
      <div class="fg" id="meetingContactNameField">
        <label>Kontaktnamn</label>
        <input class="fi" name="contact_name" id="meetingContactName" placeholder="För- och efternamn">
      </div>
      <div class="frow" id="meetingContactDetailsField">
        <div class="fg"><label>E-post (skickar mötesbekräftelse)</label><input class="fi" type="email" name="contact_email" id="meetingContactEmail"></div>
        <div class="fg"><label>Telefon</label><input class="fi" name="contact_phone" id="meetingContactPhone"></div>
      </div>
      <div class="frow">
        <div class="fg"><label>Datum</label><input class="fi" type="date" id="meetingDate" name="meeting_date" required></div>
        <div class="fg"><label>Starttid</label><input class="fi" type="time" name="start_time"></div>
        <div class="fg"><label>Sluttid</label><input class="fi" type="time" name="end_time"></div>
      </div>
      <div class="fg"><label>Plats</label><input class="fi" name="location" placeholder="T.ex. Kontoret, Lillhagsvägen 88 / Videosamtal"></div>
      <div class="fg"><label>Anteckningar</label><textarea class="fta" name="notes" rows="2"></textarea></div>
      <div style="display:flex;gap:10px;justify-content:flex-end">
        <button type="button" class="btn btn--ghost" onclick="closeModal('meetingModal')">Avbryt</button>
        <button class="btn btn--primary">Boka möte</button>
      </div>
    </form>
  </div>
</div>

<script>
function toggleMeetingContact(type) {
  document.getElementById('meetingCustomerField').style.display = type === 'customer' ? '' : 'none';
  document.getElementById('meetingSupplierField').style.display = type === 'supplier' ? '' : 'none';
  const manual = type === 'contact';
  document.getElementById('meetingContactNameField').style.display = manual ? '' : 'none';
  document.getElementById('meetingContactDetailsField').style.display = manual ? '' : 'none';
  document.getElementById('meetingContactIdHidden').value = '';
}
// Initialize default view (contact) on load
toggleMeetingContact('contact');
</script>

<?php require_once __DIR__ . '/includes/crm-footer.php'; ?>
