<?php
/**
 * Visma eEkonomi API integration — OAuth2 + REST wrapper.
 * Scaffold ready to connect: set VISMA_CLIENT_ID / VISMA_CLIENT_SECRET in crm/config.php,
 * then connect via crm/bokforing.php. Tokens persisted in the settings table.
 *
 * Docs: https://eaccountingapi.vismaonline.com/swagger/index.html
 */

require_once __DIR__ . '/db.php';

const VISMA_AUTH_URL  = 'https://identity.vismaonline.com/connect/authorize';
const VISMA_TOKEN_URL = 'https://identity.vismaonline.com/connect/token';
const VISMA_API_BASE  = 'https://eaccountingapi.vismaonline.com/v2/';

function visma_enabled(): bool {
    return defined('VISMA_CLIENT_ID') && VISMA_CLIENT_ID !== '';
}

function visma_connected(): bool {
    return get_setting('visma_access_token') !== null;
}

function visma_authorize_url(string $redirectUri): string {
    $state = bin2hex(random_bytes(16));
    set_setting('visma_oauth_state', $state);
    return VISMA_AUTH_URL . '?' . http_build_query([
        'client_id'     => VISMA_CLIENT_ID,
        'redirect_uri'  => $redirectUri,
        'scope'         => 'ea:api ea:sales offline_access',
        'state'         => $state,
        'response_type' => 'code',
    ]);
}

function visma_exchange_code(string $code, string $redirectUri): bool {
    $ch = curl_init(VISMA_TOKEN_URL);
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => http_build_query([
            'grant_type'    => 'authorization_code',
            'code'          => $code,
            'redirect_uri'  => $redirectUri,
            'client_id'     => VISMA_CLIENT_ID,
            'client_secret' => VISMA_CLIENT_SECRET,
        ]),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 15,
    ]);
    $resp = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    $data = json_decode((string)$resp, true);

    if ($httpCode >= 200 && $httpCode < 300 && !empty($data['access_token'])) {
        set_setting('visma_access_token', $data['access_token']);
        set_setting('visma_refresh_token', $data['refresh_token'] ?? '');
        set_setting('visma_token_expires', (string)(time() + (int)($data['expires_in'] ?? 3600)));
        return true;
    }
    return false;
}

function visma_refresh_token(): bool {
    $refresh = get_setting('visma_refresh_token');
    if (!$refresh) return false;

    $ch = curl_init(VISMA_TOKEN_URL);
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => http_build_query([
            'grant_type'    => 'refresh_token',
            'refresh_token' => $refresh,
            'client_id'     => VISMA_CLIENT_ID,
            'client_secret' => VISMA_CLIENT_SECRET,
        ]),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 15,
    ]);
    $resp = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    $data = json_decode((string)$resp, true);

    if ($code >= 200 && $code < 300 && !empty($data['access_token'])) {
        set_setting('visma_access_token', $data['access_token']);
        set_setting('visma_refresh_token', $data['refresh_token'] ?? $refresh);
        set_setting('visma_token_expires', (string)(time() + (int)($data['expires_in'] ?? 3600)));
        return true;
    }
    return false;
}

function visma_request(string $method, string $path, array $body = []): array {
    $expires = (int)get_setting('visma_token_expires', '0');
    if ($expires && $expires < time() + 30) visma_refresh_token();

    $token = get_setting('visma_access_token');
    if (!$token) return ['ok' => false, 'code' => 0, 'data' => ['error' => 'not_connected']];

    $ch = curl_init(VISMA_API_BASE . ltrim($path, '/'));
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

function visma_sync_customer(array $customer): ?string {
    $existingId = get_setting('visma_customer_' . $customer['id']);
    $payload = [
        'Name'              => $customer['name'],
        'Email'             => $customer['email'] ?: '',
        'Phone'             => $customer['phone'] ?: '',
        'InvoiceAddress'    => [
            'Address1' => $customer['address'] ?: '',
            'City'     => $customer['city'] ?: '',
            'PostalCode' => $customer['postal_code'] ?: '',
        ],
        'CorporateIdentityNumber' => $customer['org_nr'] ?: '',
    ];

    $res = $existingId
        ? visma_request('PUT', "customers/$existingId", $payload)
        : visma_request('POST', 'customers', $payload);

    $extId = $res['data']['Id'] ?? $existingId;
    if ($res['ok'] && $extId) set_setting('visma_customer_' . $customer['id'], (string)$extId);

    db()->prepare(
        "INSERT INTO accounting_sync_log (provider, entity_type, entity_id, external_id, action, status, response) VALUES ('visma','customer',?,?,?,?,?)"
    )->execute([$customer['id'], $extId, $existingId ? 'update' : 'create', $res['ok'] ? 'success' : 'failed', json_encode($res['data'])]);

    return $res['ok'] ? (string)$extId : null;
}

function visma_sync_invoice(array $invoice, array $items, array $customer): ?string {
    $vismaCustId = visma_sync_customer($customer);
    if (!$vismaCustId) return null;

    $rows = array_map(fn($i) => [
        'Description' => $i['description'],
        'Quantity'    => $i['qty'],
        'UnitPrice'   => $i['unit_price'],
    ], $items);

    $payload = [
        'CustomerId'  => $vismaCustId,
        'InvoiceDate' => $invoice['issue_date'],
        'DueDate'     => $invoice['due_date'],
        'Rows'        => $rows,
    ];

    $res = visma_request('POST', 'customerinvoices', $payload);
    $extId = $res['data']['Id'] ?? null;

    db()->prepare(
        "INSERT INTO accounting_sync_log (provider, entity_type, entity_id, external_id, action, status, response) VALUES ('visma','invoice',?,?,?,?,?)"
    )->execute([$invoice['id'], $extId, 'create', $res['ok'] ? 'success' : 'failed', json_encode($res['data'])]);

    return $res['ok'] ? (string)$extId : null;
}
