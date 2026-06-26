<?php
/**
 * CRM — Send supplier portal invite
 */
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/mailer.php';
require_role(['super_admin','sales','support']);

$sid = (int)($_GET['supplier'] ?? 0);
if (!$sid) { header('Location: /crm/leverantorer.php'); exit; }

$s = db()->prepare("SELECT * FROM suppliers WHERE id = ?");
$s->execute([$sid]);
$supplier = $s->fetch();
if (!$supplier) { header('Location: /crm/leverantorer.php'); exit; }

$error = ''; $success = ''; $mailStatus = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $email = strtolower(trim($_POST['email'] ?? $supplier['email'] ?? ''));
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Ogiltig e-postadress.';
    } else {
        $token   = bin2hex(random_bytes(24));
        $expires = date('Y-m-d H:i:s', strtotime('+7 days'));
        db()->prepare("UPDATE supplier_invites SET used_at = datetime('now') WHERE supplier_id = ? AND used_at IS NULL")
            ->execute([$sid]);
        db()->prepare("INSERT INTO supplier_invites (supplier_id, token, email, expires_at) VALUES (?,?,?,?)")
            ->execute([$sid, $token, $email, $expires]);

        $inviteUrl = (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . '/leverantor/login.php?token=' . $token;
        audit('supplier_invite_sent', 'supplier', $sid, "email=$email");

        $sent = crm_send_mail(
            $email, $supplier['company'] ?: $email,
            'Din leverantörsportal hos M2 Bygg Team',
            '<p>Hej ' . htmlspecialchars($supplier['company'] ?? '', ENT_QUOTES, 'UTF-8') . '!</p><p>Klicka på knappen nedan för att sätta ett lösenord och aktivera ditt leverantörskonto. Länken är giltig i 7 dagar.</p>',
            'supplier', $sid, $inviteUrl, 'Aktivera leverantörskonto'
        );

        $success = $inviteUrl;
        $mailStatus = $sent ? 'sent' : 'failed';
    }
}

$crm_title = 'Bjud in till leverantörsportal';
$crm_page  = 'portaler';
require_once __DIR__ . '/includes/crm-header.php';
?>
<div class="topbar">
  <div>
    <h1>Bjud in till leverantörsportal</h1>
    <div class="topbar__sub">Leverantör: <strong><?= e($supplier['company']) ?></strong></div>
  </div>
  <div class="topbar__actions">
    <a href="leverantorer.php" class="btn btn--ghost">← Tillbaka</a>
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
    <a href="mailto:<?= e($supplier['email'] ?? '') ?>?subject=Din leverantörsportal hos M2 Bygg Team&body=<?= rawurlencode("Hej " . ($supplier['company'] ?? '') . "!\n\nKlicka på länken för att aktivera ditt leverantörskonto:\n" . $success . "\n\nMed vänliga hälsningar,\nM2 Bygg Team AB") ?>" class="btn btn--primary btn--sm">Öppna i e-post</a>
    <button type="button" onclick="navigator.clipboard.writeText('<?= e($success) ?>');this.textContent='Kopierat!'" class="btn btn--ghost btn--sm">Kopiera länk</button>
  </div>
</div>
<?php endif; ?>

<div class="card card--pad" style="max-width:480px">
  <p style="font-size:13px;color:var(--gray);margin-bottom:18px">Leverantören får en länk för att sätta lösenord och komma åt sin leverantörsportal.</p>
  <form method="post">
    <?= csrf_field() ?>
    <div class="fg">
      <label>E-postadress</label>
      <input class="fi" type="email" name="email" value="<?= e($supplier['email'] ?? '') ?>" required>
    </div>
    <button type="submit" class="btn btn--primary">Generera inbjudningslänk</button>
  </form>
</div>

<?php require_once __DIR__ . '/includes/crm-footer.php'; ?>
