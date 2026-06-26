<?php
/**
 * CRM — E-post (compose & send emails to customers, suppliers, or any address; history log)
 */
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/mailer.php';
$me = require_login();
$pdo = db();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $action = $_POST['action'] ?? '';

    if ($action === 'send') {
        $toEmail = trim($_POST['to_email'] ?? '');
        $toName  = trim($_POST['to_name'] ?? '');
        $subject = trim($_POST['subject'] ?? '');
        $bodyRaw = trim($_POST['body'] ?? '');
        $entityType = $_POST['entity_type'] ?? '';
        $entityId = (int)($_POST['entity_id'] ?? 0);

        if (!filter_var($toEmail, FILTER_VALIDATE_EMAIL)) {
            flash('Ogiltig mottagaradress.', 'error');
        } elseif ($subject === '' || $bodyRaw === '') {
            flash('Ämne och meddelande krävs.', 'error');
        } else {
            $bodyHtml = nl2br(htmlspecialchars($bodyRaw, ENT_QUOTES, 'UTF-8'));
            $ok = crm_send_mail($toEmail, $toName ?: $toEmail, $subject, '<p>' . $bodyHtml . '</p>', $entityType, $entityId);
            $pdo->prepare("INSERT INTO emails (to_email, to_name, entity_type, entity_id, subject, body, status, sent_by) VALUES (?,?,?,?,?,?,?,?)")
                ->execute([$toEmail, $toName, $entityType, $entityId, $subject, $bodyRaw, $ok ? 'sent' : 'failed', $me['id']]);
            audit('email_send', $entityType, $entityId, $subject);
            flash($ok ? 'E-post skickad.' : 'E-post kunde inte skickas (kontrollera SMTP-inställningar).', $ok ? 'success' : 'error');
        }
        header('Location: email.php'); exit;
    }
}

$emails = $pdo->query("
    SELECT e.*, u.name AS sender_name
    FROM emails e LEFT JOIN users u ON u.id = e.sent_by
    ORDER BY e.created_at DESC LIMIT 100
")->fetchAll();

$customers = $pdo->query("SELECT id, name, email FROM customers WHERE email IS NOT NULL AND email != '' ORDER BY name")->fetchAll();
$suppliers = $pdo->query("SELECT id, company AS name, email FROM suppliers WHERE email IS NOT NULL AND email != '' ORDER BY company")->fetchAll();

$crm_title = 'E-post';
$crm_page  = 'email';
require_once __DIR__ . '/includes/crm-header.php';
?>
<div class="topbar">
  <div>
    <h1>E-post</h1>
    <div class="topbar__sub"><?= count($emails) ?> skickade · skicka till kunder, leverantörer eller valfri adress</div>
  </div>
  <div class="topbar__actions">
    <button class="btn btn--primary" onclick="openModal('emailModal')">+ Nytt meddelande</button>
  </div>
</div>

<?php flash(); ?>

<div class="card" style="overflow:hidden">
  <table class="data">
    <thead><tr><th>Mottagare</th><th>Ämne</th><th>Status</th><th>Skickat av</th><th>Datum</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($emails as $em): ?>
    <tr style="cursor:pointer" onclick="viewEmail(<?= htmlspecialchars(json_encode($em), ENT_QUOTES) ?>)">
      <td>
        <div style="font-weight:550;font-size:13px"><?= e($em['to_name'] ?: $em['to_email']) ?></div>
        <div style="font-size:11.5px;color:var(--gray)"><?= e($em['to_email']) ?></div>
      </td>
      <td style="font-size:13px"><?= e($em['subject']) ?></td>
      <td><span class="badge-<?= $em['status']==='sent' ? 'success' : 'danger' ?>" style="padding:3px 9px;border-radius:20px;font-size:11.5px"><?= $em['status']==='sent' ? 'Skickat' : 'Misslyckades' ?></span></td>
      <td style="font-size:12.5px;color:var(--gray)"><?= e($em['sender_name'] ?? '—') ?></td>
      <td style="font-size:12px;color:var(--gray);white-space:nowrap"><?= e(substr($em['created_at'],0,16)) ?></td>
      <td><button class="btn btn--ghost btn--sm" onclick="event.stopPropagation();quickReply('<?= e($em['to_email']) ?>','<?= e($em['to_name']) ?>')">Svara</button></td>
    </tr>
    <?php endforeach; ?>
    <?php if (!$emails): ?><tr><td colspan="6" style="padding:24px;color:var(--gray);font-size:13px">Inga e-postmeddelanden skickade ännu.</td></tr><?php endif; ?>
    </tbody>
  </table>
</div>

<!-- COMPOSE MODAL -->
<div class="modal-bg" id="emailModal">
  <div class="modal" style="max-width:600px">
    <h3>Nytt meddelande</h3>
    <form method="post">
      <?= csrf_field() ?><input type="hidden" name="action" value="send">
      <div class="fg"><label>Snabbval mottagare (valfritt)</label>
        <select class="fs" id="quickPick" onchange="quickPickFill(this)">
          <option value="">— Skriv egen adress nedan —</option>
          <optgroup label="Kunder">
            <?php foreach ($customers as $c): ?>
            <option value="<?= e($c['email']) ?>" data-name="<?= e($c['name']) ?>" data-type="customer" data-id="<?= $c['id'] ?>"><?= e($c['name']) ?> (<?= e($c['email']) ?>)</option>
            <?php endforeach; ?>
          </optgroup>
          <optgroup label="Leverantörer">
            <?php foreach ($suppliers as $s): ?>
            <option value="<?= e($s['email']) ?>" data-name="<?= e($s['name']) ?>" data-type="supplier" data-id="<?= $s['id'] ?>"><?= e($s['name']) ?> (<?= e($s['email']) ?>)</option>
            <?php endforeach; ?>
          </optgroup>
        </select>
      </div>
      <div class="frow">
        <div class="fg"><label>Till (e-post) *</label><input class="fi" type="email" name="to_email" id="f_to_email" required></div>
        <div class="fg"><label>Namn</label><input class="fi" name="to_name" id="f_to_name"></div>
      </div>
      <input type="hidden" name="entity_type" id="f_entity_type" value="">
      <input type="hidden" name="entity_id" id="f_entity_id" value="">
      <div class="fg"><label>Ämne *</label><input class="fi" name="subject" id="f_subject" required></div>
      <div class="fg"><label>Meddelande *</label><textarea class="fi" name="body" id="f_body" rows="8" required></textarea></div>
      <div style="display:flex;gap:10px;justify-content:flex-end">
        <button type="button" class="btn btn--ghost" onclick="closeModal('emailModal')">Avbryt</button>
        <button class="btn btn--primary">Skicka</button>
      </div>
    </form>
  </div>
</div>

<!-- VIEW MODAL -->
<div class="modal-bg" id="viewEmailModal">
  <div class="modal" style="max-width:600px">
    <h3 id="ve_subject"></h3>
    <p style="font-size:12.5px;color:var(--gray);margin-bottom:16px">Till: <span id="ve_to"></span></p>
    <div id="ve_body" style="font-size:14px;line-height:1.6;white-space:pre-wrap;background:#F9FAFB;border-radius:10px;padding:14px 16px;max-height:340px;overflow-y:auto"></div>
    <div style="display:flex;justify-content:flex-end;margin-top:16px">
      <button type="button" class="btn btn--ghost" onclick="closeModal('viewEmailModal')">Stäng</button>
    </div>
  </div>
</div>

<script>
function quickPickFill(sel) {
  const opt = sel.selectedOptions[0];
  if (!opt || !opt.value) return;
  document.getElementById('f_to_email').value = opt.value;
  document.getElementById('f_to_name').value = opt.dataset.name || '';
  document.getElementById('f_entity_type').value = opt.dataset.type || '';
  document.getElementById('f_entity_id').value = opt.dataset.id || '';
}
function quickReply(email, name) {
  document.getElementById('f_to_email').value = email;
  document.getElementById('f_to_name').value = name;
  document.getElementById('f_subject').value = '';
  document.getElementById('f_body').value = '';
  openModal('emailModal');
}
function viewEmail(em) {
  document.getElementById('ve_subject').textContent = em.subject;
  document.getElementById('ve_to').textContent = (em.to_name ? em.to_name + ' ' : '') + '<' + em.to_email + '>';
  document.getElementById('ve_body').textContent = em.body;
  openModal('viewEmailModal');
}
</script>

<?php require_once __DIR__ . '/includes/crm-footer.php'; ?>
