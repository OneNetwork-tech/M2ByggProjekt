<?php
/**
 * CRM — Send customer portal invite
 */
require_once __DIR__ . '/includes/auth.php';
require_role(['super_admin','sales','support']);

$cid = (int)($_GET['customer'] ?? 0);
if (!$cid) { header('Location: /crm/kunder.php'); exit; }

$_s = db()->prepare("SELECT * FROM customers WHERE id = ?");
$_s->execute([$cid]);
$customer = $_s->fetch() ?: null;
if (!$customer) { header('Location: /crm/kunder.php'); exit; }

$error = ''; $success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = strtolower(trim($_POST['email'] ?? $customer['email'] ?? ''));
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Ogiltig e-postadress.';
    } else {
        // Create invite token (valid 7 days)
        $token   = bin2hex(random_bytes(24));
        $expires = date('Y-m-d H:i:s', strtotime('+7 days'));
        // Invalidate old invites
        db()->prepare("UPDATE portal_invites SET used_at = datetime('now') WHERE customer_id = ? AND used_at IS NULL")
            ->execute([$cid]);
        db()->prepare("INSERT INTO portal_invites (customer_id, token, email, expires_at) VALUES (?,?,?,?)")
            ->execute([$cid, $token, $email, $expires]);

        $inviteUrl = (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . '/portal/login.php?token=' . $token;
        audit('portal_invite_sent', 'customer', $cid, "email=$email");
        $success = $inviteUrl; // Display link (or send via email if mailer configured)
    }
}
require_once __DIR__ . '/includes/crm-header.php';
?>
<div class="content">
  <div class="page-header">
    <div>
      <h1>Bjud in till kundportal</h1>
      <p class="text-muted">Kund: <strong><?= e($customer['name']) ?></strong></p>
    </div>
    <a href="/crm/kund.php?id=<?= $cid ?>" class="btn btn-outline-secondary">← Tillbaka</a>
  </div>

  <?php if ($error): ?>
  <div class="alert alert-danger"><?= e($error) ?></div>
  <?php endif; ?>

  <?php if ($success): ?>
  <div class="alert alert-success">
    <strong>Inbjudningslänk skapad!</strong> Skicka denna länk till kunden (giltig 7 dagar):<br>
    <code style="word-break:break-all;display:block;margin-top:8px;font-size:.875rem"><?= e($success) ?></code>
    <div style="margin-top:12px;display:flex;gap:8px;flex-wrap:wrap">
      <a href="mailto:<?= e($customer['email'] ?? '') ?>?subject=Din kundportal hos M2 Bygg Team&body=<?= rawurlencode("Hej " . ($customer['name'] ?? '') . "!\n\nKlicka på länken för att aktivera din kundportal:\n" . $success . "\n\nMed vänliga hälsningar,\nM2 Bygg Team AB") ?>" class="btn btn-primary">
        Öppna i e-post
      </a>
      <button onclick="navigator.clipboard.writeText('<?= e($success) ?>');this.textContent='Kopierat!'" class="btn btn-outline-secondary">
        Kopiera länk
      </button>
    </div>
  </div>
  <?php endif; ?>

  <div class="card">
    <div class="card-body" style="max-width:480px">
      <p class="text-muted" style="margin-bottom:20px">Kunden får en länk för att sätta lösenord och logga in på sin kundportal.</p>
      <form method="post">
        <div class="mb-3">
          <label class="form-label">E-postadress</label>
          <input type="email" name="email" class="form-control" value="<?= e($customer['email'] ?? '') ?>" required>
          <small class="text-muted">Länken skickas hit (eller kopiera manuellt).</small>
        </div>
        <button type="submit" class="btn btn-primary">Generera inbjudningslänk</button>
      </form>
    </div>
  </div>
</div>
<?php require_once __DIR__ . '/includes/crm-footer.php'; ?>
