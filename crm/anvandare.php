<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/mailer.php';
$me = require_role([]); // super_admin only
$pdo = db();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $action = $_POST['action'] ?? '';

    if ($action === 'create') {
        $email = strtolower(trim($_POST['email']));
        $name  = trim($_POST['name']);
        $exists = $pdo->prepare("SELECT id FROM users WHERE email=?"); $exists->execute([$email]);
        if ($exists->fetchColumn()) {
            flash('E-postadressen finns redan.', 'error');
        } elseif (strlen($_POST['password'] ?? '') < 8) {
            flash('Lösenordet måste vara minst 8 tecken.', 'error');
        } else {
            $pdo->prepare("INSERT INTO users (name,email,password_hash,role,phone) VALUES (?,?,?,?,?)")
                ->execute([$name, $email, password_hash($_POST['password'], PASSWORD_DEFAULT), $_POST['role'], trim($_POST['phone'] ?? '')]);
            $newId = (int)$pdo->lastInsertId();
            audit('user_create', 'user', $newId);

            if (!empty($_POST['send_welcome'])) {
                $token = create_password_reset_token('crm', $newId, 60 * 24 * 7); // 7 days, like the supplier/portal invite links
                $setUrl = (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . '/crm/reset-password.php?token=' . $token;
                $sent = crm_send_mail(
                    $email, $name,
                    'Välkommen till M2 Platform!',
                    '<p>Hej ' . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . '!</p><p>Du har fått ett konto i M2 Platform med rollen <strong>' . htmlspecialchars(ROLES[$_POST['role']] ?? $_POST['role'], ENT_QUOTES, 'UTF-8') . '</strong>.</p><p>Klicka på knappen nedan för att välja ditt eget lösenord och logga in. Länken är giltig i 7 dagar.</p>',
                    'user', $newId, $setUrl, 'Sätt mitt lösenord'
                );
                flash($sent ? 'Användare skapad och välkomstmejl skickat.' : 'Användare skapad, men välkomstmejlet kunde inte skickas (kontrollera SMTP-inställningar).', $sent ? 'success' : 'error');
            } else {
                flash('Användare skapad.');
            }
        }
        header('Location: anvandare.php'); exit;
    }

    if ($action === 'toggle') {
        $uid = (int)$_POST['id'];
        if ($uid !== (int)$me['id']) {
            $pdo->prepare("UPDATE users SET active = 1 - active WHERE id=?")->execute([$uid]);
            audit('user_toggle', 'user', $uid);
            flash('Användarstatus ändrad.');
        }
        header('Location: anvandare.php'); exit;
    }

    if ($action === 'role') {
        $uid = (int)$_POST['id'];
        if ($uid !== (int)$me['id'] && isset(ROLES[$_POST['role']])) {
            $pdo->prepare("UPDATE users SET role=? WHERE id=?")->execute([$_POST['role'], $uid]);
            audit('user_role', 'user', $uid, $_POST['role']);
            flash('Roll uppdaterad.');
        }
        header('Location: anvandare.php'); exit;
    }

    if ($action === 'password') {
        $uid = (int)$_POST['id'];
        if (strlen($_POST['password']) >= 8) {
            $pdo->prepare("UPDATE users SET password_hash=? WHERE id=?")->execute([password_hash($_POST['password'], PASSWORD_DEFAULT), $uid]);
            audit('user_password', 'user', $uid);
            flash('Lösenord uppdaterat.');
        } else {
            flash('Lösenordet måste vara minst 8 tecken.', 'error');
        }
        header('Location: anvandare.php'); exit;
    }
}

$users = $pdo->query("SELECT * FROM users ORDER BY created_at")->fetchAll();

$crm_title = 'Användare';
$crm_page  = 'anvandare';
require_once __DIR__ . '/includes/crm-header.php';
?>

<div class="topbar">
  <div>
    <h1>Användare & roller</h1>
    <div class="topbar__sub"><?= count($users) ?> användare · Rollbaserad åtkomstkontroll (RBAC)</div>
  </div>
  <div class="topbar__actions">
    <button class="btn btn--primary" onclick="openModal('newUserModal')">+ Ny användare</button>
  </div>
</div>

<?php flash(); ?>

<!-- ROLE EXPLANATION (per blueprint) -->
<div class="card card--pad" style="margin-bottom:16px;background:var(--blue-lt);border-color:#BFDBFE">
  <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:14px;font-size:12.5px">
    <div><strong style="color:var(--blue)">Super Admin</strong><br><span style="color:var(--ink-soft)">Full åtkomst till allt</span></div>
    <div><strong style="color:var(--blue)">Säljansvarig</strong><br><span style="color:var(--ink-soft)">Leads, offerter, kunder</span></div>
    <div><strong style="color:var(--blue)">Projektledare</strong><br><span style="color:var(--ink-soft)">Projekt, leverantörer, schema</span></div>
    <div><strong style="color:var(--blue)">Ekonomi</strong><br><span style="color:var(--ink-soft)">Fakturor, betalningar</span></div>
    <div><strong style="color:var(--blue)">Kundsupport</strong><br><span style="color:var(--ink-soft)">Meddelanden, ärenden</span></div>
  </div>
</div>

<div class="card">
  <div class="table-wrap">
    <table class="data">
      <thead><tr><th>Användare</th><th>Roll</th><th>Status</th><th>Senast inloggad</th><th>Åtgärder</th></tr></thead>
      <tbody>
        <?php foreach ($users as $u): ?>
        <tr>
          <td>
            <div style="display:flex;align-items:center;gap:10px">
              <div class="avatar" style="width:32px;height:32px;font-size:11.5px"><?= e(initials($u['name'])) ?></div>
              <div><div style="font-weight:550"><?= e($u['name']) ?></div><div style="font-size:11.5px;color:var(--gray-lt)"><?= e($u['email']) ?></div></div>
            </div>
          </td>
          <td>
            <?php if ($u['id'] == $me['id']): ?>
            <span class="badge" style="background:var(--blue-lt);color:var(--blue)"><?= e(ROLES[$u['role']]) ?> (du)</span>
            <?php else: ?>
            <form method="POST" style="display:flex;gap:6px">
              <?= csrf_field() ?><input type="hidden" name="action" value="role"><input type="hidden" name="id" value="<?= $u['id'] ?>">
              <select class="fs" name="role" style="padding:5px 9px;font-size:12px;width:auto" onchange="this.form.submit()">
                <?php foreach (ROLES as $rk => $rl): ?>
                <option value="<?= $rk ?>" <?= $u['role']===$rk?'selected':'' ?>><?= e($rl) ?></option>
                <?php endforeach; ?>
              </select>
            </form>
            <?php endif; ?>
          </td>
          <td><span class="badge" style="background:<?= $u['active'] ? '#05966914' : '#6B728014' ?>;color:<?= $u['active'] ? 'var(--green)' : 'var(--gray)' ?>"><?= $u['active'] ? 'Aktiv' : 'Avstängd' ?></span></td>
          <td style="font-size:12.5px;color:var(--gray)"><?= $u['last_login'] ? time_ago($u['last_login']) : 'Aldrig' ?></td>
          <td>
            <div style="display:flex;gap:6px">
              <button class="btn btn--ghost btn--sm" onclick="document.getElementById('pwId').value='<?= $u['id'] ?>';document.getElementById('pwName').textContent='<?= e($u['name']) ?>';openModal('pwModal')">Lösenord</button>
              <?php if ($u['id'] != $me['id']): ?>
              <form method="POST"><?= csrf_field() ?><input type="hidden" name="action" value="toggle"><input type="hidden" name="id" value="<?= $u['id'] ?>">
                <button class="btn <?= $u['active'] ? 'btn--danger' : 'btn--green' ?> btn--sm"><?= $u['active'] ? 'Stäng av' : 'Aktivera' ?></button>
              </form>
              <?php endif; ?>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<div class="modal-bg" id="newUserModal">
  <div class="modal">
    <h3>Ny användare</h3>
    <form method="POST">
      <?= csrf_field() ?><input type="hidden" name="action" value="create">
      <div class="fg"><label>Namn *</label><input class="fi" name="name" required></div>
      <div class="frow">
        <div class="fg"><label>E-post *</label><input class="fi" type="email" name="email" required></div>
        <div class="fg"><label>Telefon</label><input class="fi" name="phone"></div>
      </div>
      <div class="frow">
        <div class="fg"><label>Roll</label>
          <select class="fs" name="role">
            <?php foreach (ROLES as $rk => $rl): if ($rk === 'super_admin') continue; ?>
            <option value="<?= $rk ?>"><?= e($rl) ?></option>
            <?php endforeach; ?>
            <option value="super_admin">Super Admin</option>
          </select>
        </div>
        <div class="fg"><label>Lösenord * (min 8)</label><input class="fi" type="text" name="password" minlength="8" required></div>
      </div>
      <div class="fg"><label style="display:flex;align-items:center;gap:8px;font-weight:500">
        <input type="checkbox" name="send_welcome" value="1" checked> Skicka välkomstmejl med länk för att sätta eget lösenord
      </label></div>
      <div style="display:flex;gap:10px;justify-content:flex-end">
        <button type="button" class="btn btn--ghost" onclick="closeModal('newUserModal')">Avbryt</button>
        <button class="btn btn--primary">Skapa</button>
      </div>
    </form>
  </div>
</div>

<div class="modal-bg" id="pwModal">
  <div class="modal">
    <h3>Byt lösenord – <span id="pwName"></span></h3>
    <form method="POST">
      <?= csrf_field() ?><input type="hidden" name="action" value="password"><input type="hidden" name="id" id="pwId">
      <div class="fg"><label>Nytt lösenord (min 8 tecken)</label><input class="fi" type="text" name="password" minlength="8" required></div>
      <div style="display:flex;gap:10px;justify-content:flex-end">
        <button type="button" class="btn btn--ghost" onclick="closeModal('pwModal')">Avbryt</button>
        <button class="btn btn--primary">Spara</button>
      </div>
    </form>
  </div>
</div>

<?php require_once __DIR__ . '/includes/crm-footer.php'; ?>
