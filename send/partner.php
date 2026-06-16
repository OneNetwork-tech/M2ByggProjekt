<?php
/**
 * M2 Bygg Team AB — Partner Application Handler
 * AUTOMATION: Supplier Registration → Create Supplier (pending) in CRM
 */

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
    exit;
}

require_once __DIR__ . '/mailer.php';

$company   = post('company');
$orgnr     = post('orgnr');
$contact   = post('contact');
$phone     = post('phone');
$email     = post('email');
$specialty = post('specialty');
$areaTxt   = post('area');
$about     = post('about');

$errors = [];
if (empty($company))   $errors[] = 'Företagsnamn saknas.';
if (empty($contact))   $errors[] = 'Kontaktperson saknas.';
if (empty($phone))     $errors[] = 'Telefon saknas.';
if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Giltig e-post krävs.';
if (empty($specialty)) $errors[] = 'Tjänsteområde saknas.';
if (!empty(post('website'))) { echo json_encode(['success' => true]); exit; }

if (!empty($errors)) {
    echo json_encode(['success' => false, 'message' => implode(' ', $errors)]);
    exit;
}

/* ════════ CRM AUTOMATION: register supplier as pending ════════ */
try {
    require_once __DIR__ . '/../crm/includes/db.php';
    $notes = trim($about);
    if ($areaTxt) $notes .= "\n\nVerksamhetsområde: " . $areaTxt;
    db()->prepare("INSERT INTO suppliers (company, contact, email, phone, specialty, org_nr, status, notes) VALUES (?,?,?,?,?,?,'pending',?)")
        ->execute([trim($company), trim($contact), trim($email), trim($phone), trim($specialty), trim($orgnr), $notes]);
    $supId = db()->lastInsertId();
    log_timeline('supplier', $supId, 'system', 'Partneransökan inkom via webben', '');
    notify_role('project', 'Ny partneransökan: ' . trim($company), trim($specialty), 'leverantorer.php');
} catch (Throwable $ex) {
    error_log('CRM supplier creation failed: ' . $ex->getMessage());
}

$subject = "Partneransökan: " . safe($company) . " – " . safe($specialty);
$body = '';
$fields = [
    'Företag'           => safe($company),
    'Org.nummer'        => safe($orgnr) ?: '–',
    'Kontaktperson'     => safe($contact),
    'Telefon'           => safe($phone),
    'E-post'            => safe($email),
    'Specialitet'       => safe($specialty),
    'Verksamhetsområde' => safe($areaTxt) ?: '–',
];
foreach ($fields as $label => $value) {
    $body .= "<div class=\"field\"><div class=\"label\">{$label}</div><div class=\"value\">{$value}</div></div>";
}
if (!empty($about)) {
    $body .= "<div class=\"field\"><div class=\"label\">Om företaget</div><div class=\"value\" style=\"white-space:pre-line\">" . safe($about) . "</div></div>";
}

$result = sendMail($subject, $body, $email, safe($contact));

if ($result['success']) {
    $autoBody = "<div class=\"field\"><div class=\"value\">Hej " . safe($contact) . ",<br><br>Tack för din partneransökan till M2 Bygg Team AB! Vi granskar uppgifterna och återkommer inom <strong>2 arbetsdagar</strong>.<br><br>Med vänliga hälsningar,<br><strong>M2 Bygg Team AB</strong><br>031-96 88 88</div></div>";
    sendMail('Partneransökan mottagen – M2 Bygg Team AB', $autoBody, MAIL_TO, SMTP_FROM_NAME);
}

echo json_encode($result);
