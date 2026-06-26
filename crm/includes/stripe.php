<?php
/**
 * Minimal Stripe REST API wrapper (no SDK dependency).
 * Covers what's needed for invoice payment: Checkout Sessions + webhook verification.
 * Set STRIPE_SECRET_KEY / STRIPE_WEBHOOK_SECRET in crm/config.php to enable.
 */

function stripe_enabled(): bool {
    return defined('STRIPE_SECRET_KEY') && STRIPE_SECRET_KEY !== '';
}

/**
 * Low-level call to the Stripe API.
 */
function stripe_request(string $method, string $path, array $params = []): array {
    $url = 'https://api.stripe.com/v1/' . ltrim($path, '/');
    if ($method === 'GET' && $params) $url .= '?' . http_build_query($params);

    $ch = curl_init($url);
    $opts = [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 15,
        CURLOPT_HTTPHEADER     => ['Authorization: Bearer ' . STRIPE_SECRET_KEY],
    ];
    if ($method === 'POST') {
        $opts[CURLOPT_POST]       = true;
        $opts[CURLOPT_POSTFIELDS] = stripe_flatten_params($params);
    }
    curl_setopt_array($ch, $opts);
    $resp = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    $data = json_decode((string)$resp, true) ?: [];
    return ['ok' => $code >= 200 && $code < 300, 'code' => $code, 'data' => $data];
}

/** Stripe expects PHP-style nested array params flattened as x[y]=z */
function stripe_flatten_params(array $params, string $prefix = ''): string {
    $pairs = [];
    foreach ($params as $k => $v) {
        $key = $prefix === '' ? $k : "{$prefix}[{$k}]";
        if (is_array($v)) {
            $pairs[] = stripe_flatten_params($v, $key);
        } else {
            $pairs[] = urlencode($key) . '=' . urlencode((string)$v);
        }
    }
    return implode('&', array_filter($pairs));
}

/**
 * Create a Checkout Session for paying a single invoice. Returns the session URL or null on failure.
 */
function stripe_create_invoice_checkout(array $invoice, string $successUrl, string $cancelUrl): ?string {
    if (!stripe_enabled()) return null;

    $remaining = round(((float)$invoice['total'] - (float)$invoice['paid_amount']) * 100); // öre
    if ($remaining <= 0) return null;

    $res = stripe_request('POST', 'checkout/sessions', [
        'mode' => 'payment',
        'payment_method_types' => ['card'],
        'line_items' => [[
            'price_data' => [
                'currency' => 'sek',
                'product_data' => ['name' => 'Faktura ' . $invoice['invoice_no']],
                'unit_amount' => $remaining,
            ],
            'quantity' => 1,
        ]],
        'metadata' => ['invoice_id' => $invoice['id'], 'invoice_no' => $invoice['invoice_no']],
        'success_url' => $successUrl,
        'cancel_url'  => $cancelUrl,
    ]);

    return $res['ok'] ? ($res['data']['url'] ?? null) : null;
}

/**
 * Verify a Stripe webhook signature (Stripe-Signature header) without the SDK.
 */
function stripe_verify_webhook(string $payload, string $sigHeader, string $secret, int $toleranceSeconds = 300): bool {
    $parts = [];
    foreach (explode(',', $sigHeader) as $pair) {
        [$k, $v] = array_pad(explode('=', $pair, 2), 2, '');
        $parts[$k][] = $v;
    }
    $timestamp = $parts['t'][0] ?? null;
    $signatures = $parts['v1'] ?? [];
    if (!$timestamp || !$signatures) return false;
    if (abs(time() - (int)$timestamp) > $toleranceSeconds) return false;

    $signedPayload = $timestamp . '.' . $payload;
    $expected = hash_hmac('sha256', $signedPayload, $secret);

    foreach ($signatures as $sig) {
        if (hash_equals($expected, $sig)) return true;
    }
    return false;
}
