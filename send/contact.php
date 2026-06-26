<?php
/**
 * M2 Bygg Team AB — Contact / Offert Form Handler
 * POST endpoint: /send/contact.php
 * AUTOMATION: Lead Submitted → Create Lead in CRM → Notify Sales
 */

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
    exit;
}

$origin = $_SERVER['HTTP_ORIGIN'] ?? $_SERVER['HTTP_REFERER'] ?? '';
if (!empty($origin) && strpos($origin, 'm2team.se') === false && strpos($origin, 'localhost') === false) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Forbidden.']);
    exit;
}

require_once __DIR__ . '/mailer.php';
require_once __DIR__ . '/../crm/includes/db.php';

if (!rate_limit_check('contact_form', $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0', 8, 10)) {
    http_response_code(429);
    echo json_encode(['success' => false, 'message' => 'För många förfrågningar. Försök igen om en stund eller ring oss direkt.']);
    exit;
}
rate_limit_record('contact_form', $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0', false);

$fname    = post('fname');
$lname    = post('lname');
$phone    = post('phone');
$email    = post('email');
$service  = post('service');
$city     = post('city');
$area     = post('area');
$message  = post('message');
$calltime = post('calltime');
$source   = post('source');

$errors = [];
if (empty($fname))   $errors[] = 'Förnamn saknas.';
if (empty($phone))   $errors[] = 'Telefonnummer saknas.';
if (empty($service)) $errors[] = 'Tjänst saknas.';
if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Ogiltig e-postadress.';

// Honeypot
if (!empty(post('website'))) { echo json_encode(['success' => true, 'message' => 'Tack!']); exit; }

if (!empty($errors)) {
    echo json_encode(['success' => false, 'message' => implode(' ', $errors)]);
    exit;
}

$name = safe($fname) . ' ' . safe($lname);

/* ════════ CRM AUTOMATION: create lead ════════ */
$leadNo = null;
try {
    require_once __DIR__ . '/../crm/includes/db.php';
    $leadNo = next_number('L', 'leads', 'lead_no');
    $msgFull = trim($message);
    if ($area)     $msgFull .= "\n\nCa yta: " . $area;
    if ($calltime) $msgFull .= "\nBästa tid att ringa: " . $calltime;

    db()->prepare("INSERT INTO leads (lead_no, name, email, phone, city, service, source, message, stage) VALUES (?,?,?,?,?,?,?,?,'new')")
        ->execute([$leadNo, trim($fname . ' ' . $lname), trim($email), trim($phone), trim($city), trim($service),
                   $source ?: 'Webbformulär', $msgFull]);
    $leadId = db()->lastInsertId();
    log_timeline('lead', $leadId, 'system', 'Lead inkom via webbformulär', 'Källa: ' . ($source ?: 'Webbformulär'));
    notify_role('sales', 'Ny lead: ' . trim($fname . ' ' . $lname), $service . ($city ? ' i ' . $city : ''), 'lead.php?id=' . $leadId);
} catch (Throwable $ex) {
    // CRM unavailable — email still goes out
    error_log('CRM lead creation failed: ' . $ex->getMessage());
}

/* ════════ EMAIL via SMTP ════════ */
$subject = "Offertförfrågan från {$name} – " . safe($service) . ($leadNo ? " ($leadNo)" : '');

$body = '';
$fields = [
    'Namn'                => $name,
    'Telefon'             => safe($phone),
    'E-post'              => safe($email) ?: '–',
    'Tjänst'              => safe($service),
    'Ort / adress'        => safe($city) ?: '–',
    'Ca yta'              => safe($area) ?: '–',
    'Bästa tid att ringa' => safe($calltime) ?: '–',
    'Källa'               => safe($source) ?: 'Webbformulär',
];
if ($leadNo) $fields['CRM Lead'] = $leadNo;

foreach ($fields as $label => $value) {
    $body .= "<div class=\"field\"><div class=\"label\">{$label}</div><div class=\"value\">{$value}</div></div>";
}
if (!empty($message)) {
    $body .= "<div class=\"field\"><div class=\"label\">Meddelande</div><div class=\"value\" style=\"white-space:pre-line\">" . safe($message) . "</div></div>";
}

$result = sendMail($subject, $body, !empty($email) ? $email : null, $name);

// Auto-reply to customer
if ($result['success'] && !empty($email)) {
    $autoBody = "
    <div class=\"field\"><div class=\"value\">
      Hej {$name},<br><br>
      Tack för din offertförfrågan! Vi återkommer med ett fast pris inom <strong>24 timmar</strong>.<br><br>
      Snabbare svar? Ring oss direkt:<br>
      📞 <strong>031-96 88 88</strong><br>
      📱 <strong>0732-40 50 26</strong><br><br>
      Med vänliga hälsningar,<br><strong>M2 Bygg Team AB</strong>
    </div></div>";
    sendMail('Vi har tagit emot din förfrågan – M2 Bygg Team AB', $autoBody, MAIL_TO, SMTP_FROM_NAME);
}

echo json_encode($result);
