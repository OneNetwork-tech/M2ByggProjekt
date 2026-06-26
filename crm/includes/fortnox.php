<?php
/**
 * Fortnox API integration — OAuth2 + REST wrapper.
 * Scaffold ready to connect: set FORTNOX_CLIENT_ID / FORTNOX_CLIENT_SECRET in crm/config.php,
 * then connect via crm/bokforing.php (OAuth authorization-code flow). Tokens are persisted
 * in the settings table so they survive across requests and refresh automatically.
 *
 * Docs: https://www.fortnox.se/developer/
 */

require_once __DIR__ . '/db.php';

const FORTNOX_AUTH_URL  = 'https://apps.fortnox.se/oauth-v1/auth';
const FORTNOX_TOKEN_URL = 'https://apps.fortnox.se/oauth-v1/token';
const FORTNOX_API_BASE  = 'https://api.fortnox.se/3/';

function fortnox_enabled(): bool {
    return defined('FORTNOX_CLIENT_ID') && FORTNOX_CLIENT_ID !== '';
}

function fortnox_connected(): bool {
    return get_setting('fortnox_access_token') !== null;
}

/** Build the OAuth authorization URL the admin clicks to connect Fortnox. */
function fortnox_authorize_url(string $redirectUri): string {
    $state = bin2hex(random_bytes(16));
    set_setting('fortnox_oauth_state', $state);
    return FORTNOX_AUTH_URL . '?' . http_build_query([
        'client_id'     => FORTNOX_CLIENT_ID,
        'redirect_uri'  => $redirectUri,
        'scope'         => 'companyinformation invoice customer',
        'state'         => $state,
        'access_type'   => 'offline',
        'response_type' => 'code',
    ]);
}

/** Exchange an authorization code for access + refresh tokens. */
function fortnox_exchange_code(string $code, string $redirectUri): bool {
    $ch = curl_init(FORTNOX_TOKEN_URL);
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => http_build_query([
            'grant_type'   => 'authorization_code',
            'code'         => $code,
            'redirect_uri' => $redirectUri,
        ]),
        CURLOPT_HTTPHEADER => [
            'Authorization: Basic ' . base64_encode(FORTNOX_CLIENT_ID . ':' . FORTNOX_CLIENT_SECRET),
            'Content-Type: application/x-www-form-urlencoded',
        ],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 15,
    ]);
    $resp = curl_exec($ch);
    $code200 = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    $data = json_decode((string)$resp, true);

    if ($code200 >= 200 && $code200 < 300 && !empty($data['access_token'])) {
        set_setting('fortnox_access_token', $data['access_token']);
        set_setting('fortnox_refresh_token', $data['refresh_token'] ?? '');
        set_setting('fortnox_token_expires', (string)(time() + (int)($data['expires_in'] ?? 3600)));
        return true;
    }
    return false;
}

function fortnox_refresh_token(): bool {
    $refresh = get_setting('fortnox_refresh_token');
    if (!$refresh) return false;

    $ch = curl_init(FORTNOX_TOKEN_URL);
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => http_build_query(['grant_type' => 'refresh_token', 'refresh_token' => $refresh]),
        CURLOPT_HTTPHEADER => [
            'Authorization: Basic ' . base64_encode(FORTNOX_CLIENT_ID . ':' . FORTNOX_CLIENT_SECRET),
            'Content-Type: application/x-www-form-urlencoded',
        ],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 15,
    ]);
    $resp = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    $data = json_decode((string)$resp, true);

    if ($code >= 200 && $code < 300 && !empty($data['access_token'])) {
        set_setting('fortnox_access_token', $data['access_token']);
        set_setting('fortnox_refresh_token', $data['refresh_token'] ?? $refresh);
        set_setting('fortnox_token_expires', (string)(time() + (int)($data['expires_in'] ?? 3600)));
        return true;
    }
    return false;
}

/** Low-level Fortnox API call. Auto-refreshes the token once on 401. */
function fortnox_request(string $method, string $path, array $body = []): array {
    $expires = (int)get_setting('fortnox_token_expires', '0');
    if ($expires && $expires < time() + 30) fortnox_refresh_token();

    $token = get_setting('fortnox_access_token');
    if (!$token) return ['ok' => false, 'code' => 0, 'data' => ['error' => 'not_connected']];

    $ch = curl_init(FORTNOX_API_BASE . ltrim($path, '/'));
    $opts = [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 15,
        CURLOPT_CUSTOMREQUEST  => $method,
        CURLOPT_HTTPHEADER     => [
            'Authorization: Bearer ' . $token,
            'Content-Type: application/json',
            'Accept: application/json',
        ],
    ];
    if ($body) $opts[CURLOPT_POSTFIELDS] = json_encode($body);
    curl_setopt_array($ch, $opts);
    $resp = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return ['ok' => $code >= 200 && $code < 300, 'code' => $code, 'data' => json_decode((string)$resp, true) ?: []];
}

/** Push (create or update) a customer to Fortnox. Returns the Fortnox customer number or null. */
function fortnox_sync_customer(array $customer): ?string {
    $existingId = get_setting('fortnox_customer_' . $customer['id']);
    $payload = ['Customer' => [
        'Name'  => $customer['name'],
        'Email' => $customer['email'] ?: '',
        'Phone1' => $customer['phone'] ?: '',
        'Address1' => $customer['address'] ?: '',
        'City' => $customer['city'] ?: '',
        'ZipCode' => $customer['postal_code'] ?: '',
        'OrganisationNumber' => $customer['org_nr'] ?: '',
    ]];

    if ($existingId) {
        $res = fortnox_request('PUT', "customers/$existingId", $payload);
    } else {
        $res = fortnox_request('POST', 'customers', $payload);
    }

    $extId = $res['data']['Customer']['CustomerNumber'] ?? $existingId;
    if ($res['ok'] && $extId) set_setting('fortnox_customer_' . $customer['id'], (string)$extId);

    db()->prepare(
        "INSERT INTO accounting_sync_log (provider, entity_type, entity_id, external_id, action, status, response) VALUES ('fortnox','customer',?,?,?,?,?)"
    )->execute([$customer['id'], $extId, $existingId ? 'update' : 'create', $res['ok'] ? 'success' : 'failed', json_encode($res['data'])]);

    return $res['ok'] ? (string)$extId : null;
}

/** Push an invoice to Fortnox, syncing its customer first if needed. */
function fortnox_sync_invoice(array $invoice, array $items, array $customer): ?string {
    $fortnoxCustNo = fortnox_sync_customer($customer);
    if (!$fortnoxCustNo) return null;

    $rows = array_map(fn($i) => [
        'Description' => $i['description'],
        'DeliveredQuantity' => $i['qty'],
        'Price' => $i['unit_price'],
    ], $items);

    $payload = ['Invoice' => [
        'CustomerNumber' => $fortnoxCustNo,
        'InvoiceDate'    => $invoice['issue_date'],
        'DueDate'        => $invoice['due_date'],
        'InvoiceRows'    => $rows,
    ]];

    $res = fortnox_request('POST', 'invoices', $payload);
    $extId = $res['data']['Invoice']['DocumentNumber'] ?? null;

    db()->prepare(
        "INSERT INTO accounting_sync_log (provider, entity_type, entity_id, external_id, action, status, response) VALUES ('fortnox','invoice',?,?,?,?,?)"
    )->execute([$invoice['id'], $extId, 'create', $res['ok'] ? 'success' : 'failed', json_encode($res['data'])]);

    return $res['ok'] ? (string)$extId : null;
}
