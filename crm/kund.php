<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/helpers.php';
$me = require_login();
$pdo = db();

$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT * FROM customers WHERE id=?"); $stmt->execute([$id]);
$cust = $stmt->fetch();
if (!$cust) { header('Location: kunder.php'); exit; }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    if (($_POST['action'] ?? '') === 'update') {
        $pdo->prepare("UPDATE customers SET name=?,email=?,phone=?,address=?,city=?,postal_code=?,org_nr=?,type=?,notes=? WHERE id=?")
            ->execute([trim($_POST['name']),trim($_POST['email']),trim($_POST['phone']),trim($_POST['address']),trim($_POST['city']),trim($_POST['postal_code']),trim($_POST['org_nr']),$_POST['type'],trim($_POST['notes']),$id]);
        flash('Kund uppdaterad.');
        header("Location: kund.php?id=$id"); exit;
    }
    if (($_POST['action'] ?? '') === 'note') {
        log_timeline('customer', $id, $_POST['type'] ?? 'note', trim($_POST['title']), trim($_POST['body'] ?? ''), $me['id']);
        flash('Sparad.');
        header("Location: kund.php?id=$id"); exit;
    }
}

// Related data (per blueprint: profile, history, projects, contracts, invoices)
$projects = $pdo->prepare("SELECT * FROM projects WHERE customer_id=? ORDER BY created_at DESC"); $projects->execute([$id]); $projects = $projects->fetchAll();
$quotes   = $pdo->prepare("SELECT * FROM quotes WHERE customer_id=? ORDER BY created_at DESC"); $quotes->execute([$id]); $quotes = $quotes->fetchAll();
$invoices = $pdo->prepare("SELECT * FROM invoices WHERE customer_id=? ORDER BY created_at DESC"); $invoices->execute([$id]); $invoices = $invoices->fetchAll();
$tl       = $pdo->prepare("SELECT * FROM timeline WHERE entity_type='customer' AND entity_id=? ORDER BY created_at DESC LIMIT 30"); $tl->execute([$id]); $timeline = $tl->fetchAll();
$ltv      = array_sum(array_column($invoices, 'paid_amount'));

$crm_title = $cust['name'];
$crm_page  = 'kunder';
require_once __DIR__ . '/includes/crm-header.php';
?>

<div class="topbar">
  <div>
    <div style="display:flex;align-items:center;gap:10px">
      <a href="kunder.php" style="color:var(--gray-lt);display:flex"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:18px;height:18px"><path d="M19 12H5M12 19l-7-7 7-7"/></svg></a>
      <h1><?= e($cust['name']) ?></h1>
      <span class="badge" style="background:#F3F4F6;color:var(--gray)"><?= $cust['type'] === 'company' ? 'Företag' : 'Privat' ?></span>
    </div>
    <div class="topbar__sub">Kund sedan <?= dt($cust['created_at']) ?> · Livstidsvärde: <strong><?= money($ltv) ?></strong></div>
  </div>
  <div class="topbar__actions">
    <a href="portal-inbjudan.php?customer=<?= $id ?>" class="btn btn--outline btn--sm">🔗 Bjud in till portal</a>
    <a href="offert.php?customer=<?= $id ?>" class="btn btn--primary">+ Ny offert</a>
  </div>
</div>

<?php flash(); ?>

<div class="detail-grid">
  <div style="display:flex;flex-direction:column;gap:16px">

    <!-- PROJECTS -->
    <div class="card">
      <div style="padding:14px 18px;border-bottom:1px solid var(--border)"><h3 style="font-size:14.5px">Projekt (<?= count($projects) ?>)</h3></div>
      <?php if (!$projects): ?><p style="padding:18px;font-size:13px;color:var(--gray)">Inga projekt.</p><?php endif; ?>
      <?php foreach ($projects as $p): ?>
      <a href="projekt-detalj.php?id=<?= $p['id'] ?>" style="display:flex;align-items:center;justify-content:space-between;gap:12px;padding:13px 18px;border-bottom:1px solid #F3F4F6;font-size:13.5px">
        <div><div style="font-weight:550"><?= e($p['title']) ?></div><div style="font-size:11.5px;color:var(--gray-lt)"><?= e($p['project_no']) ?> · <?= money($p['budget']) ?></div></div>
        <?= badge($p['status'], PROJECT_STATUSES) ?>
      </a>
      <?php endforeach; ?>
    </div>

    <!-- QUOTES -->
    <div class="card">
      <div style="padding:14px 18px;border-bottom:1px solid var(--border)"><h3 style="font-size:14.5px">Offerter (<?= count($quotes) ?>)</h3></div>
      <?php if (!$quotes): ?><p style="padding:18px;font-size:13px;color:var(--gray)">Inga offerter.</p><?php endif; ?>
      <?php foreach ($quotes as $qt): ?>
      <a href="offert.php?id=<?= $qt['id'] ?>" style="display:flex;align-items:center;justify-content:space-between;gap:12px;padding:13px 18px;border-bottom:1px solid #F3F4F6;font-size:13.5px">
        <div><div style="font-weight:550"><?= e($qt['title']) ?></div><div style="font-size:11.5px;color:var(--gray-lt)"><?= e($qt['quote_no']) ?> · <?= money($qt['total']) ?></div></div>
        <?= badge($qt['status'], QUOTE_STATUSES) ?>
      </a>
      <?php endforeach; ?>
    </div>

    <!-- INVOICES -->
    <div class="card">
      <div style="padding:14px 18px;border-bottom:1px solid var(--border)"><h3 style="font-size:14.5px">Fakturor (<?= count($invoices) ?>)</h3></div>
      <?php if (!$invoices): ?><p style="padding:18px;font-size:13px;color:var(--gray)">Inga fakturor.</p><?php endif; ?>
      <?php foreach ($invoices as $inv): ?>
      <a href="faktura.php?id=<?= $inv['id'] ?>" style="display:flex;align-items:center;justify-content:space-between;gap:12px;padding:13px 18px;border-bottom:1px solid #F3F4F6;font-size:13.5px">
        <div><div style="font-weight:550"><?= e($inv['invoice_no']) ?></div><div style="font-size:11.5px;color:var(--gray-lt)"><?= money($inv['paid_amount']) ?> / <?= money($inv['total']) ?></div></div>
        <?= badge($inv['status'], INVOICE_STATUSES) ?>
      </a>
      <?php endforeach; ?>
    </div>

    <!-- TIMELINE -->
    <div class="card card--pad">
      <h3 style="font-size:14.5px;margin-bottom:14px">Historik</h3>
      <form method="POST" style="margin-bottom:18px">
        <?= csrf_field() ?><input type="hidden" name="action" value="note">
        <div style="display:flex;gap:8px">
          <select class="fs" name="type" style="width:120px"><option value="note">Anteckning</option><option value="call">Samtal</option><option value="email">E-post</option></select>
          <input class="fi" name="title" placeholder="Logga händelse..." required style="flex:1">
          <button class="btn btn--primary btn--sm">Spara</button>
        </div>
      </form>
      <div class="timeline">
        <?php foreach ($timeline as $ev): ?>
        <div class="tl-item">
          <div class="tl-dot <?= $ev['type'] === 'system' ? 'gray' : '' ?>"></div>
          <div class="tl-title"><?= e($ev['title']) ?></div>
          <?php if ($ev['body']): ?><div class="tl-body"><?= e($ev['body']) ?></div><?php endif; ?>
          <div class="tl-meta"><?= e(user_name($ev['created_by'])) ?> · <?= time_ago($ev['created_at']) ?></div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>

  <!-- RIGHT: profile -->
  <div class="card card--pad">
    <h3 style="font-size:14.5px;margin-bottom:12px">Kundprofil</h3>
    <form method="POST">
      <?= csrf_field() ?><input type="hidden" name="action" value="update">
      <div class="fg"><label>Namn</label><input class="fi" name="name" value="<?= e($cust['name']) ?>"></div>
      <div class="fg"><label>Telefon</label><input class="fi" name="phone" value="<?= e($cust['phone']) ?>"></div>
      <div class="fg"><label>E-post</label><input class="fi" name="email" value="<?= e($cust['email']) ?>"></div>
      <div class="fg"><label>Adress</label><input class="fi" name="address" value="<?= e($cust['address']) ?>"></div>
      <div class="frow">
        <div class="fg"><label>Postnr</label><input class="fi" name="postal_code" value="<?= e($cust['postal_code']) ?>"></div>
        <div class="fg"><label>Stad</label><input class="fi" name="city" value="<?= e($cust['city']) ?>"></div>
      </div>
      <div class="frow">
        <div class="fg"><label>Typ</label>
          <select class="fs" name="type">
            <option value="private" <?= $cust['type']==='private'?'selected':'' ?>>Privatperson</option>
            <option value="company" <?= $cust['type']==='company'?'selected':'' ?>>Företag</option>
          </select>
        </div>
        <div class="fg"><label>Org.nr</label><input class="fi" name="org_nr" value="<?= e($cust['org_nr']) ?>"></div>
      </div>
      <div class="fg"><label>Anteckningar</label><textarea class="fta" name="notes"><?= e($cust['notes']) ?></textarea></div>
      <button class="btn btn--primary btn--sm" style="width:100%;justify-content:center">Spara</button>
    </form>
  </div>
</div>

<?php require_once __DIR__ . '/includes/crm-footer.php'; ?>
