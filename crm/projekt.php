<?php
$crm_title = 'Projekt';
$crm_page  = 'projekt';
require_once __DIR__ . '/includes/crm-header.php';
require_role(['project','sales']);
$pdo = db();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'create') {
    csrf_check();
    $no = next_number('P', 'projects', 'project_no');
    $pdo->prepare("INSERT INTO projects (project_no, customer_id, title, address, city, status, budget, start_date, manager_id, next_step) VALUES (?,?,?,?,?,?,?,?,?,?)")
        ->execute([$no, $_POST['customer_id'] ?: null, trim($_POST['title']), trim($_POST['address'] ?? ''), trim($_POST['city'] ?? ''),
                   $_POST['status'] ?? 'planning', (float)($_POST['budget'] ?? 0), $_POST['start_date'] ?: null, $me['id'], trim($_POST['next_step'] ?? '')]);
    $pid = $pdo->lastInsertId();
    log_timeline('project', $pid, 'system', 'Projekt skapat', '', $me['id']);
    audit('project_create', 'project', $pid);
    flash("Projekt $no skapat.");
    header("Location: projekt-detalj.php?id=$pid"); exit;
}

$filter = $_GET['status'] ?? 'active';
if ($filter === 'active')      $where = "WHERE p.status IN ('inspection','planning','scheduled','in_progress','quality')";
elseif ($filter === 'done')    $where = "WHERE p.status IN ('completed','closed')";
elseif ($filter === 'all')     $where = '';
else                           $where = "WHERE p.status = " . $pdo->quote($filter);

$projects = $pdo->query("SELECT p.*, c.name AS customer_name FROM projects p LEFT JOIN customers c ON c.id=p.customer_id $where ORDER BY p.created_at DESC")->fetchAll();
$customers = $pdo->query("SELECT id, name FROM customers ORDER BY name")->fetchAll();
?>

<div class="topbar">
  <div>
    <h1>Projekt</h1>
    <div class="topbar__sub"><?= count($projects) ?> projekt visas</div>
  </div>
  <div class="topbar__actions">
    <button class="btn btn--primary" onclick="openModal('newProjModal')">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
      Nytt projekt
    </button>
  </div>
</div>

<?php flash(); ?>

<div class="tabs">
  <a href="?status=active" class="tab <?= $filter==='active'?'active':'' ?>">Aktiva</a>
  <a href="?status=done" class="tab <?= $filter==='done'?'active':'' ?>">Slutförda</a>
  <a href="?status=all" class="tab <?= $filter==='all'?'active':'' ?>">Alla</a>
</div>

<div class="card">
  <div class="table-wrap">
    <table class="data">
      <thead><tr><th>Projekt</th><th>Kund</th><th>Adress</th><th>Status</th><th>Förlopp</th><th>Budget</th><th>Start</th></tr></thead>
      <tbody>
        <?php if (!$projects): ?>
        <tr><td colspan="7" style="text-align:center;color:var(--gray);padding:36px">Inga projekt. Projekt skapas automatiskt vid offertacceptans, eller manuellt.</td></tr>
        <?php endif; ?>
        <?php foreach ($projects as $p): ?>
        <tr data-href="projekt-detalj.php?id=<?= $p['id'] ?>">
          <td><div style="font-weight:550"><?= e($p['title']) ?></div><div style="font-size:11.5px;color:var(--gray-lt)"><?= e($p['project_no']) ?></div></td>
          <td><?= e($p['customer_name'] ?: '–') ?></td>
          <td style="font-size:12.5px;color:var(--gray)"><?= e($p['address'] ?: '–') ?><?= $p['city'] ? ', ' . e($p['city']) : '' ?></td>
          <td><?= badge($p['status'], PROJECT_STATUSES) ?></td>
          <td style="min-width:130px">
            <div style="display:flex;align-items:center;gap:8px">
              <div class="progress" style="flex:1"><div class="progress__bar" style="width:<?= (int)$p['progress'] ?>%"></div></div>
              <span style="font-size:11.5px;color:var(--gray);font-weight:550"><?= (int)$p['progress'] ?>%</span>
            </div>
          </td>
          <td style="font-weight:550"><?= money($p['budget']) ?></td>
          <td style="font-size:12.5px;color:var(--gray)"><?= dt($p['start_date']) ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<div class="modal-bg" id="newProjModal">
  <div class="modal">
    <h3>Nytt projekt</h3>
    <form method="POST">
      <?= csrf_field() ?><input type="hidden" name="action" value="create">
      <div class="fg"><label>Titel *</label><input class="fi" name="title" required placeholder="T.ex. Takbyte – Villagatan 12"></div>
      <div class="fg"><label>Kund</label>
        <select class="fs" name="customer_id"><option value="">– Välj –</option>
          <?php foreach ($customers as $c): ?><option value="<?= $c['id'] ?>"><?= e($c['name']) ?></option><?php endforeach; ?>
        </select>
      </div>
      <div class="frow">
        <div class="fg"><label>Adress</label><input class="fi" name="address"></div>
        <div class="fg"><label>Stad</label><input class="fi" name="city"></div>
      </div>
      <div class="frow">
        <div class="fg"><label>Budget (kr)</label><input class="fi" type="number" name="budget" value="0"></div>
        <div class="fg"><label>Startdatum</label><input class="fi" type="date" name="start_date"></div>
      </div>
      <div class="fg"><label>Status</label>
        <select class="fs" name="status">
          <?php foreach (PROJECT_STATUSES as $k => $cfg): ?><option value="<?= $k ?>"><?= e($cfg['label']) ?></option><?php endforeach; ?>
        </select>
      </div>
      <div class="fg"><label>Nästa steg</label><input class="fi" name="next_step" placeholder="T.ex. Boka besiktning"></div>
      <div style="display:flex;gap:10px;justify-content:flex-end">
        <button type="button" class="btn btn--ghost" onclick="closeModal('newProjModal')">Avbryt</button>
        <button class="btn btn--primary">Skapa</button>
      </div>
    </form>
  </div>
</div>

<?php require_once __DIR__ . '/includes/crm-footer.php'; ?>
