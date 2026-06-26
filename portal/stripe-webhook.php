<?php
/**
 * Stripe webhook — marks invoices paid on checkout.session.completed.
 * Configure this URL (https://yourdomain/portal/stripe-webhook.php) in the Stripe Dashboard.
 */
require_once dirname(__DIR__) . '/crm/includes/db.php';
require_once dirname(__DIR__) . '/crm/includes/helpers.php';
require_once dirname(__DIR__) . '/crm/includes/stripe.php';

$payload   = file_get_contents('php://input');
$sigHeader = $_SERVER['HTTP_STRIPE_SIGNATURE'] ?? '';

if (!defined('STRIPE_WEBHOOK_SECRET') || !stripe_verify_webhook($payload, $sigHeader, STRIPE_WEBHOOK_SECRET)) {
    http_response_code(400);
    exit('Invalid signature');
}

$event = json_decode($payload, true);
if (!$event) { http_response_code(400); exit('Invalid payload'); }

if ($event['type'] === 'checkout.session.completed') {
    $session   = $event['data']['object'];
    $invoiceId = (int)($session['metadata']['invoice_id'] ?? 0);
    $amountPaid = (float)($session['amount_total'] ?? 0) / 100;

    if ($invoiceId) {
        $s = db()->prepare("SELECT * FROM invoices WHERE id = ?");
        $s->execute([$invoiceId]);
        if ($inv = $s->fetch()) {
            $newPaid = (float)$inv['paid_amount'] + $amountPaid;
            db()->prepare("UPDATE invoices SET paid_amount = ? WHERE id = ?")->execute([$newPaid, $invoiceId]);
            refresh_invoice_status($invoiceId);
            log_timeline('invoice', $invoiceId, 'system', 'Betalning mottagen via Stripe: ' . money($amountPaid), '', null);
            audit('stripe_payment', 'invoice', $invoiceId, 'amount=' . $amountPaid);
        }
    }
}

http_response_code(200);
echo json_encode(['received' => true]);
