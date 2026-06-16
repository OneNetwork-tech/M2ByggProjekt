<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/layout.php';
$pu = portal_require();
$cid = (int)$pu['customer_id'];

// Fetch summary data
$projects = portal_projects($cid);
$activeProject = null;
foreach ($projects as $p) {
    if (!in_array($p['status'], ['completed','closed'])) { $activeProject = $p; break; }
}

$invoices = db()->prepare(
    "SELECT * FROM invoices WHERE customer_id = ? ORDER BY created_at DESC LIMIT 5"
);
$invoices->execute([$cid]);
$invoices = $invoices->fetchAll();

$unpaidTotal = 0;
foreach ($invoices as $inv) {
    if (in_array($inv['status'], ['sent','partial','overdue'])) {
        $unpaidTotal += $inv['total'] - ($inv['paid_amount'] ?? 0);
    }
}

$pendingQuotes = db()->prepare(
    "SELECT COUNT(*) AS c FROM quotes WHERE customer_id = ? AND status = 'sent'"
);
$pendingQuotes->execute([$cid]);
$pendingQuotes = (int)$pendingQuotes->fetchColumn();

$unreadMsgs = db()->prepare(
    "SELECT COUNT(*) AS c FROM portal_messages
     WHERE project_id IN (SELECT id FROM projects WHERE customer_id = ?)
       AND sender_type = 'staff' AND read_at IS NULL"
);
$unreadMsgs->execute([$cid]);
$unreadMsgs = (int)$unreadMsgs->fetchColumn();

portal_head('Dashboard', $pu);
portal_nav('/');
?>
<main class="portal-main">
  <div class="portal-page-title">
    <h1>Hej, <?= e(explode(' ', $pu['name'])[0]) ?>!</h1>
    <p>Här är en översikt av dina pågående ärenden.</p>
  </div>

  <!-- Stat cards -->
  <div class="grid-4" style="margin-bottom:28px">
    <div class="stat-card">
      <div class="stat-card__icon" style="background:#fef3c7">
        <svg viewBox="0 0 24 24" fill="none" stroke="#92400e" stroke-width="1.8"><path d="M2 20h20M6 20V10m4 10V4m4 10V8m4 12V14"/></svg>
      </div>
      <div>
        <div class="stat-card__val"><?= count($projects) ?></div>
        <div class="stat-card__lbl">Projekt totalt</div>
      </div>
    </div>
    <div class="stat-card">
      <div class="stat-card__icon" style="background:#dbeafe">
        <svg viewBox="0 0 24 24" fill="none" stroke="#1e40af" stroke-width="1.8"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14,2 14,8 20,8"/></svg>
      </div>
      <div>
        <div class="stat-card__val"><?= $pendingQuotes ?></div>
        <div class="stat-card__lbl">Offerter att besvara</div>
      </div>
    </div>
    <div class="stat-card">
      <div class="stat-card__icon" style="background:#fee2e2">
        <svg viewBox="0 0 24 24" fill="none" stroke="#991b1b" stroke-width="1.8"><rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
      </div>
      <div>
        <div class="stat-card__val"><?= number_format($unpaidTotal, 0, ',', ' ') ?> kr</div>
        <div class="stat-card__lbl">Obetalt belopp</div>
      </div>
    </div>
    <div class="stat-card">
      <div class="stat-card__icon" style="background:#d1fae5">
        <svg viewBox="0 0 24 24" fill="none" stroke="#065f46" stroke-width="1.8"><path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"/></svg>
      </div>
      <div>
        <div class="stat-card__val"><?= $unreadMsgs ?></div>
        <div class="stat-card__lbl">Olästa meddelanden</div>
      </div>
    </div>
  </div>

  <div class="grid-2">
    <!-- Active project -->
    <div class="card">
      <div class="card-header">
        <h3>Aktivt projekt</h3>
        <a href="/portal/projekt.php" class="btn btn--outline btn--sm">Alla projekt</a>
      </div>
      <?php if ($activeProject): ?>
        <div style="margin-bottom:16px">
          <strong style="font-size:1rem"><?= e($activeProject['title']) ?></strong>
          <div style="font-size:.82rem;color:var(--steel);margin-top:2px"><?= e($activeProject['address'] ?? '') ?></div>
        </div>
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px">
          <span class="badge badge--blue"><?= e(portal_status_label($activeProject['status'])) ?></span>
          <span style="font-size:.8rem;color:var(--steel)"><?= portal_status_pct($activeProject['status']) ?>% klart</span>
        </div>
        <div class="progress-wrap"><div class="progress-bar" style="width:<?= portal_status_pct($activeProject['status']) ?>%"></div></div>
        <?php if ($activeProject['next_step']): ?>
        <p style="font-size:.82rem;color:var(--steel);margin-top:10px">
          <strong>Nästa steg:</strong> <?= e($activeProject['next_step']) ?>
        </p>
        <?php endif; ?>
      <?php else: ?>
        <p style="color:var(--steel);font-size:.875rem">Inga aktiva projekt just nu.</p>
      <?php endif; ?>
    </div>

    <!-- Latest invoices -->
    <div class="card">
      <div class="card-header">
        <h3>Senaste fakturor</h3>
        <a href="/portal/fakturor.php" class="btn btn--outline btn--sm">Alla fakturor</a>
      </div>
      <?php if ($invoices): ?>
      <table class="tbl">
        <thead><tr><th>Faktura</th><th>Belopp</th><th>Status</th></tr></thead>
        <tbody>
        <?php foreach (array_slice($invoices, 0, 4) as $inv):
          $statusColors = ['draft'=>'gray','sent'=>'blue','partial'=>'orange','paid'=>'green','overdue'=>'red','cancelled'=>'gray'];
          $color = $statusColors[$inv['status']] ?? 'gray';
        ?>
        <tr>
          <td><?= e($inv['invoice_no'] ?? '—') ?><br><span style="font-size:.75rem;color:var(--steel)"><?= e($inv['due_date'] ?? '') ?></span></td>
          <td style="font-weight:600"><?= number_format($inv['total'], 0, ',', ' ') ?> kr</td>
          <td><span class="badge badge--<?= $color ?>"><?= e(INVOICE_STATUSES[$inv['status']]['label'] ?? $inv['status']) ?></span></td>
        </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
      <?php else: ?>
        <p style="color:var(--steel);font-size:.875rem">Inga fakturor ännu.</p>
      <?php endif; ?>
    </div>
  </div>

  <?php if ($pendingQuotes > 0): ?>
  <div class="alert alert--info" style="margin-top:20px;display:flex;align-items:center;gap:14px">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:20px;height:20px;flex-shrink:0"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
    Du har <strong><?= $pendingQuotes ?> offert<?= $pendingQuotes > 1 ? 'er' : '' ?></strong> som väntar på ditt svar.
    <a href="/portal/offerter.php" class="btn btn--primary btn--sm" style="margin-left:auto">Granska nu →</a>
  </div>
  <?php endif; ?>
</main>
<?php portal_foot(); ?>
