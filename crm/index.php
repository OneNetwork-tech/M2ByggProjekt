<?php
$crm_title = 'Dashboard';
$crm_page  = 'dashboard';
require_once __DIR__ . '/includes/crm-header.php';

$pdo = db();

// KPIs per blueprint: leads, conversion, quote acceptance, revenue, active/completed projects
$startOfMonth = date('Y-m-01');
$leadsMonthStmt = $pdo->prepare("SELECT COUNT(*) FROM leads WHERE created_at >= ?");
$leadsMonthStmt->execute([$startOfMonth]);
$kpi = [
  'leads_month'   => $leadsMonthStmt->fetchColumn(),
  'leads_total'   => $pdo->query("SELECT COUNT(*) FROM leads")->fetchColumn(),
  'leads_won'     => $pdo->query("SELECT COUNT(*) FROM leads WHERE stage='won'")->fetchColumn(),
  'leads_closed'  => $pdo->query("SELECT COUNT(*) FROM leads WHERE stage IN ('won','lost')")->fetchColumn(),
  'quotes_sent'   => $pdo->query("SELECT COUNT(*) FROM quotes WHERE status IN ('sent','viewed','accepted','rejected')")->fetchColumn(),
  'quotes_accepted'=> $pdo->query("SELECT COUNT(*) FROM quotes WHERE status='accepted'")->fetchColumn(),
  'revenue'       => (float)$pdo->query("SELECT COALESCE(SUM(paid_amount),0) FROM invoices WHERE status != 'cancelled'")->fetchColumn(),
  'outstanding'   => (float)$pdo->query("SELECT COALESCE(SUM(total - paid_amount),0) FROM invoices WHERE status IN ('sent','partial','overdue')")->fetchColumn(),
  'projects_active'=> $pdo->query("SELECT COUNT(*) FROM projects WHERE status IN ('inspection','planning','scheduled','in_progress','quality')")->fetchColumn(),
  'projects_done' => $pdo->query("SELECT COUNT(*) FROM projects WHERE status IN ('completed','closed')")->fetchColumn(),
];
$conversion = $kpi['leads_closed'] > 0 ? round($kpi['leads_won'] / $kpi['leads_closed'] * 100) : 0;
$acceptance = $kpi['quotes_sent'] > 0 ? round($kpi['quotes_accepted'] / $kpi['quotes_sent'] * 100) : 0;

// Recent leads
$recentLeads = $pdo->query("SELECT * FROM leads ORDER BY created_at DESC LIMIT 6")->fetchAll();
// Tasks for me
$myTasks = $pdo->prepare("SELECT * FROM tasks WHERE done=0 AND (assigned_to=? OR assigned_to IS NULL) ORDER BY due_date LIMIT 6");
$myTasks->execute([$me['id']]); $myTasks = $myTasks->fetchAll();
// Active projects
$activeProjects = $pdo->query("SELECT p.*, c.name AS customer_name FROM projects p LEFT JOIN customers c ON c.id=p.customer_id WHERE p.status IN ('inspection','planning','scheduled','in_progress','quality') ORDER BY p.created_at DESC LIMIT 5")->fetchAll();
// Overdue invoices
$pdo->exec("UPDATE invoices SET status='overdue' WHERE status='sent' AND due_date < " . today_expr());
$overdue = $pdo->query("SELECT i.*, c.name AS customer_name FROM invoices i LEFT JOIN customers c ON c.id=i.customer_id WHERE i.status='overdue' ORDER BY i.due_date LIMIT 4")->fetchAll();
?>

<div class="topbar">
  <div>
    <h1>Hej, <?= e(explode(' ', $me['name'])[0]) ?> 👋</h1>
    <div class="topbar__sub">Här är läget för M2 Bygg Team idag, <?= dt(date('Y-m-d')) ?></div>
  </div>
  <div class="topbar__actions">
    <a href="leads.php?new=1" class="btn btn--primary">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
      Ny lead
    </a>
  </div>
</div>

<?php flash(); ?>

<!-- KPIs -->
<div class="kpi-grid">
  <div class="kpi">
    <div class="kpi__label">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
      Leads denna månad
    </div>
    <div class="kpi__value"><?= $kpi['leads_month'] ?></div>
    <div class="kpi__trend flat"><?= $kpi['leads_total'] ?> totalt</div>
  </div>
  <div class="kpi">
    <div class="kpi__label">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/><polyline points="17 6 23 6 23 12"/></svg>
      Konverteringsgrad
    </div>
    <div class="kpi__value"><?= $conversion ?>%</div>
    <div class="kpi__trend <?= $conversion >= 30 ? 'up' : 'flat' ?>"><?= $kpi['leads_won'] ?> vunna leads</div>
  </div>
  <div class="kpi">
    <div class="kpi__label">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
      Offertacceptans
    </div>
    <div class="kpi__value"><?= $acceptance ?>%</div>
    <div class="kpi__trend flat"><?= $kpi['quotes_accepted'] ?> av <?= $kpi['quotes_sent'] ?> accepterade</div>
  </div>
  <div class="kpi">
    <div class="kpi__label">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/></svg>
      Intäkter (betalt)
    </div>
    <div class="kpi__value"><?= money($kpi['revenue']) ?></div>
    <div class="kpi__trend <?= $kpi['outstanding'] > 0 ? 'down' : 'up' ?>"><?= money($kpi['outstanding']) ?> utestående</div>
  </div>
  <div class="kpi">
    <div class="kpi__label">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/></svg>
      Aktiva projekt
    </div>
    <div class="kpi__value"><?= $kpi['projects_active'] ?></div>
    <div class="kpi__trend up"><?= $kpi['projects_done'] ?> slutförda</div>
  </div>
</div>

<div style="display:grid;grid-template-columns:1.4fr 1fr;gap:16px" class="dash-grid">

  <!-- RECENT LEADS -->
  <div class="card">
    <div style="display:flex;align-items:center;justify-content:space-between;padding:16px 20px;border-bottom:1px solid var(--border)">
      <h3 style="font-size:15px">Senaste leads</h3>
      <a href="leads.php" class="btn btn--ghost btn--sm">Visa alla</a>
    </div>
    <div class="table-wrap">
      <table class="data">
        <thead><tr><th>Lead</th><th>Tjänst</th><th>Status</th><th>Inkom</th></tr></thead>
        <tbody>
          <?php if (!$recentLeads): ?>
          <tr><td colspan="4" style="text-align:center;color:var(--gray);padding:32px">Inga leads ännu. Leads från webbformuläret hamnar här automatiskt.</td></tr>
          <?php endif; ?>
          <?php foreach ($recentLeads as $l): ?>
          <tr data-href="lead.php?id=<?= $l['id'] ?>">
            <td>
              <div style="font-weight:550"><?= e($l['name']) ?></div>
              <div style="font-size:12px;color:var(--gray-lt)"><?= e($l['city'] ?: '–') ?></div>
            </td>
            <td><?= e($l['service'] ?: '–') ?></td>
            <td><?= badge($l['stage'], LEAD_STAGES) ?></td>
            <td style="color:var(--gray);font-size:12.5px"><?= time_ago($l['created_at']) ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>

  <div style="display:flex;flex-direction:column;gap:16px">

    <!-- TASKS -->
    <div class="card card--pad">
      <h3 style="font-size:15px;margin-bottom:14px">Mina uppgifter</h3>
      <?php if (!$myTasks): ?>
      <p style="font-size:13px;color:var(--gray)">Inga öppna uppgifter. 🎉</p>
      <?php endif; ?>
      <?php foreach ($myTasks as $t): ?>
      <label style="display:flex;align-items:flex-start;gap:10px;padding:8px 0;border-bottom:1px solid #F3F4F6;cursor:pointer">
        <input type="checkbox" onchange="fetch('api/task-done.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({id:<?= $t['id'] ?>})}).then(()=>this.closest('label').style.opacity='.4')" style="margin-top:2px;accent-color:var(--blue)">
        <div>
          <div style="font-size:13.5px;font-weight:500"><?= e($t['title']) ?></div>
          <?php if ($t['due_date']): ?>
          <div style="font-size:11.5px;color:<?= strtotime($t['due_date']) < time() ? 'var(--red)' : 'var(--gray-lt)' ?>">Förfaller <?= dt($t['due_date']) ?></div>
          <?php endif; ?>
        </div>
      </label>
      <?php endforeach; ?>
    </div>

    <!-- OVERDUE INVOICES -->
    <?php if ($overdue): ?>
    <div class="card card--pad" style="border-color:#FECACA">
      <h3 style="font-size:15px;margin-bottom:12px;color:var(--red)">⚠ Förfallna fakturor</h3>
      <?php foreach ($overdue as $inv): ?>
      <a href="faktura.php?id=<?= $inv['id'] ?>" style="display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid #F3F4F6;font-size:13px">
        <span><?= e($inv['invoice_no']) ?> · <?= e($inv['customer_name'] ?: '–') ?></span>
        <strong style="color:var(--red)"><?= money($inv['total'] - $inv['paid_amount']) ?></strong>
      </a>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>

  </div>
</div>

<!-- ACTIVE PROJECTS -->
<div class="card" style="margin-top:16px">
  <div style="display:flex;align-items:center;justify-content:space-between;padding:16px 20px;border-bottom:1px solid var(--border)">
    <h3 style="font-size:15px">Pågående projekt</h3>
    <a href="projekt.php" class="btn btn--ghost btn--sm">Visa alla</a>
  </div>
  <div class="table-wrap">
    <table class="data">
      <thead><tr><th>Projekt</th><th>Kund</th><th>Status</th><th>Förlopp</th><th>Budget</th></tr></thead>
      <tbody>
        <?php if (!$activeProjects): ?>
        <tr><td colspan="5" style="text-align:center;color:var(--gray);padding:32px">Inga aktiva projekt. Projekt skapas automatiskt när en offert accepteras.</td></tr>
        <?php endif; ?>
        <?php foreach ($activeProjects as $p): ?>
        <tr data-href="projekt-detalj.php?id=<?= $p['id'] ?>">
          <td>
            <div style="font-weight:550"><?= e($p['title']) ?></div>
            <div style="font-size:12px;color:var(--gray-lt)"><?= e($p['project_no']) ?></div>
          </td>
          <td><?= e($p['customer_name'] ?: '–') ?></td>
          <td><?= badge($p['status'], PROJECT_STATUSES) ?></td>
          <td style="min-width:140px">
            <div style="display:flex;align-items:center;gap:9px">
              <div class="progress" style="flex:1"><div class="progress__bar" style="width:<?= (int)$p['progress'] ?>%"></div></div>
              <span style="font-size:12px;color:var(--gray);font-weight:550"><?= (int)$p['progress'] ?>%</span>
            </div>
          </td>
          <td style="font-weight:550"><?= money($p['budget']) ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<style>@media(max-width:920px){.dash-grid{grid-template-columns:1fr!important}}</style>

<?php require_once __DIR__ . '/includes/crm-footer.php'; ?>
