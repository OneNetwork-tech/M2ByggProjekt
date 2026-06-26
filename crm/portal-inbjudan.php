<?php
/**
 * CRM — Send customer portal invite
 */
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/mailer.php';
require_role(['super_admin','sales','support']);

$cid = (int)($_GET['customer'] ?? 0);
if (!$cid) { header('Location: /crm/kunder.php'); exit; }

$_s = db()->prepare("SELECT * FROM customers WHERE id = ?");
$_s->execute([$cid]);
$customer = $_s->fetch() ?: null;
if (!$customer) { header('Location: /crm/kunder.php'); exit; }

$error = ''; $success = ''; $mailStatus = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $email = strtolower(trim($_POST['email'] ?? $customer['email'] ?? ''));
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Ogiltig e-postadress.';
    } else {
        $token   = bin2hex(random_bytes(24));
        $expires = date('Y-m-d H:i:s', strtotime('+7 days'));
        db()->prepare("UPDATE portal_invites SET used_at = datetime('now') WHERE customer_id = ? AND used_at IS NULL")
            ->execute([$cid]);
        db()->prepare("INSERT INTO portal_invites (customer_id, token, email, expires_at) VALUES (?,?,?,?)")
            ->execute([$cid, $token, $email, $expires]);

        $inviteUrl = (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . '/portal/login.php?token=' . $token;
        audit('portal_invite_sent', 'customer', $cid, "email=$email");

        $sent = crm_send_mail(
            $email, $customer['name'] ?: $email,
            'Din kundportal hos M2 Bygg Team',
            '<p>Hej ' . htmlspecialchars($customer['name'] ?? '', ENT_QUOTES, 'UTF-8') . '!</p><p>Klicka på knappen nedan för att sätta ett lösenord och aktivera din kundportal. Länken är giltig i 7 dagar.</p>',
            'customer', $cid, $inviteUrl, 'Aktivera kundportal'
        );

        $success = $inviteUrl;
        $mailStatus = $sent ? 'sent' : 'failed';
    }
}

$crm_title = 'Bjud in till kundportal';
$crm_page  = 'portaler';
require_once __DIR__ . '/includes/crm-header.php';
?>
<div class="topbar">
  <div>
    <h1>Bjud in till kundportal</h1>
    <div class="topbar__sub">Kund: <strong><?= e($customer['name']) ?></strong></div>
  </div>
  <div class="topbar__actions">
    <a href="kund.php?id=<?= $cid ?>" class="btn btn--ghost">← Tillbaka</a>
  </div>
</div>

<?php flash(); ?>

<?php if ($error): ?>
<div class="flash" style="border-color:#DC262633;background:#DC26260d;color:var(--red)"><?= e($error) ?></div>
<?php endif; ?>

<?php if ($success): ?>
<?php $color = $mailStatus === 'sent' ? 'var(--green)' : 'var(--amber)'; ?>
<div class="flash" style="border-color:<?= $mailStatus==='sent'?'#05966933':'#D9770633' ?>;background:<?= $mailStatus==='sent'?'#0596690d':'#D977060d' ?>;color:<?= $color ?>">
  <?php if ($mailStatus === 'sent'): ?>
  <strong>Inbjudan skickad via e-post!</strong> Länken nedan är giltig i 7 dagar:
  <?php else: ?>
  <strong>Länk skapad, men e-post kunde inte skickas.</strong> Kopiera länken och skicka manuellt (giltig 7 dagar):
  <?php endif; ?>
  <code style="word-break:break-all;display:block;margin-top:8px;font-size:12.5px;color:var(--ink)"><?= e($success) ?></code>
  <div style="margin-top:12px;display:flex;gap:8px;flex-wrap:wrap">
    <a href="mailto:<?= e($customer['email'] ?? '') ?>?subject=Din kundportal hos M2 Bygg Team&body=<?= rawurlencode("Hej " . ($customer['name'] ?? '') . "!\n\nKlicka på länken för att aktivera din kundportal:\n" . $success . "\n\nMed vänliga hälsningar,\nM2 Bygg Team AB") ?>" class="btn btn--primary btn--sm">Öppna i e-post</a>
    <button type="button" onclick="navigator.clipboard.writeText('<?= e($success) ?>');this.textContent='Kopierat!'" class="btn btn--ghost btn--sm">Kopiera länk</button>
  </div>
</div>
<?php endif; ?>

<div class="card card--pad" style="max-width:480px">
  <p style="font-size:13px;color:var(--gray);margin-bottom:18px">Kunden får en länk för att sätta lösenord och logga in på sin kundportal.</p>
  <form method="post">
    <?= csrf_field() ?>
    <div class="fg">
      <label>E-postadress</label>
      <input class="fi" type="email" name="email" value="<?= e($customer['email'] ?? '') ?>" required>
      <small style="font-size:11.5px;color:var(--gray-lt)">Länken skickas hit (eller kopiera manuellt).</small>
    </div>
    <button type="submit" class="btn btn--primary">Generera inbjudningslänk</button>
  </form>
</div>

<?php require_once __DIR__ . '/includes/crm-footer.php'; ?>
