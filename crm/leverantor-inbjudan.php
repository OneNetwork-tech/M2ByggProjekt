<?php
/**
 * CRM — Send supplier portal invite
 */
require_once __DIR__ . '/includes/auth.php';
require_role(['super_admin','sales','support']);

$sid = (int)($_GET['supplier'] ?? 0);
if (!$sid) { header('Location: /crm/leverantorer.php'); exit; }

$s = db()->prepare("SELECT * FROM suppliers WHERE id = ?");
$s->execute([$sid]);
$supplier = $s->fetch();
if (!$supplier) { header('Location: /crm/leverantorer.php'); exit; }

$error = ''; $success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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
        $success = $inviteUrl;
    }
}
require_once __DIR__ . '/includes/crm-header.php';
?>
<div class="content">
  <div class="page-header">
    <div>
      <h1>Bjud in till leverantörsportal</h1>
      <p class="text-muted">Leverantör: <strong><?= e($supplier['company']) ?></strong></p>
    </div>
    <a href="/crm/leverantorer.php" class="btn btn-outline-secondary">← Tillbaka</a>
  </div>

  <?php if ($error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>

  <?php if ($success): ?>
  <div class="alert alert-success">
    <strong>Inbjudningslänk skapad!</strong> Skicka denna länk till leverantören (giltig 7 dagar):<br>
    <code style="word-break:break-all;display:block;margin-top:8px;font-size:.875rem"><?= e($success) ?></code>
    <div style="margin-top:12px;display:flex;gap:8px;flex-wrap:wrap">
      <a href="mailto:<?= e($supplier['email'] ?? '') ?>?subject=Din leverantörsportal hos M2 Bygg Team&body=<?= rawurlencode("Hej " . ($supplier['company'] ?? '') . "!\n\nKlicka på länken för att aktivera ditt leverantörskonto:\n" . $success . "\n\nMed vänliga hälsningar,\nM2 Bygg Team AB") ?>" class="btn btn-primary">Öppna i e-post</a>
      <button onclick="navigator.clipboard.writeText('<?= e($success) ?>');this.textContent='Kopierat!'" class="btn btn-outline-secondary">Kopiera länk</button>
    </div>
  </div>
  <?php endif; ?>

  <div class="card">
    <div class="card-body" style="max-width:480px">
      <p class="text-muted" style="margin-bottom:20px">Leverantören får en länk för att sätta lösenord och komma åt sin leverantörsportal.</p>
      <form method="post">
        <div class="mb-3">
          <label class="form-label">E-postadress</label>
          <input type="email" name="email" class="form-control" value="<?= e($supplier['email'] ?? '') ?>" required>
        </div>
        <button type="submit" class="btn btn-primary">Generera inbjudningslänk</button>
      </form>
    </div>
  </div>
</div>
<?php require_once __DIR__ . '/includes/crm-footer.php'; ?>
