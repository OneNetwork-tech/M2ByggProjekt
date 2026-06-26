<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/helpers.php';
$me = require_login();
$pdo = db();

$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT * FROM leads WHERE id = ?");
$stmt->execute([$id]);
$lead = $stmt->fetch();
if (!$lead) { header('Location: leads.php'); exit; }

// ACTIONS
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $action = $_POST['action'] ?? '';

    if ($action === 'stage') {
        $stage = $_POST['stage'];
        if (isset(LEAD_STAGES[$stage])) {
            $pdo->prepare("UPDATE leads SET stage=?, updated_at=datetime('now','localtime') WHERE id=?")->execute([$stage, $id]);
            log_timeline('lead', $id, 'status', 'Status ändrad till ' . LEAD_STAGES[$stage]['label'], '', $me['id']);
            audit('lead_stage', 'lead', $id, $stage);
            flash('Status uppdaterad.');
        }
        header("Location: lead.php?id=$id"); exit;
    }

    if ($action === 'note') {
        log_timeline('lead', $id, $_POST['type'] ?? 'note', trim($_POST['title'] ?? 'Anteckning'), trim($_POST['body'] ?? ''), $me['id']);
        flash('Anteckning sparad.');
        header("Location: lead.php?id=$id"); exit;
    }

    if ($action === 'update') {
        $pdo->prepare("UPDATE leads SET name=?, email=?, phone=?, address=?, city=?, service=?, value_estimate=?, updated_at=datetime('now','localtime') WHERE id=?")
            ->execute([trim($_POST['name']), trim($_POST['email']), trim($_POST['phone']), trim($_POST['address']), trim($_POST['city']), $_POST['service'], (float)$_POST['value_estimate'], $id]);
        flash('Lead uppdaterad.');
        header("Location: lead.php?id=$id"); exit;
    }

    if ($action === 'convert_customer') {
        // Create customer from lead
        $stmt = $pdo->prepare("INSERT INTO customers (name,email,phone,address,city) VALUES (?,?,?,?,?)");
        $stmt->execute([$lead['name'], $lead['email'], $lead['phone'], $lead['address'], $lead['city']]);
        $custId = $pdo->lastInsertId();
        $pdo->prepare("UPDATE leads SET customer_id=? WHERE id=?")->execute([$custId, $id]);
        log_timeline('lead', $id, 'system', 'Konverterad till kund', '', $me['id']);
        log_timeline('customer', $custId, 'system', 'Kund skapad från lead ' . $lead['lead_no'], '', $me['id']);
        audit('lead_convert', 'lead', $id);
        flash('Kund skapad.');
        header("Location: kund.php?id=$custId"); exit;
    }

    if ($action === 'task') {
        $pdo->prepare("INSERT INTO tasks (title, due_date, assigned_to, entity_type, entity_id) VALUES (?,?,?,?,?)")
            ->execute([trim($_POST['title']), $_POST['due_date'] ?: null, $me['id'], 'lead', $id]);
        flash('Uppgift skapad.');
        header("Location: lead.php?id=$id"); exit;
    }
}

// Timeline
$tl = $pdo->prepare("SELECT * FROM timeline WHERE entity_type='lead' AND entity_id=? ORDER BY created_at DESC");
$tl->execute([$id]);
$timeline = $tl->fetchAll();

// Quotes for this lead
$quotes = $pdo->prepare("SELECT * FROM quotes WHERE lead_id=? ORDER BY created_at DESC");
$quotes->execute([$id]);
$quotes = $quotes->fetchAll();

$crm_title = $lead['name'] . ' – Lead';
$crm_page  = 'leads';
require_once __DIR__ . '/includes/crm-header.php';
?>

<div class="topbar">
  <div>
    <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap">
      <a href="leads.php" style="color:var(--gray-lt);display:flex"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:18px;height:18px"><path d="M19 12H5M12 19l-7-7 7-7"/></svg></a>
      <h1><?= e($lead['name']) ?></h1>
      <?= badge($lead['stage'], LEAD_STAGES) ?>
    </div>
    <div class="topbar__sub"><?= e($lead['lead_no']) ?> · Inkom <?= dt($lead['created_at'], 'j M H:i') ?> · Källa: <?= e($lead['source']) ?></div>
  </div>
  <div class="topbar__actions">
    <a href="kalender.php?lead=<?= $lead['id'] ?>" class="btn btn--ghost">📅 Boka besök</a>
    <?php if (!$lead['customer_id']): ?>
    <form method="POST" style="display:inline">
      <?= csrf_field() ?><input type="hidden" name="action" value="convert_customer">
      <button class="btn btn--ghost">→ Konvertera till kund</button>
    </form>
    <?php else: ?>
    <a href="kund.php?id=<?= $lead['customer_id'] ?>" class="btn btn--ghost">Visa kund</a>
    <?php endif; ?>
    <a href="offert.php?lead=<?= $id ?>" class="btn btn--primary">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
      Skapa offert
    </a>
  </div>
</div>

<?php flash(); ?>

<!-- STAGE PIPELINE -->
<div class="card card--pad" style="margin-bottom:16px;overflow-x:auto">
  <form method="POST" id="stageForm">
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="stage">
    <input type="hidden" name="stage" id="stageInput">
    <div style="display:flex;gap:6px;min-width:760px">
      <?php
      $stageKeys = array_keys(LEAD_STAGES);
      $currentIdx = array_search($lead['stage'], $stageKeys);
      foreach (LEAD_STAGES as $key => $cfg):
        $idx = array_search($key, $stageKeys);
        $isCurrent = $key === $lead['stage'];
        $isPast = $idx < $currentIdx && !in_array($lead['stage'], ['lost']);
      ?>
      <button type="button"
              onclick="document.getElementById('stageInput').value='<?= $key ?>';document.getElementById('stageForm').submit()"
              style="flex:1;padding:9px 6px;border-radius:9px;border:1.5px solid <?= $isCurrent ? $cfg['color'] : 'var(--border)' ?>;background:<?= $isCurrent ? $cfg['color'] : ($isPast ? $cfg['color'].'14' : 'var(--card)') ?>;color:<?= $isCurrent ? '#fff' : ($isPast ? $cfg['color'] : 'var(--gray)') ?>;font-size:12px;font-weight:550;cursor:pointer;transition:all .15s;white-space:nowrap">
        <?= e($cfg['label']) ?>
      </button>
      <?php endforeach; ?>
    </div>
  </form>
</div>

<div class="detail-grid">

  <!-- LEFT: timeline + add note -->
  <div style="display:flex;flex-direction:column;gap:16px">

    <!-- ADD COMMUNICATION (per blueprint: all channels logged) -->
    <div class="card card--pad">
      <h3 style="font-size:14.5px;margin-bottom:12px">Logga kommunikation</h3>
      <form method="POST">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="note">
        <div class="frow" style="grid-template-columns:140px 1fr">
          <div class="fg">
            <label>Typ</label>
            <select class="fs" name="type">
              <option value="note">Anteckning</option>
              <option value="call">Telefonsamtal</option>
              <option value="email">E-post</option>
              <option value="sms">SMS</option>
              <option value="meeting">Möte/Besök</option>
            </select>
          </div>
          <div class="fg">
            <label>Rubrik</label>
            <input class="fi" name="title" placeholder="T.ex. Ringde kunden om takbesiktning" required>
          </div>
        </div>
        <div class="fg"><textarea class="fta" name="body" placeholder="Detaljer..." style="min-height:64px"></textarea></div>
        <div style="display:flex;justify-content:flex-end">
          <button class="btn btn--primary btn--sm">Spara i tidslinje</button>
        </div>
      </form>
    </div>

    <!-- TIMELINE -->
    <div class="card card--pad">
      <h3 style="font-size:14.5px;margin-bottom:18px">Tidslinje</h3>
      <div class="timeline">
        <?php if (!$timeline): ?><p style="font-size:13px;color:var(--gray)">Ingen aktivitet ännu.</p><?php endif; ?>
        <?php foreach ($timeline as $ev):
          $dotClass = ['status'=>'amber','system'=>'gray','note'=>'','call'=>'','email'=>'','sms'=>'','meeting'=>'green'][$ev['type']] ?? '';
          $typeLabel = ['note'=>'Anteckning','call'=>'Samtal','email'=>'E-post','sms'=>'SMS','meeting'=>'Möte','status'=>'Status','system'=>'System'][$ev['type']] ?? $ev['type'];
        ?>
        <div class="tl-item">
          <div class="tl-dot <?= $dotClass ?>"></div>
          <div class="tl-title"><?= e($ev['title']) ?></div>
          <?php if ($ev['body']): ?><div class="tl-body"><?= e($ev['body']) ?></div><?php endif; ?>
          <div class="tl-meta"><?= e($typeLabel) ?> · <?= e(user_name($ev['created_by'])) ?> · <?= time_ago($ev['created_at']) ?></div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>

  <!-- RIGHT: details + quotes + tasks -->
  <div style="display:flex;flex-direction:column;gap:16px">

    <!-- CONTACT INFO (editable) -->
    <div class="card card--pad">
      <h3 style="font-size:14.5px;margin-bottom:12px">Kontaktuppgifter</h3>
      <form method="POST">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="update">
        <div class="fg"><label>Namn</label><input class="fi" name="name" value="<?= e($lead['name']) ?>"></div>
        <div class="fg"><label>Telefon</label><input class="fi" name="phone" value="<?= e($lead['phone']) ?>"></div>
        <div class="fg"><label>E-post</label><input class="fi" name="email" value="<?= e($lead['email']) ?>"></div>
        <div class="fg"><label>Adress</label><input class="fi" name="address" value="<?= e($lead['address']) ?>"></div>
        <div class="frow">
          <div class="fg"><label>Stad</label><input class="fi" name="city" value="<?= e($lead['city']) ?>"></div>
          <div class="fg"><label>Tjänst</label>
            <select class="fs" name="service">
              <option value="">Välj...</option>
              <?php foreach (SERVICES as $s): ?>
              <option <?= $lead['service'] === $s ? 'selected' : '' ?>><?= e($s) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
        <div class="fg"><label>Uppskattat värde (kr)</label><input class="fi" type="number" name="value_estimate" value="<?= (float)$lead['value_estimate'] ?>"></div>
        <button class="btn btn--ghost btn--sm" style="width:100%;justify-content:center">Spara ändringar</button>
      </form>
      <?php if ($lead['phone']): ?>
      <a href="tel:<?= e(preg_replace('/\s+/','',$lead['phone'])) ?>" class="btn btn--primary btn--sm" style="width:100%;justify-content:center;margin-top:8px">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07A19.5 19.5 0 013.07 8.81 19.79 19.79 0 01.22 2.18 2 2 0 012.18 0h3a2 2 0 012 1.72c.13.96.36 1.9.7 2.81a2 2 0 01-.45 2.11L6.3 7.74a16 16 0 0010 9.96l1.1-1.1a2 2 0 012.11-.45c.91.34 1.85.57 2.81.7A2 2 0 0124 18z"/></svg>
        Ring <?= e($lead['phone']) ?>
      </a>
      <?php endif; ?>
    </div>

    <!-- MESSAGE FROM FORM -->
    <?php if ($lead['message']): ?>
    <div class="card card--pad">
      <h3 style="font-size:14.5px;margin-bottom:8px">Meddelande från kund</h3>
      <p style="font-size:13.5px;color:var(--gray);white-space:pre-line;line-height:1.6"><?= e($lead['message']) ?></p>
    </div>
    <?php endif; ?>

    <!-- QUOTES -->
    <div class="card card--pad">
      <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px">
        <h3 style="font-size:14.5px">Offerter</h3>
        <a href="offert.php?lead=<?= $id ?>" class="btn btn--ghost btn--sm">+ Ny</a>
      </div>
      <?php if (!$quotes): ?><p style="font-size:13px;color:var(--gray)">Inga offerter ännu.</p><?php endif; ?>
      <?php foreach ($quotes as $qt): ?>
      <a href="offert.php?id=<?= $qt['id'] ?>" style="display:flex;justify-content:space-between;align-items:center;padding:9px 0;border-bottom:1px solid #F3F4F6;font-size:13px">
        <div>
          <div style="font-weight:550"><?= e($qt['quote_no']) ?></div>
          <div style="font-size:11.5px;color:var(--gray-lt)"><?= money($qt['total']) ?></div>
        </div>
        <?= badge($qt['status'], QUOTE_STATUSES) ?>
      </a>
      <?php endforeach; ?>
    </div>

    <!-- ADD TASK -->
    <div class="card card--pad">
      <h3 style="font-size:14.5px;margin-bottom:12px">Ny uppgift</h3>
      <form method="POST">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="task">
        <div class="fg"><input class="fi" name="title" placeholder="T.ex. Ring kunden imorgon" required></div>
        <div class="fg"><input class="fi" type="date" name="due_date"></div>
        <button class="btn btn--ghost btn--sm" style="width:100%;justify-content:center">Skapa uppgift</button>
      </form>
    </div>

  </div>
</div>

<?php require_once __DIR__ . '/includes/crm-footer.php'; ?>
