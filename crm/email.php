<?php
/**
 * CRM — E-post: fungerar som en enkel e-postklient. Flera konton kan läggas till i
 * Inställningar; varje konto kan både skicka (SMTP) och ta emot (IMAP, om PHP:s imap-
 * tillägg är aktiverat på servern). "Skickat" loggas alltid lokalt i databasen oavsett.
 */
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/mailer.php';
require_once __DIR__ . '/includes/imapclient.php';
$me = require_login();
$pdo = db();

// Auto-provision a default account from config-level SMTP_* constants the first time
// this page loads, so the system can send mail immediately without requiring a manual
// Inställningar step — only once a real password has been configured (not the 'PASSWORD'
// placeholder), via crm/config.local.php or send/config.local.php (SMTP_PASS_OVERRIDE).
if ((int)$pdo->query("SELECT COUNT(*) FROM email_accounts")->fetchColumn() === 0 && SMTP_PASS !== 'PASSWORD') {
    $pdo->prepare(
        "INSERT INTO email_accounts (label, host, port, encryption, username, password, from_email, from_name, imap_host, imap_port, imap_encryption, is_default, active)
         VALUES (?,?,?,?,?,?,?,?,?,?,?,1,1)"
    )->execute(['Standard (noreply@m2team.se)', SMTP_HOST, SMTP_PORT, 'ssl', SMTP_USER, SMTP_PASS, SMTP_FROM, SMTP_FROM_NAME, SMTP_HOST, 993, 'ssl']);
}

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
        $accountId = (int)($_POST['account_id'] ?? 0) ?: null;

        if (!filter_var($toEmail, FILTER_VALIDATE_EMAIL)) {
            flash('Ogiltig mottagaradress.', 'error');
        } elseif ($subject === '' || $bodyRaw === '') {
            flash('Ämne och meddelande krävs.', 'error');
        } else {
            $bodyHtml = nl2br(htmlspecialchars($bodyRaw, ENT_QUOTES, 'UTF-8'));
            $ok = crm_send_mail($toEmail, $toName ?: $toEmail, $subject, '<p>' . $bodyHtml . '</p>', $entityType, $entityId, null, null, $accountId);
            $pdo->prepare("INSERT INTO emails (to_email, to_name, entity_type, entity_id, subject, body, status, sent_by) VALUES (?,?,?,?,?,?,?,?)")
                ->execute([$toEmail, $toName, $entityType, $entityId, $subject, $bodyRaw, $ok ? 'sent' : 'failed', $me['id']]);
            audit('email_send', $entityType, $entityId, $subject);
            flash($ok ? 'E-post skickad.' : 'E-post kunde inte skickas (kontrollera SMTP-inställningar).', $ok ? 'success' : 'error');
        }
        header('Location: email.php?tab=' . ($_POST['return_tab'] ?? 'sent')); exit;
    }
}

$accounts = $pdo->query("SELECT * FROM email_accounts WHERE active = 1 ORDER BY is_default DESC, id ASC")->fetchAll();
$tab = $_GET['tab'] ?? ($accounts ? 'inbox' : 'sent');
$accountId = (int)($_GET['account'] ?? ($accounts[0]['id'] ?? 0));
$selectedAccount = null;
foreach ($accounts as $a) if ((int)$a['id'] === $accountId) { $selectedAccount = $a; break; }

$inboxMessages = [];
$inboxError = '';
$viewedMessage = null;
if ($tab === 'inbox' && $selectedAccount) {
    if (!imap_ext_available()) {
        $inboxError = 'PHP:s imap-tillägg är inte aktiverat på servern. Aktivera det via cPanel → MultiPHP Manager/Select PHP Extensions för att ta emot e-post i klienten. Att skicka fungerar oavsett.';
    } else {
        $inboxMessages = imap_fetch_inbox($selectedAccount, 50);
        if (!$inboxMessages && imap_account_last_error() !== 'Okänt IMAP-fel') {
            $inboxError = 'Kunde inte ansluta till inkorgen: ' . imap_account_last_error();
        }
        $viewMsgno = (int)($_GET['view'] ?? 0);
        if ($viewMsgno) $viewedMessage = imap_fetch_message($selectedAccount, $viewMsgno);
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
    <div class="topbar__sub"><?= count($accounts) ?> konto(n) · <?= count($emails) ?> skickade totalt</div>
  </div>
  <div class="topbar__actions">
    <?php if (!$accounts): ?>
    <a href="installningar.php" class="btn btn--ghost">Lägg till e-postkonto</a>
    <?php endif; ?>
    <button class="btn btn--primary" onclick="openCompose()">+ Nytt meddelande</button>
  </div>
</div>

<?php flash(); ?>

<div class="tabs" style="margin-bottom:16px">
  <a href="?tab=inbox<?= $accountId ? "&account=$accountId" : '' ?>" class="tab <?= $tab==='inbox'?'active':'' ?>">Inkorg</a>
  <a href="?tab=sent" class="tab <?= $tab==='sent'?'active':'' ?>">Skickat</a>
</div>

<?php if ($tab === 'inbox'): ?>

  <?php if (count($accounts) > 1): ?>
  <form method="get" style="margin-bottom:14px">
    <input type="hidden" name="tab" value="inbox">
    <select class="fs" name="account" onchange="this.form.submit()" style="max-width:320px">
      <?php foreach ($accounts as $a): ?>
      <option value="<?= $a['id'] ?>" <?= $a['id']==$accountId?'selected':'' ?>><?= e($a['label']) ?> (<?= e($a['from_email']) ?>)</option>
      <?php endforeach; ?>
    </select>
  </form>
  <?php endif; ?>

  <?php if (!$accounts): ?>
  <div class="card card--pad" style="text-align:center;color:var(--gray);padding:48px">
    Inga e-postkonton konfigurerade ännu. <a href="installningar.php">Lägg till ett i Inställningar</a> för att kunna skicka och ta emot e-post här.
  </div>
  <?php elseif ($inboxError): ?>
  <div class="card card--pad" style="color:var(--amber);background:#FFFBEB;border-color:#FDE68A"><?= e($inboxError) ?></div>
  <?php else: ?>

  <div style="display:grid;grid-template-columns:360px 1fr;gap:16px;align-items:start">
    <div class="card" style="overflow:hidden;max-height:640px;overflow-y:auto">
      <?php if (!$inboxMessages): ?>
      <div style="padding:24px;color:var(--gray);font-size:13px">Inkorgen är tom.</div>
      <?php endif; ?>
      <?php foreach ($inboxMessages as $m): ?>
      <a href="?tab=inbox&account=<?= $accountId ?>&view=<?= $m['msgno'] ?>"
         style="display:block;padding:12px 16px;border-bottom:1px solid var(--border);text-decoration:none;color:inherit;<?= ($viewedMessage && (int)($_GET['view']??0)===$m['msgno']) ? 'background:#F0F6FF' : '' ?>">
        <div style="display:flex;justify-content:space-between;gap:8px">
          <div style="font-weight:<?= $m['seen']?'500':'700' ?>;font-size:13px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:200px"><?= e($m['from']) ?></div>
          <div style="font-size:11px;color:var(--gray-lt);white-space:nowrap"><?= $m['ts'] ? date('j M', $m['ts']) : '' ?></div>
        </div>
        <div style="font-size:13px;color:var(--ink-soft);overflow:hidden;text-overflow:ellipsis;white-space:nowrap"><?= e($m['subject']) ?></div>
      </a>
      <?php endforeach; ?>
    </div>

    <div class="card card--pad" style="min-height:300px">
      <?php if ($viewedMessage): ?>
        <h3 style="margin-bottom:6px"><?= e($viewedMessage['subject']) ?></h3>
        <div style="font-size:12.5px;color:var(--gray);margin-bottom:16px">
          Från: <?= e($viewedMessage['from']) ?> · <?= e($viewedMessage['date']) ?>
        </div>
        <div style="font-size:14px;line-height:1.6;border-top:1px solid var(--border);padding-top:16px;max-height:420px;overflow-y:auto">
          <?php if ($viewedMessage['body_html']): ?>
            <iframe sandbox="" srcdoc="<?= htmlspecialchars($viewedMessage['body_html'], ENT_QUOTES) ?>" style="width:100%;height:380px;border:none"></iframe>
          <?php else: ?>
            <div style="white-space:pre-wrap"><?= e($viewedMessage['body_text']) ?></div>
          <?php endif; ?>
        </div>
        <div style="margin-top:14px">
          <button class="btn btn--ghost btn--sm" onclick='openCompose(<?= json_encode([
            "to_email" => preg_match('/<(.+)>/', $viewedMessage['from'], $mm) ? $mm[1] : $viewedMessage['from'],
            "to_name" => trim(preg_replace('/<.+>/', '', $viewedMessage['from'])),
            "subject" => (stripos($viewedMessage['subject'], 're:') === 0 ? '' : 'Re: ') . $viewedMessage['subject'],
            "account_id" => $accountId,
            "return_tab" => "inbox",
          ]) ?>)'>Svara</button>
        </div>
      <?php else: ?>
        <div style="color:var(--gray-lt);font-size:13px;text-align:center;padding:60px 0">Välj ett meddelande till vänster för att läsa det.</div>
      <?php endif; ?>
    </div>
  </div>

  <?php endif; ?>

<?php else: ?>

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
        <td><button class="btn btn--ghost btn--sm" onclick="event.stopPropagation();openCompose({to_email:'<?= e($em['to_email']) ?>',to_name:'<?= e($em['to_name']) ?>'})">Svara</button></td>
      </tr>
      <?php endforeach; ?>
      <?php if (!$emails): ?><tr><td colspan="6" style="padding:24px;color:var(--gray);font-size:13px">Inga e-postmeddelanden skickade ännu.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>

<?php endif; ?>

<!-- COMPOSE MODAL -->
<div class="modal-bg" id="emailModal">
  <div class="modal" style="max-width:600px">
    <h3>Nytt meddelande</h3>
    <form method="post">
      <?= csrf_field() ?><input type="hidden" name="action" value="send">
      <input type="hidden" name="return_tab" id="f_return_tab" value="sent">
      <?php if (count($accounts) > 1): ?>
      <div class="fg"><label>Från</label>
        <select class="fs" name="account_id" id="f_account_id">
          <?php foreach ($accounts as $a): ?>
          <option value="<?= $a['id'] ?>"><?= e($a['label']) ?> (<?= e($a['from_email']) ?>)</option>
          <?php endforeach; ?>
        </select>
      </div>
      <?php elseif ($accounts): ?>
      <input type="hidden" name="account_id" id="f_account_id" value="<?= $accounts[0]['id'] ?>">
      <?php endif; ?>
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

<!-- VIEW MODAL (sent log) -->
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
function openCompose(prefill) {
  prefill = prefill || {};
  document.getElementById('f_to_email').value = prefill.to_email || '';
  document.getElementById('f_to_name').value = prefill.to_name || '';
  document.getElementById('f_subject').value = prefill.subject || '';
  document.getElementById('f_body').value = '';
  document.getElementById('f_return_tab').value = prefill.return_tab || 'sent';
  const acctSel = document.getElementById('f_account_id');
  if (acctSel && prefill.account_id) acctSel.value = prefill.account_id;
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
