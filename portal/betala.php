<?php
require_once __DIR__ . '/includes/auth.php';
require_once dirname(__DIR__) . '/crm/includes/stripe.php';
$pu  = portal_require();
$cid = (int)$pu['customer_id'];

$id = (int)($_GET['invoice'] ?? 0);
$s = db()->prepare("SELECT * FROM invoices WHERE id = ? AND customer_id = ?");
$s->execute([$id, $cid]);
$invoice = $s->fetch();

if (!$invoice) { header('Location: /portal/fakturor.php'); exit; }

if (!stripe_enabled()) {
    header('Location: /portal/fakturor.php?payerror=not_configured'); exit;
}

if ((float)$invoice['total'] - (float)$invoice['paid_amount'] <= 0) {
    header('Location: /portal/fakturor.php?payerror=already_paid'); exit;
}

$base = (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'];
$checkoutUrl = stripe_create_invoice_checkout(
    $invoice,
    $base . '/portal/fakturor.php?paid=1&invoice=' . $id,
    $base . '/portal/fakturor.php?paycancel=1'
);

if ($checkoutUrl) {
    header('Location: ' . $checkoutUrl);
} else {
    header('Location: /portal/fakturor.php?payerror=1');
}
exit;
