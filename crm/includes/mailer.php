<?php
/**
 * M2 Platform — CRM transactional mailer
 * General-purpose sender for portal invites, quotes, invoices, messages, reminders.
 * Reuses the same PHPMailer/SMTP credentials as send/mailer.php but can address
 * any recipient (not just the company inbox) and logs every send to notifications_log.
 */

require_once __DIR__ . '/db.php';

$_phpmailer_path = dirname(__DIR__, 2) . '/vendor/phpmailer/src/PHPMailer.php';
if (file_exists($_phpmailer_path)) {
    require_once $_phpmailer_path;
    require_once dirname(__DIR__, 2) . '/vendor/phpmailer/src/SMTP.php';
    require_once dirname(__DIR__, 2) . '/vendor/phpmailer/src/Exception.php';
} elseif (file_exists(dirname(__DIR__, 2) . '/vendor/autoload.php')) {
    require_once dirname(__DIR__, 2) . '/vendor/autoload.php';
}

@include dirname(__DIR__, 2) . '/send/config.local.php';

if (!defined('SMTP_HOST'))      define('SMTP_HOST', 'mail.m2team.se');
if (!defined('SMTP_PORT'))      define('SMTP_PORT', 465);
if (!defined('SMTP_USER'))      define('SMTP_USER', 'noreply@m2team.se');
if (!defined('SMTP_PASS'))      define('SMTP_PASS', defined('SMTP_PASS_OVERRIDE') ? SMTP_PASS_OVERRIDE : 'PASSWORD');
if (!defined('SMTP_FROM'))      define('SMTP_FROM', 'noreply@m2team.se');
if (!defined('SMTP_FROM_NAME')) define('SMTP_FROM_NAME', 'M2 Bygg Team AB');

/**
 * Send a transactional email to any recipient and log the result.
 *
 * @param string $entityType e.g. 'customer','supplier','quote','invoice' (for the log)
 * @param int    $entityId
 */
function crm_send_mail(string $to, string $toName, string $subject, string $bodyHtml, string $entityType = '', int $entityId = 0, ?string $ctaUrl = null, ?string $ctaLabel = null, ?int $accountId = null, ?string $replyTo = 'info@m2team.se'): bool {
    $ok = false;
    $err = '';

    $account  = $accountId ? get_email_account($accountId) : get_default_email_account();
    $host     = $account['host']       ?? SMTP_HOST;
    $port     = (int)($account['port'] ?? SMTP_PORT);
    $username = $account['username']   ?? SMTP_USER;
    $password = $account['password']   ?? SMTP_PASS;
    $enc      = $account['encryption'] ?? 'ssl';

    // System emails to customers/suppliers always identify as noreply@m2team.se with
    // info@m2team.se as reply-to, regardless of which SMTP account relays the message —
    // unless an account is explicitly chosen ($accountId set), in which case that
    // account's own sender identity is used intentionally instead.
    if ($accountId && $account) {
        $fromMail = $account['from_email'];
        $fromName = $account['from_name'];
    } else {
        $fromMail = SMTP_FROM;
        $fromName = SMTP_FROM_NAME;
    }

    if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
        $err = 'Ogiltig e-postadress';
    } elseif (!class_exists('PHPMailer\\PHPMailer\\PHPMailer')) {
        $err = 'PHPMailer saknas';
    } else {
        $mail = new PHPMailer\PHPMailer\PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host       = $host;
            $mail->SMTPAuth   = true;
            $mail->Username   = $username;
            $mail->Password   = $password;
            $mail->SMTPSecure = $enc === 'tls' ? PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS : PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port       = $port;
            $mail->CharSet    = 'UTF-8';
            $mail->setFrom($fromMail, $fromName);
            if ($replyTo && filter_var($replyTo, FILTER_VALIDATE_EMAIL)) {
                $mail->addReplyTo($replyTo, 'M2 Bygg Team AB');
            }
            $mail->addAddress($to, $toName);
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = crm_mail_template($subject, $bodyHtml, $ctaUrl, $ctaLabel);
            $mail->AltBody = strip_tags(str_replace(['<br>', '<br/>', '</p>'], "\n", $bodyHtml));
            $mail->send();
            $ok = true;
        } catch (Throwable $e) {
            $err = $mail->ErrorInfo ?: $e->getMessage();
            error_log('crm_send_mail: ' . $err);
        }
    }

    db()->prepare(
        "INSERT INTO notifications_log (channel, recipient, subject, entity_type, entity_id, status, error) VALUES ('email',?,?,?,?,?,?)"
    )->execute([$to, $subject, $entityType, $entityId, $ok ? 'sent' : 'failed', $err]);

    return $ok;
}

function crm_mail_template(string $subject, string $body, ?string $ctaUrl = null, ?string $ctaLabel = null): string {
    $cta = '';
    if ($ctaUrl) {
        $label = htmlspecialchars($ctaLabel ?: 'Öppna', ENT_QUOTES, 'UTF-8');
        $cta = '<a href="' . htmlspecialchars($ctaUrl, ENT_QUOTES, 'UTF-8') . '" class="cta-btn">' . $label . '</a>';
    }
    return <<<HTML
    <!DOCTYPE html><html lang="sv"><head><meta charset="UTF-8">
    <style>
      body{font-family:'Helvetica Neue',Arial,sans-serif;background:#F6F4F0;margin:0;padding:0}
      .wrapper{max-width:600px;margin:32px auto}
      .header{background:#111318;padding:28px 32px;border-radius:12px 12px 0 0}
      .logo-mark{height:34px;width:auto;vertical-align:middle;display:inline-block}
      .logo-text{display:inline-block;vertical-align:middle;margin-left:10px}
      .logo-tag{font-size:11px;color:rgba(246,244,240,.4);letter-spacing:.1em;text-transform:uppercase}
      .content{background:#fff;padding:36px 32px;border-radius:0 0 12px 12px}
      h2{font-family:Georgia,serif;color:#111318;font-size:22px;margin:0 0 20px}
      .cta-btn{display:inline-block;background:#B5712A;color:#fff;padding:12px 24px;border-radius:8px;font-weight:600;font-size:14px;text-decoration:none;margin-top:20px}
      .footer-note{font-size:12px;color:#9CA3AF;margin-top:24px;text-align:center;line-height:1.6}
    </style></head><body>
      <div class="wrapper">
        <div class="header"><img class="logo-mark" src="https://m2team.se/assets/images/M2-AB-logotyp-wht.png" alt="M2 Bygg Team AB"><span class="logo-text"><span class="logo-tag">Göteborg · Hisings Backa</span></span></div>
        <div class="content"><h2>{$subject}</h2>{$body}{$cta}</div>
        <p class="footer-note">M2 Bygg Team AB · Lillhagsvägen 88, 442 43 Hisings Backa<br>031-96 88 88 · info@m2team.se · www.m2team.se</p>
      </div>
    </body></html>
    HTML;
}

/**
 * Notify staff with a given role both in-app and by email.
 */
function notify_role_email(string $role, string $title, string $body, string $link = '', string $entityType = '', int $entityId = 0): void {
    notify_role($role, $title, $body, $link);
    $users = db()->prepare("SELECT name, email FROM users WHERE role IN (?, 'super_admin') AND active = 1 AND email IS NOT NULL AND email != ''");
    $users->execute([$role]);
    $fullUrl = $link ? (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . '/crm/' . ltrim($link, '/') : null;
    foreach ($users->fetchAll() as $u) {
        crm_send_mail($u['email'], $u['name'], $title, '<p>' . htmlspecialchars($body, ENT_QUOTES, 'UTF-8') . '</p>', $entityType, $entityId, $fullUrl, 'Öppna i CRM');
    }
}

/**
 * Send an SMS. Stub interface ready for 46elks/Twilio — set SMS_PROVIDER in config.
 * Logs to notifications_log regardless of whether a real provider is configured.
 */
function crm_send_sms(string $to, string $message, string $entityType = '', int $entityId = 0): bool {
    $ok = false; $err = '';

    if (!defined('SMS_PROVIDER_USER') || !defined('SMS_PROVIDER_PASS')) {
        $err = 'SMS-leverantör ej konfigurerad (se crm/config.php)';
    } else {
        // 46elks API (Swedish SMS gateway) — swap for Twilio etc. as needed.
        $ch = curl_init('https://api.46elks.com/a1/SMS');
        curl_setopt_array($ch, [
            CURLOPT_USERPWD => SMS_PROVIDER_USER . ':' . SMS_PROVIDER_PASS,
            CURLOPT_POSTFIELDS => http_build_query([
                'from'    => defined('SMS_FROM') ? SMS_FROM : 'M2Team',
                'to'      => $to,
                'message' => $message,
            ]),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 10,
        ]);
        $resp = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($resp !== false && $code >= 200 && $code < 300) {
            $ok = true;
        } else {
            $err = "SMS-fel (HTTP $code): " . substr((string)$resp, 0, 200);
        }
    }

    db()->prepare(
        "INSERT INTO notifications_log (channel, recipient, subject, entity_type, entity_id, status, error) VALUES ('sms',?,?,?,?,?,?)"
    )->execute([$to, mb_strimwidth($message, 0, 60, '…'), $entityType, $entityId, $ok ? 'sent' : 'failed', $err]);

    return $ok;
}
