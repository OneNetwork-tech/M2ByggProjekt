<?php
$crm_title = 'Kommunikation';
$crm_page  = 'meddelanden';
require_once __DIR__ . '/includes/crm-header.php';
$pdo = db();

// mark notifications read
$pdo->prepare("UPDATE notifications SET read_at = datetime('now','localtime') WHERE user_id=? AND read_at IS NULL")->execute([$me['id']]);

$view = $_GET['view'] ?? 'activity';

// ── PORTAL MESSAGES ──────────────────────────────────────────────────────────
if ($view === 'portal') {
    // Reply
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['body'])) {
        csrf_check();
        $pid  = (int)$_POST['project_id'];
        $body = trim($_POST['body']);
        if ($pid && $body) {
            $pdo->prepare("INSERT INTO portal_messages (project_id, sender_type, sender_id, body) VALUES (?,?,?,?)")
                ->execute([$pid, 'staff', $me['id'], $body]);
            // Mark customer messages in this thread as read
            $pdo->prepare("UPDATE portal_messages SET read_at=datetime('now','localtime') WHERE project_id=? AND sender_type='customer' AND read_at IS NULL")
                ->execute([$pid]);
        }
        header("Location: meddelanden.php?view=portal&thread=$pid"); exit;
    }

    // Threads: all projects that have portal messages
    $threads = $pdo->query("
        SELECT p.id, p.title, p.project_no,
               c.name AS customer_name,
               COUNT(m.id) AS msg_count,
               MAX(m.created_at) AS last_msg,
               SUM(CASE WHEN m.sender_type='customer' AND m.read_at IS NULL THEN 1 ELSE 0 END) AS unread
        FROM portal_messages m
        JOIN projects p ON p.id=m.project_id
        LEFT JOIN customers c ON c.id=p.customer_id
        GROUP BY p.id ORDER BY last_msg DESC
    ")->fetchAll();

    $activeThread = (int)($_GET['thread'] ?? ($threads[0]['id'] ?? 0));
    $messages = [];
    $threadProj = null;
    if ($activeThread) {
        // Mark unread customer messages as read when staff opens thread
        $pdo->prepare("UPDATE portal_messages SET read_at=datetime('now','localtime') WHERE project_id=? AND sender_type='customer' AND read_at IS NULL")
            ->execute([$activeThread]);

        $s = $pdo->prepare("
            SELECT m.*,
                   u.name AS staff_name,
                   pu.name AS portal_name
            FROM portal_messages m
            LEFT JOIN users u ON u.id=m.sender_id AND m.sender_type='staff'
            LEFT JOIN portal_users pu ON pu.id=m.sender_id AND m.sender_type='customer'
            WHERE m.project_id=? ORDER BY m.created_at ASC
        ");
        $s->execute([$activeThread]);
        $messages = $s->fetchAll();

        $tp = $pdo->prepare("SELECT p.*, c.name AS customer_name FROM projects p LEFT JOIN customers c ON c.id=p.customer_id WHERE p.id=?");
        $tp->execute([$activeThread]);
        $threadProj = $tp->fetch();
    }
?>
<div class="topbar">
  <div>
    <h1>Kommunikationscenter</h1>
    <div class="topbar__sub">Kundportal-meddelanden</div>
  </div>
</div>
<?php flash(); ?>

<div class="tabs" style="margin-bottom:16px">
  <a href="?view=activity" class="tab">Aktivitetsflöde</a>
  <a href="?view=portal" class="tab active">Kundportal <span class="badge" style="background:var(--blue);color:#fff;font-size:10px;padding:2px 6px;margin-left:4px"><?= array_sum(array_column($threads,'unread')) ?: '' ?></span></a>
</div>

<div style="display:grid;grid-template-columns:280px 1fr;gap:16px;height:calc(100vh - 220px)">
  <!-- Thread list -->
  <div class="card" style="overflow-y:auto">
    <?php if (!$threads): ?>
    <p style="padding:20px;font-size:13px;color:var(--gray)">Inga kundmeddelanden ännu.</p>
    <?php endif; ?>
    <?php foreach ($threads as $t): $isActive = $t['id'] === $activeThread; ?>
    <a href="?view=portal&thread=<?= $t['id'] ?>" style="display:block;padding:14px 16px;border-bottom:1px solid #F3F4F6;background:<?= $isActive ? 'var(--blue-lt)' : 'transparent' ?>;text-decoration:none">
      <div style="display:flex;justify-content:space-between;align-items:flex-start">
        <div style="font-weight:<?= $t['unread'] ? 700 : 550 ?>;font-size:13.5px;color:var(--ink)"><?= e($t['customer_name'] ?: '—') ?></div>
        <?php if ($t['unread']): ?><span class="badge" style="background:var(--blue);color:#fff;font-size:10px"><?= $t['unread'] ?></span><?php endif; ?>
      </div>
      <div style="font-size:11.5px;color:var(--gray);margin-top:2px"><?= e($t['title']) ?></div>
      <div style="font-size:11px;color:var(--gray-lt);margin-top:4px"><?= time_ago($t['last_msg']) ?> · <?= $t['msg_count'] ?> meddelanden</div>
    </a>
    <?php endforeach; ?>
  </div>

  <!-- Message thread -->
  <div class="card" style="display:flex;flex-direction:column;overflow:hidden">
    <?php if ($threadProj): ?>
    <div style="padding:14px 20px;border-bottom:1px solid #F3F4F6;display:flex;justify-content:space-between;align-items:center">
      <div>
        <div style="font-weight:600;font-size:14px"><?= e($threadProj['customer_name'] ?: '—') ?></div>
        <div style="font-size:12px;color:var(--gray)"><?= e($threadProj['title']) ?> · <?= e($threadProj['project_no']) ?></div>
      </div>
      <a href="projekt-detalj.php?id=<?= $threadProj['id'] ?>" class="btn btn--ghost btn--sm">Öppna projekt</a>
    </div>

    <div id="thread" style="flex:1;overflow-y:auto;padding:16px;display:flex;flex-direction:column;gap:12px">
      <?php foreach ($messages as $msg):
        $isStaff = $msg['sender_type'] === 'staff';
        $name = $isStaff ? ($msg['staff_name'] ?: 'M2 Bygg Team') : ($msg['portal_name'] ?: 'Kund');
        $initials = strtoupper(implode('', array_map(fn($w)=>$w[0], array_slice(explode(' ',$name),0,2))));
      ?>
      <div style="display:flex;flex-direction:<?= $isStaff ? 'row-reverse' : 'row' ?>;gap:10px;align-items:flex-end">
        <div style="width:32px;height:32px;border-radius:50%;background:<?= $isStaff ? 'var(--blue)' : '#E5E7EB' ?>;color:<?= $isStaff ? '#fff' : 'var(--ink)' ?>;display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:700;flex-shrink:0"><?= $initials ?></div>
        <div style="max-width:65%">
          <div style="background:<?= $isStaff ? 'var(--blue)' : '#F3F4F6' ?>;color:<?= $isStaff ? '#fff' : 'var(--ink)' ?>;padding:10px 14px;border-radius:<?= $isStaff ? '14px 14px 4px 14px' : '14px 14px 14px 4px' ?>;font-size:13.5px;line-height:1.5">
            <?= nl2br(e($msg['body'])) ?>
          </div>
          <div style="font-size:11px;color:var(--gray-lt);margin-top:4px;text-align:<?= $isStaff ? 'right' : 'left' ?>"><?= e($name) ?> · <?= time_ago($msg['created_at']) ?></div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>

    <!-- Reply form -->
    <form method="post" style="padding:14px 16px;border-top:1px solid #F3F4F6;display:flex;gap:10px">
      <?= csrf_field() ?>
      <input type="hidden" name="project_id" value="<?= $activeThread ?>">
      <textarea name="body" class="fi" placeholder="Skriv ett svar…" rows="2" style="flex:1;resize:none;padding:10px 14px;min-height:44px" required></textarea>
      <button class="btn btn--primary" style="align-self:flex-end">Skicka</button>
    </form>
    <?php else: ?>
    <div style="flex:1;display:flex;align-items:center;justify-content:center;color:var(--gray);font-size:13px">Välj en konversation till vänster.</div>
    <?php endif; ?>
  </div>
</div>
<script>var t=document.getElementById('thread');if(t)t.scrollTop=t.scrollHeight;</script>
<?php
    require_once __DIR__ . '/includes/crm-footer.php';
    exit;
}

// ── ACTIVITY FEED (default) ──────────────────────────────────────────────────
$typeFilter = $_GET['type'] ?? 'all';
$where = "1=1";
if ($typeFilter !== 'all') $where = "t.type = " . $pdo->quote($typeFilter);

$events = $pdo->query("SELECT t.* FROM timeline t WHERE $where ORDER BY t.created_at DESC LIMIT 100")->fetchAll();
$notifications = $pdo->prepare("SELECT * FROM notifications WHERE user_id=? ORDER BY created_at DESC LIMIT 20");
$notifications->execute([$me['id']]); $notifications = $notifications->fetchAll();

$entityLinks = ['lead'=>'lead.php?id=','customer'=>'kund.php?id=','project'=>'projekt-detalj.php?id=','quote'=>'offert.php?id=','invoice'=>'faktura.php?id='];
$entityLabels = ['lead'=>'Lead','customer'=>'Kund','project'=>'Projekt','quote'=>'Offert','invoice'=>'Faktura','supplier'=>'Leverantör'];
$typeLabels = ['note'=>'Anteckning','call'=>'Samtal','email'=>'E-post','sms'=>'SMS','meeting'=>'Möte','status'=>'Statusändring','system'=>'System'];

// Unread portal messages count
$portalUnread = $pdo->query("SELECT COUNT(*) FROM portal_messages WHERE sender_type='customer' AND read_at IS NULL")->fetchColumn();
?>

<div class="topbar">
  <div>
    <h1>Kommunikationscenter</h1>
    <div class="topbar__sub">All kommunikation och aktivitet samlat på ett ställe</div>
  </div>
</div>

<?php flash(); ?>

<div class="tabs" style="margin-bottom:16px">
  <a href="?view=activity" class="tab active">Aktivitetsflöde</a>
  <a href="?view=portal" class="tab">Kundportal<?php if ($portalUnread): ?> <span class="badge" style="background:var(--blue);color:#fff;font-size:10px;padding:2px 6px;margin-left:4px"><?= $portalUnread ?></span><?php endif; ?></a>
</div>

<div class="tabs" style="margin-bottom:0">
  <a href="?view=activity&type=all" class="tab <?= $typeFilter==='all'?'active':'' ?>">Allt</a>
  <?php foreach ($typeLabels as $k => $lbl): ?>
  <a href="?view=activity&type=<?= $k ?>" class="tab <?= $typeFilter===$k?'active':'' ?>"><?= e($lbl) ?></a>
  <?php endforeach; ?>
</div>

<div class="detail-grid">
  <div class="card card--pad">
    <h3 style="font-size:14.5px;margin-bottom:18px">Aktivitetsflöde</h3>
    <div class="timeline">
      <?php if (!$events): ?><p style="font-size:13px;color:var(--gray)">Ingen aktivitet.</p><?php endif; ?>
      <?php foreach ($events as $ev): ?>
      <div class="tl-item">
        <div class="tl-dot <?= $ev['type']==='system'?'gray':($ev['type']==='status'?'amber':'') ?>"></div>
        <div class="tl-title">
          <?= e($ev['title']) ?>
          <a href="<?= ($entityLinks[$ev['entity_type']] ?? '#') . $ev['entity_id'] ?>" style="font-size:11px;font-weight:600;color:var(--blue);margin-left:6px">
            <?= e($entityLabels[$ev['entity_type']] ?? $ev['entity_type']) ?> →
          </a>
        </div>
        <?php if ($ev['body']): ?><div class="tl-body"><?= e(mb_strimwidth($ev['body'], 0, 160, '…')) ?></div><?php endif; ?>
        <div class="tl-meta"><?= e($typeLabels[$ev['type']] ?? $ev['type']) ?> · <?= e(user_name($ev['created_by'])) ?> · <?= time_ago($ev['created_at']) ?></div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>

  <div class="card card--pad">
    <h3 style="font-size:14.5px;margin-bottom:14px">Mina notiser</h3>
    <?php if (!$notifications): ?><p style="font-size:13px;color:var(--gray)">Inga notiser.</p><?php endif; ?>
    <?php foreach ($notifications as $n): ?>
    <a href="<?= e($n['link'] ?: '#') ?>" style="display:block;padding:10px 0;border-bottom:1px solid #F3F4F6">
      <div style="font-size:13px;font-weight:550"><?= e($n['title']) ?></div>
      <?php if ($n['body']): ?><div style="font-size:12px;color:var(--gray);margin-top:2px"><?= e($n['body']) ?></div><?php endif; ?>
      <div style="font-size:11px;color:var(--gray-lt);margin-top:3px"><?= time_ago($n['created_at']) ?></div>
    </a>
    <?php endforeach; ?>
  </div>
</div>

<?php require_once __DIR__ . '/includes/crm-footer.php'; ?>
