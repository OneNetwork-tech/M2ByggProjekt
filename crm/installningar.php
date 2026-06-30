<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/mailer.php';
$me = require_role([]); // super_admin only
$pdo = db();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $action = $_POST['action'] ?? '';

    if ($action === 'create_email_account') {
        $label    = trim($_POST['label'] ?? '');
        $host     = trim($_POST['host'] ?? '');
        $port     = (int)($_POST['port'] ?? 465);
        $enc      = ($_POST['encryption'] ?? 'ssl') === 'tls' ? 'tls' : 'ssl';
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        $fromMail = trim($_POST['from_email'] ?? '');
        $fromName = trim($_POST['from_name'] ?? 'M2 Bygg Team AB');
        $imapHost = trim($_POST['imap_host'] ?? '') ?: $host;
        $imapPort = (int)($_POST['imap_port'] ?? 993);
        $imapEnc  = ($_POST['imap_encryption'] ?? 'ssl') === 'tls' ? 'tls' : 'ssl';
        $makeDefault = !empty($_POST['is_default']);

        if ($label === '' || $host === '' || $username === '' || $password === '' || !filter_var($fromMail, FILTER_VALIDATE_EMAIL)) {
            flash('Fyll i alla obligatoriska fält med en giltig avsändaradress.', 'error');
        } else {
            if ($makeDefault) $pdo->exec("UPDATE email_accounts SET is_default = 0");
            $pdo->prepare("INSERT INTO email_accounts (label, host, port, encryption, username, password, from_email, from_name, imap_host, imap_port, imap_encryption, is_default) VALUES (?,?,?,?,?,?,?,?,?,?,?,?)")
                ->execute([$label, $host, $port, $enc, $username, $password, $fromMail, $fromName, $imapHost, $imapPort, $imapEnc, $makeDefault ? 1 : 0]);
            audit('email_account_create', 'email_account', (int)$pdo->lastInsertId(), $label);
            flash('E-postkonto tillagt.');
        }
        header('Location: installningar.php'); exit;
    }

    if ($action === 'update_email_account') {
        $id       = (int)($_POST['id'] ?? 0);
        $label    = trim($_POST['label'] ?? '');
        $host     = trim($_POST['host'] ?? '');
        $port     = (int)($_POST['port'] ?? 465);
        $enc      = ($_POST['encryption'] ?? 'ssl') === 'tls' ? 'tls' : 'ssl';
        $username = trim($_POST['username'] ?? '');
        $fromMail = trim($_POST['from_email'] ?? '');
        $fromName = trim($_POST['from_name'] ?? 'M2 Bygg Team AB');
        $imapHost = trim($_POST['imap_host'] ?? '') ?: $host;
        $imapPort = (int)($_POST['imap_port'] ?? 993);
        $imapEnc  = ($_POST['imap_encryption'] ?? 'ssl') === 'tls' ? 'tls' : 'ssl';
        $makeDefault = !empty($_POST['is_default']);

        if ($label === '' || $host === '' || $username === '' || !filter_var($fromMail, FILTER_VALIDATE_EMAIL)) {
            flash('Fyll i alla obligatoriska fält med en giltig avsändaradress.', 'error');
        } else {
            if ($makeDefault) $pdo->exec("UPDATE email_accounts SET is_default = 0");
            if (!empty($_POST['password'])) {
                $pdo->prepare("UPDATE email_accounts SET label=?, host=?, port=?, encryption=?, username=?, password=?, from_email=?, from_name=?, imap_host=?, imap_port=?, imap_encryption=?, is_default=? WHERE id=?")
                    ->execute([$label, $host, $port, $enc, $username, $_POST['password'], $fromMail, $fromName, $imapHost, $imapPort, $imapEnc, $makeDefault ? 1 : 0, $id]);
            } else {
                $pdo->prepare("UPDATE email_accounts SET label=?, host=?, port=?, encryption=?, username=?, from_email=?, from_name=?, imap_host=?, imap_port=?, imap_encryption=?, is_default=? WHERE id=?")
                    ->execute([$label, $host, $port, $enc, $username, $fromMail, $fromName, $imapHost, $imapPort, $imapEnc, $makeDefault ? 1 : 0, $id]);
            }
            audit('email_account_update', 'email_account', $id, $label);
            flash('E-postkonto uppdaterat.');
        }
        header('Location: installningar.php'); exit;
    }

    if ($action === 'delete_email_account') {
        $id = (int)($_POST['id'] ?? 0);
        $pdo->prepare("DELETE FROM email_accounts WHERE id = ?")->execute([$id]);
        audit('email_account_delete', 'email_account', $id);
        flash('E-postkonto borttaget.');
        header('Location: installningar.php'); exit;
    }

    if ($action === 'test_email_account') {
        $id = (int)($_POST['id'] ?? 0);
        $sent = crm_send_mail($me['email'], $me['name'], 'Testmejl från M2 Platform', '<p>Det här är ett testmejl för att bekräfta att e-postkontot fungerar korrekt.</p>', 'email_account', $id, null, null, $id ?: null);
        flash($sent ? 'Testmejl skickat till ' . $me['email'] . '.' : 'Testmejlet kunde inte skickas — kontrollera uppgifterna och se utskickslogen nedan.', $sent ? 'success' : 'error');
        header('Location: installningar.php'); exit;
    }
}

$crm_title = 'Inställningar';
$crm_page  = 'installningar';
require_once __DIR__ . '/includes/crm-header.php';

// Audit log
$logs = $pdo->query("SELECT a.*, u.name AS user_name FROM audit_logs a LEFT JOIN users u ON u.id=a.user_id ORDER BY a.created_at DESC LIMIT 60")->fetchAll();

// DB stats
$stats = [];
foreach (['leads','customers','quotes','projects','invoices','suppliers','timeline','users'] as $t) {
    $stats[$t] = $pdo->query("SELECT COUNT(*) FROM $t")->fetchColumn();
}

// Notification send log
$notifLog = $pdo->query("SELECT * FROM notifications_log ORDER BY created_at DESC LIMIT 40")->fetchAll();
$notifFailCount = $pdo->query("SELECT COUNT(*) FROM notifications_log WHERE status='failed'")->fetchColumn();

// Email accounts
$emailAccounts = $pdo->query("SELECT * FROM email_accounts ORDER BY is_default DESC, id ASC")->fetchAll();
?>

<div class="topbar">
  <div>
    <h1>Inställningar</h1>
    <div class="topbar__sub">System, granskningslogg och databasinfo</div>
  </div>
</div>

<?php flash(); ?>

<div class="card card--pad" style="margin-bottom:20px">
  <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:14px">
    <h3 style="font-size:14.5px">E-postkonton (SMTP)</h3>
    <button class="btn btn--primary btn--sm" onclick="openNewEmailAccount()">+ Lägg till konto</button>
  </div>
  <p style="font-size:12.5px;color:var(--gray-lt);margin-bottom:14px">Lägg till fler än ett konto (t.ex. ett huvudkonto hos mail.m2team.se och ett separat konto via Outlook/Microsoft 365). Kontot markerat "Standard" används för alla automatiska utskick om inget annat anges.</p>
  <div class="table-wrap">
    <table class="data">
      <thead><tr><th>Etikett</th><th>Värd</th><th>Användarnamn</th><th>Avsändare</th><th>Standard</th><th></th></tr></thead>
      <tbody>
        <?php if (!$emailAccounts): ?>
        <tr><td colspan="6" style="color:var(--gray);font-size:13px">Inga e-postkonton konfigurerade — använder standardvärden i koden (mail.m2team.se).</td></tr>
        <?php endif; ?>
        <?php foreach ($emailAccounts as $acc): ?>
        <tr style="cursor:default">
          <td style="font-size:13px;font-weight:500"><?= e($acc['label']) ?></td>
          <td style="font-size:12.5px;color:var(--gray)"><?= e($acc['host']) ?>:<?= (int)$acc['port'] ?> (<?= strtoupper(e($acc['encryption'])) ?>)</td>
          <td style="font-size:12.5px;color:var(--gray)"><?= e($acc['username']) ?></td>
          <td style="font-size:12.5px;color:var(--gray)"><?= e($acc['from_name']) ?> &lt;<?= e($acc['from_email']) ?>&gt;</td>
          <td><?php if ($acc['is_default']): ?><span class="badge badge-success">Standard</span><?php endif; ?></td>
          <td style="white-space:nowrap;display:flex;gap:6px">
            <form method="post" style="display:inline">
              <?= csrf_field() ?>
              <input type="hidden" name="action" value="test_email_account">
              <input type="hidden" name="id" value="<?= (int)$acc['id'] ?>">
              <button class="btn btn--ghost btn--sm" type="submit">Testa</button>
            </form>
            <button class="btn btn--ghost btn--sm" type="button" onclick='openEditEmailAccount(<?= json_encode($acc) ?>)'>Redigera</button>
            <form method="post" style="display:inline" onsubmit="return confirm('Ta bort detta e-postkonto?')">
              <?= csrf_field() ?>
              <input type="hidden" name="action" value="delete_email_account">
              <input type="hidden" name="id" value="<?= (int)$acc['id'] ?>">
              <button class="btn btn--ghost btn--sm" type="submit" style="color:var(--red)">Ta bort</button>
            </form>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<div class="detail-grid">
  <div class="card card--pad">
    <h3 style="font-size:14.5px;margin-bottom:14px">Granskningslogg (Audit Log)</h3>
    <div class="table-wrap">
      <table class="data">
        <thead><tr><th>Tid</th><th>Användare</th><th>Händelse</th><th>Objekt</th></tr></thead>
        <tbody>
          <?php foreach ($logs as $l): ?>
          <tr style="cursor:default">
            <td style="font-size:12px;color:var(--gray);white-space:nowrap"><?= dt($l['created_at'], 'j M H:i') ?></td>
            <td style="font-size:12.5px"><?= e($l['user_name'] ?: 'System') ?></td>
            <td><span class="badge" style="background:#F3F4F6;color:var(--gray)"><?= e($l['action']) ?></span></td>
            <td style="font-size:12px;color:var(--gray)"><?= e($l['entity_type']) ?><?= $l['entity_id'] ? ' #' . $l['entity_id'] : '' ?> <?= e($l['detail']) ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <div class="card card--pad" style="margin-top:16px">
      <h3 style="font-size:14.5px;margin-bottom:14px">
        E-post/SMS-utskick
        <?php if ($notifFailCount): ?><span class="badge badge-warning" style="font-size:11px;margin-left:6px"><?= $notifFailCount ?> misslyckade</span><?php endif; ?>
      </h3>
      <div class="table-wrap">
        <table class="data">
          <thead><tr><th>Tid</th><th>Kanal</th><th>Mottagare</th><th>Ämne</th><th>Status</th></tr></thead>
          <tbody>
          <?php if (!$notifLog): ?><tr><td colspan="5" style="color:var(--gray);font-size:13px">Inga utskick ännu.</td></tr><?php endif; ?>
          <?php foreach ($notifLog as $n): ?>
          <tr style="cursor:default">
            <td style="font-size:12px;color:var(--gray);white-space:nowrap"><?= dt($n['created_at'], 'j M H:i') ?></td>
            <td><span class="badge" style="background:#F3F4F6;color:var(--gray)"><?= e($n['channel']) ?></span></td>
            <td style="font-size:12.5px"><?= e($n['recipient']) ?></td>
            <td style="font-size:12px;color:var(--gray);max-width:240px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap"><?= e($n['subject'] ?? '') ?></td>
            <td>
              <?php if ($n['status'] === 'sent'): ?><span class="badge badge-success">Skickat</span>
              <?php else: ?><span class="badge badge-danger" title="<?= e($n['error'] ?? '') ?>">Misslyckades</span><?php endif; ?>
            </td>
          </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <div style="display:flex;flex-direction:column;gap:16px">
    <div class="card card--pad">
      <h3 style="font-size:14.5px;margin-bottom:12px">Integrationer</h3>
      <div style="display:flex;flex-direction:column;gap:8px;font-size:13px">
        <div style="display:flex;justify-content:space-between"><span style="color:var(--gray)">SMTP e-post</span><strong style="color:<?= $emailAccounts ? 'var(--green)' : 'var(--amber)' ?>"><?= $emailAccounts ? count($emailAccounts) . ' konto(n)' : 'Standardlösenord' ?></strong></div>
        <div style="display:flex;justify-content:space-between"><span style="color:var(--gray)">SMS (46elks)</span><strong style="color:<?= defined('SMS_PROVIDER_USER') ? 'var(--green)' : 'var(--gray-lt)' ?>"><?= defined('SMS_PROVIDER_USER') ? 'Aktiverad' : 'Ej konfigurerad' ?></strong></div>
        <div style="display:flex;justify-content:space-between"><span style="color:var(--gray)">Stripe-betalning</span><strong style="color:<?= defined('STRIPE_SECRET_KEY') && STRIPE_SECRET_KEY !== '' ? 'var(--green)' : 'var(--gray-lt)' ?>"><?= defined('STRIPE_SECRET_KEY') && STRIPE_SECRET_KEY !== '' ? 'Aktiverad' : 'Ej konfigurerad' ?></strong></div>
      </div>
      <p style="font-size:11.5px;color:var(--gray-lt);margin-top:10px">E-postkonton hanteras ovan. SMS/Stripe konfigureras i crm/config.php.</p>
    </div>
    <div class="card card--pad">
      <h3 style="font-size:14.5px;margin-bottom:12px">Systeminfo</h3>
      <div style="display:flex;flex-direction:column;gap:8px;font-size:13px">
        <div style="display:flex;justify-content:space-between"><span style="color:var(--gray)">Version</span><strong><?= APP_VERSION ?></strong></div>
        <div style="display:flex;justify-content:space-between"><span style="color:var(--gray)">Databas</span><strong><?= strtoupper(DB_DRIVER) ?></strong></div>
        <div style="display:flex;justify-content:space-between"><span style="color:var(--gray)">PHP</span><strong><?= PHP_VERSION ?></strong></div>
        <div style="display:flex;justify-content:space-between"><span style="color:var(--gray)">Moms</span><strong><?= VAT_RATE * 100 ?>%</strong></div>
        <div style="display:flex;justify-content:space-between"><span style="color:var(--gray)">ROT-sats</span><strong><?= ROT_RATE * 100 ?>% (max 50 000 kr)</strong></div>
      </div>
    </div>
    <div class="card card--pad">
      <h3 style="font-size:14.5px;margin-bottom:12px">Databas</h3>
      <div style="display:flex;flex-direction:column;gap:7px;font-size:13px">
        <?php
        $labels = ['leads'=>'Leads','customers'=>'Kunder','quotes'=>'Offerter','projects'=>'Projekt','invoices'=>'Fakturor','suppliers'=>'Leverantörer','timeline'=>'Tidslinjehändelser','users'=>'Användare'];
        foreach ($stats as $t => $c): ?>
        <div style="display:flex;justify-content:space-between"><span style="color:var(--gray)"><?= $labels[$t] ?></span><strong><?= $c ?></strong></div>
        <?php endforeach; ?>
      </div>
    </div>
    <div class="card card--pad" style="background:#FFFBEB;border-color:#FDE68A">
      <h3 style="font-size:14px;color:var(--amber);margin-bottom:8px">⚠ Säkerhet vid driftsättning</h3>
      <ul style="font-size:12.5px;color:var(--ink-soft);padding-left:16px;line-height:1.8">
        <li>Byt admin-lösenordet (admin@m2team.se / admin123)</li>
        <li>Skydda /data/-mappen via .htaccess (ingår)</li>
        <li>Aktivera HTTPS via cPanel</li>
        <li>Byt till MySQL i config.php för hög volym</li>
      </ul>
    </div>
  </div>
</div>

<!-- ADD/EDIT EMAIL ACCOUNT MODAL -->
<div class="modal-bg" id="emailAccountModal">
  <div class="modal">
    <h3 id="emailAccountModalTitle">Lägg till e-postkonto</h3>
    <form method="post" id="emailAccountForm">
      <?= csrf_field() ?>
      <input type="hidden" name="action" id="emailAccountAction" value="create_email_account">
      <input type="hidden" name="id" id="emailAccountId" value="">
      <div class="frow">
        <div class="fg"><label>Etikett *</label><input class="fi" name="label" id="ea_label" placeholder="T.ex. Huvudkonto, Outlook-support" required></div>
        <div class="fg"><label>Avsändarnamn</label><input class="fi" name="from_name" id="ea_from_name" value="M2 Bygg Team AB"></div>
      </div>
      <div class="frow">
        <div class="fg"><label>SMTP-värd *</label><input class="fi" name="host" id="ea_host" placeholder="mail.m2team.se eller smtp.office365.com" required></div>
        <div class="fg"><label>Port *</label><input class="fi" type="number" name="port" id="ea_port" value="465" required></div>
      </div>
      <div class="frow">
        <div class="fg"><label>Kryptering</label>
          <select class="fs" name="encryption" id="ea_encryption">
            <option value="ssl">SSL (port 465)</option>
            <option value="tls">TLS/STARTTLS (port 587 — t.ex. Outlook/Microsoft 365)</option>
          </select>
        </div>
        <div class="fg"><label>Avsändaradress *</label><input class="fi" type="email" name="from_email" id="ea_from_email" placeholder="info@m2team.se" required></div>
      </div>
      <div class="frow">
        <div class="fg"><label>Användarnamn *</label><input class="fi" name="username" id="ea_username" placeholder="info@m2team.se" required></div>
        <div class="fg"><label>Lösenord <span id="ea_password_hint" style="color:var(--gray)">*</span></label><input class="fi" type="password" name="password" id="ea_password"></div>
      </div>
      <hr style="border:none;border-top:1px solid var(--border);margin:14px 0">
      <p style="font-size:12px;color:var(--gray);margin-bottom:10px">Inkommande (IMAP) — krävs för att ta emot e-post i Inkorgen. Lämna värd tomt för att återanvända SMTP-värden ovan.</p>
      <div class="frow">
        <div class="fg"><label>IMAP-värd</label><input class="fi" name="imap_host" id="ea_imap_host" placeholder="mail.m2team.se eller outlook.office365.com"></div>
        <div class="fg"><label>IMAP-port</label><input class="fi" type="number" name="imap_port" id="ea_imap_port" value="993"></div>
      </div>
      <div class="fg"><label>IMAP-kryptering</label>
        <select class="fs" name="imap_encryption" id="ea_imap_encryption">
          <option value="ssl">SSL (port 993)</option>
          <option value="tls">TLS/STARTTLS (port 143)</option>
        </select>
      </div>
      <div class="fg"><label style="display:flex;align-items:center;gap:8px;font-weight:500">
        <input type="checkbox" name="is_default" id="ea_is_default" value="1"> Använd som standardkonto för automatiska utskick
      </label></div>
      <div style="display:flex;gap:10px;justify-content:flex-end;margin-top:8px">
        <button type="button" class="btn btn--ghost" onclick="closeModal('emailAccountModal')">Avbryt</button>
        <button class="btn btn--primary">Spara</button>
      </div>
    </form>
  </div>
</div>

<script>
function openNewEmailAccount() {
  document.getElementById('emailAccountForm').reset();
  document.getElementById('emailAccountModalTitle').textContent = 'Lägg till e-postkonto';
  document.getElementById('emailAccountAction').value = 'create_email_account';
  document.getElementById('emailAccountId').value = '';
  document.getElementById('ea_password').required = true;
  document.getElementById('ea_password_hint').textContent = '*';
  document.getElementById('ea_imap_port').value = '993';
  document.getElementById('ea_imap_encryption').value = 'ssl';
  openModal('emailAccountModal');
}
function openEditEmailAccount(acc) {
  document.getElementById('emailAccountModalTitle').textContent = 'Redigera e-postkonto';
  document.getElementById('emailAccountAction').value = 'update_email_account';
  document.getElementById('emailAccountId').value = acc.id;
  document.getElementById('ea_label').value = acc.label;
  document.getElementById('ea_from_name').value = acc.from_name;
  document.getElementById('ea_host').value = acc.host;
  document.getElementById('ea_port').value = acc.port;
  document.getElementById('ea_encryption').value = acc.encryption;
  document.getElementById('ea_from_email').value = acc.from_email;
  document.getElementById('ea_username').value = acc.username;
  document.getElementById('ea_password').value = '';
  document.getElementById('ea_password').required = false;
  document.getElementById('ea_password_hint').textContent = '(lämna tomt för att behålla nuvarande)';
  document.getElementById('ea_imap_host').value = acc.imap_host || '';
  document.getElementById('ea_imap_port').value = acc.imap_port || 993;
  document.getElementById('ea_imap_encryption').value = acc.imap_encryption || 'ssl';
  document.getElementById('ea_is_default').checked = !!acc.is_default;
  openModal('emailAccountModal');
}
</script>

<?php require_once __DIR__ . '/includes/crm-footer.php'; ?>
