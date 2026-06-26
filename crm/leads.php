<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/helpers.php';
$me = require_role(['sales','support','project']);

$pdo = db();

// CREATE LEAD
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'create') {
    csrf_check();
    $no = next_number('L', 'leads', 'lead_no');
    $stmt = $pdo->prepare("INSERT INTO leads (lead_no,name,email,phone,address,city,service,sub_service,source,message,value_estimate,assigned_to) VALUES (?,?,?,?,?,?,?,?,?,?,?,?)");
    $stmt->execute([
        $no, trim($_POST['name']), trim($_POST['email'] ?? ''), trim($_POST['phone'] ?? ''),
        trim($_POST['address'] ?? ''), trim($_POST['city'] ?? ''),
        $_POST['service'] ?? '', trim($_POST['sub_service'] ?? ''),
        $_POST['source'] ?? 'Manuell', trim($_POST['message'] ?? ''),
        (float)($_POST['value_estimate'] ?? 0), $me['id'],
    ]);
    $id = $pdo->lastInsertId();
    log_timeline('lead', $id, 'system', 'Lead skapad', 'Skapad manuellt av ' . $me['name'], $me['id']);
    audit('lead_create', 'lead', $id);
    flash("Lead $no skapad.");
    header("Location: lead.php?id=$id"); exit;
}

$view = $_GET['view'] ?? 'kanban';
$q    = trim($_GET['q'] ?? '');

$where = '1=1'; $params = [];
if ($q) { $where .= " AND (name LIKE ? OR phone LIKE ? OR email LIKE ? OR city LIKE ? OR lead_no LIKE ?)"; $params = array_fill(0, 5, "%$q%"); }

$stmt = $pdo->prepare("SELECT * FROM leads WHERE $where ORDER BY created_at DESC");
$stmt->execute($params);
$leads = $stmt->fetchAll();

$byStage = [];
foreach (LEAD_STAGES as $k => $_) $byStage[$k] = [];
foreach ($leads as $l) $byStage[$l['stage']][] = $l;

$crm_title = 'Leads';
$crm_page  = 'leads';
require_once __DIR__ . '/includes/crm-header.php';
?>

<div class="topbar">
  <div>
    <h1>Leads</h1>
    <div class="topbar__sub"><?= count($leads) ?> leads · <?= count($byStage['new']) ?> nya</div>
  </div>
  <div class="topbar__actions">
    <form method="GET" class="search-box">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
      <input name="q" placeholder="Sök lead..." value="<?= e($q) ?>">
      <input type="hidden" name="view" value="<?= e($view) ?>">
    </form>
    <a href="?view=<?= $view === 'kanban' ? 'list' : 'kanban' ?>" class="btn btn--ghost btn--sm"><?= $view === 'kanban' ? 'Listvy' : 'Kanban' ?></a>
    <button class="btn btn--primary" onclick="openModal('newLeadModal')">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
      Ny lead
    </button>
  </div>
</div>

<?php flash(); ?>

<?php if ($view === 'kanban'): ?>
<!-- KANBAN -->
<div class="kanban">
  <?php foreach (LEAD_STAGES as $stage => $cfg): ?>
  <div class="kanban__col" data-stage="<?= $stage ?>">
    <div class="kanban__head">
      <span class="kanban__title">
        <span class="kanban__dot" style="background:<?= $cfg['color'] ?>"></span>
        <?= e($cfg['label']) ?>
      </span>
      <span class="kanban__count"><?= count($byStage[$stage]) ?></span>
    </div>
    <div class="kanban__cards">
      <?php foreach ($byStage[$stage] as $l): ?>
      <div class="kanban__card" draggable="true" data-id="<?= $l['id'] ?>" onclick="location.href='lead.php?id=<?= $l['id'] ?>'">
        <div class="kanban__card-title"><?= e($l['name']) ?></div>
        <div class="kanban__card-sub"><?= e($l['service'] ?: 'Ingen tjänst') ?><?= $l['city'] ? ' · ' . e($l['city']) : '' ?></div>
        <div class="kanban__card-meta">
          <span><?= e($l['lead_no']) ?></span>
          <span><?= time_ago($l['created_at']) ?></span>
        </div>
        <?php if ($l['value_estimate'] > 0): ?>
        <div style="margin-top:7px;font-size:12px;font-weight:600;color:var(--blue)"><?= money($l['value_estimate']) ?></div>
        <?php endif; ?>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
  <?php endforeach; ?>
</div>

<?php else: ?>
<!-- LIST -->
<div class="card">
  <div class="table-wrap">
    <table class="data">
      <thead><tr><th>Nr</th><th>Namn</th><th>Kontakt</th><th>Tjänst</th><th>Stad</th><th>Källa</th><th>Status</th><th>Inkom</th></tr></thead>
      <tbody>
        <?php foreach ($leads as $l): ?>
        <tr data-href="lead.php?id=<?= $l['id'] ?>">
          <td style="font-size:12px;color:var(--gray-lt)"><?= e($l['lead_no']) ?></td>
          <td style="font-weight:550"><?= e($l['name']) ?></td>
          <td style="font-size:12.5px;color:var(--gray)"><?= e($l['phone'] ?: $l['email'] ?: '–') ?></td>
          <td><?= e($l['service'] ?: '–') ?></td>
          <td><?= e($l['city'] ?: '–') ?></td>
          <td style="font-size:12.5px;color:var(--gray)"><?= e($l['source']) ?></td>
          <td><?= badge($l['stage'], LEAD_STAGES) ?></td>
          <td style="font-size:12.5px;color:var(--gray)"><?= time_ago($l['created_at']) ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php endif; ?>

<!-- NEW LEAD MODAL -->
<div class="modal-bg <?= isset($_GET['new']) ? 'open' : '' ?>" id="newLeadModal">
  <div class="modal">
    <h3>Ny lead</h3>
    <form method="POST">
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="create">
      <div class="frow">
        <div class="fg"><label>Namn *</label><input class="fi" name="name" required placeholder="Anna Svensson"></div>
        <div class="fg"><label>Telefon</label><input class="fi" name="phone" placeholder="07X-XXX XX XX"></div>
      </div>
      <div class="fg"><label>E-post</label><input class="fi" type="email" name="email" placeholder="anna@email.se"></div>
      <div class="frow">
        <div class="fg"><label>Tjänst</label>
          <select class="fs" name="service">
            <option value="">Välj...</option>
            <?php foreach (SERVICES as $s): ?><option><?= e($s) ?></option><?php endforeach; ?>
          </select>
        </div>
        <div class="fg"><label>Stad</label><input class="fi" name="city" placeholder="Göteborg"></div>
      </div>
      <div class="fg"><label>Adress</label><input class="fi" name="address" placeholder="Gatuadress"></div>
      <div class="frow">
        <div class="fg"><label>Källa</label>
          <select class="fs" name="source">
            <option>Manuell</option><option>Telefon</option><option>Webbformulär</option>
            <option>Rekommendation</option><option>Google</option><option>Sociala medier</option>
          </select>
        </div>
        <div class="fg"><label>Uppskattat värde (kr)</label><input class="fi" type="number" name="value_estimate" placeholder="0"></div>
      </div>
      <div class="fg"><label>Anteckning</label><textarea class="fta" name="message" placeholder="Beskrivning av förfrågan..."></textarea></div>
      <div style="display:flex;gap:10px;justify-content:flex-end">
        <button type="button" class="btn btn--ghost" onclick="closeModal('newLeadModal')">Avbryt</button>
        <button type="submit" class="btn btn--primary">Skapa lead</button>
      </div>
    </form>
  </div>
</div>

<?php require_once __DIR__ . '/includes/crm-footer.php'; ?>
