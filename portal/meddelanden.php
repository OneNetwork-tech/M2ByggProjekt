<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/layout.php';
require_once dirname(__DIR__) . '/crm/includes/mailer.php';
$pu  = portal_require();
$cid = (int)$pu['customer_id'];

// Get customer's projects for thread selector
$projects = portal_projects($cid);
$pids     = array_column($projects, 'id');

$activePid = isset($_GET['project']) ? (int)$_GET['project'] : ($pids[0] ?? 0);

// Send message
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['body'])) {
    $pid  = (int)($_POST['project_id'] ?? 0);
    $body = trim($_POST['body']);
    if (in_array($pid, $pids) && $body !== '') {
        db()->prepare(
            "INSERT INTO portal_messages (project_id, sender_type, sender_id, body) VALUES (?,?,?,?)"
        )->execute([$pid, 'customer', $cid, $body]);
        // Notify CRM staff in-app + email
        notify_role_email('support', 'Nytt kundmeddelande',
            $pu['name'] . ' skickade ett meddelande: "' . mb_strimwidth($body, 0, 100, '…') . '"',
            'meddelanden.php?view=portal&thread=' . $pid, 'project', $pid);
        header('Location: /portal/meddelanden.php?project=' . $pid);
        exit;
    }
}

// Load messages for active project
$messages = [];
if ($activePid) {
    // Mark staff messages as read
    db()->prepare(
        "UPDATE portal_messages SET read_at = " . now_expr() . "
         WHERE project_id = ? AND sender_type = 'staff' AND read_at IS NULL"
    )->execute([$activePid]);

    $s = db()->prepare(
        "SELECT m.*,
                CASE m.sender_type WHEN 'customer' THEN ? ELSE 'M2 Bygg Team' END AS sender_name,
                u.name AS staff_name
         FROM portal_messages m
         LEFT JOIN users u ON u.id = m.sender_id AND m.sender_type = 'staff'
         WHERE m.project_id = ? ORDER BY m.created_at ASC"
    );
    $s->execute([$pu['name'], $activePid]);
    $messages = $s->fetchAll();
}

// Find active project label
$activeProjectTitle = '';
foreach ($projects as $p) {
    if ($p['id'] === $activePid) { $activeProjectTitle = $p['title']; break; }
}

portal_head('Meddelanden', $pu);
portal_nav('/meddelanden.php');
?>
<main class="portal-main">
  <div class="portal-page-title"><h1>Meddelanden</h1><p>Direktkommunikation med M2 Bygg Team.</p></div>

  <?php if ($projects): ?>
  <!-- Project tabs -->
  <?php if (count($projects) > 1): ?>
  <div style="display:flex;gap:8px;margin-bottom:20px;flex-wrap:wrap">
    <?php foreach ($projects as $p): ?>
    <a href="?project=<?= $p['id'] ?>" class="btn btn--<?= $p['id'] === $activePid ? 'primary' : 'outline' ?> btn--sm">
      <?= e($p['title']) ?>
    </a>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>

  <div class="card">
    <div class="card-header">
      <h3>
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" style="width:18px;height:18px;display:inline;margin-right:6px;vertical-align:-3px"><path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"/></svg>
        <?= e($activeProjectTitle ?: 'Välj projekt') ?>
      </h3>
    </div>

    <!-- Messages -->
    <div class="message-thread" id="thread">
      <?php if ($messages): ?>
        <?php foreach ($messages as $msg):
          $isMe = $msg['sender_type'] === 'customer';
          $name = $isMe ? $pu['name'] : ($msg['staff_name'] ?: 'M2 Bygg Team');
          $initials = strtoupper(implode('', array_map(fn($w)=>$w[0], array_slice(explode(' ',$name),0,2))));
        ?>
        <div class="message <?= $isMe ? 'message--mine' : 'message--theirs' ?>">
          <div class="message__avatar"><?= $initials ?></div>
          <div>
            <div class="message__bubble"><?= nl2br(e($msg['body'])) ?></div>
            <div class="message__meta"><?= e($name) ?> · <?= e($msg['created_at']) ?></div>
          </div>
        </div>
        <?php endforeach; ?>
      <?php else: ?>
        <p style="color:var(--steel);font-size:.875rem;text-align:center;padding:24px 0">Inga meddelanden ännu. Skicka ett meddelande nedan!</p>
      <?php endif; ?>
    </div>

    <!-- Send form -->
    <form method="post" style="margin-top:16px;border-top:1px solid var(--border);padding-top:16px;display:flex;gap:10px">
      <input type="hidden" name="project_id" value="<?= $activePid ?>">
      <textarea name="body" class="form-control" placeholder="Skriv ett meddelande…" rows="2" style="flex:1;resize:none" required></textarea>
      <button type="submit" class="btn btn--primary" style="align-self:flex-end">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:16px;height:16px"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22,2 15,22 11,13 2,9 22,2"/></svg>
        Skicka
      </button>
    </form>
  </div>

  <?php else: ?>
  <div class="card" style="text-align:center;padding:48px">
    <p style="color:var(--steel)">Inga projekt kopplade till ditt konto ännu.</p>
  </div>
  <?php endif; ?>
</main>
<script>
// Scroll thread to bottom
var t = document.getElementById('thread');
if (t) t.scrollTop = t.scrollHeight;
</script>
<?php portal_foot(); ?>
