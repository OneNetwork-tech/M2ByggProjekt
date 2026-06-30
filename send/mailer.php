<?php
/**
 * M2 Bygg Team AB — SMTP Mail Sender
 * Uses PHPMailer with SSL SMTP on port 465
 *
 * Usage:
 *   require_once __DIR__ . '/../send/mailer.php';
 *   $result = sendMail($to, $subject, $htmlBody, $replyTo);
 */

// PHPMailer via Composer autoload OR manual require
$phpmailer_path = __DIR__ . '/../vendor/phpmailer/src/PHPMailer.php';
if (file_exists($phpmailer_path)) {
    require_once $phpmailer_path;
    require_once __DIR__ . '/../vendor/phpmailer/src/SMTP.php';
    require_once __DIR__ . '/../vendor/phpmailer/src/Exception.php';
} elseif (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require_once __DIR__ . '/../vendor/autoload.php';
} else {
    // Fallback error - PHPMailer not found
    error_log('PHPMailer not found. Run: composer require phpmailer/phpmailer');
    die(json_encode(['success' => false, 'message' => 'Mail library not installed.']));
}

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../crm/includes/db.php';

// ── SMTP CREDENTIALS ──────────────────────────────────────────
// Default account is configured in crm/installningar.php (stored in the email_accounts
// table). These constants are only a fallback for when no account has been set up yet.
define('SMTP_HOST',     'mail.m2team.se');
define('SMTP_PORT',     465);
define('SMTP_USER',     'noreply@m2team.se');
// Secret override: create send/config.local.php (gitignored) with
//   <?php define('SMTP_PASS_OVERRIDE', 'real-password');
@include __DIR__ . '/config.local.php';
define('SMTP_PASS', defined('SMTP_PASS_OVERRIDE') ? SMTP_PASS_OVERRIDE : 'PASSWORD');
define('SMTP_FROM',     'noreply@m2team.se');
define('SMTP_FROM_NAME','M2 Bygg Team AB');

$_send_mailer_account = get_default_email_account();
define('MAIL_TO',       'info@m2team.se');      // where form emails land
define('MAIL_TO_NAME',  'M2 Bygg Team AB');

/**
 * Send an email via SMTP.
 *
 * @param string      $subject
 * @param string      $htmlBody   Full HTML body
 * @param string|null $replyTo    Optional reply-to address (customer's email)
 * @param string|null $replyName  Optional reply-to name
 * @return array ['success' => bool, 'message' => string]
 */
function sendMail(string $subject, string $htmlBody, ?string $replyTo = null, ?string $replyName = null): array
{
    global $_send_mailer_account;
    $account  = $_send_mailer_account;
    $host     = $account['host']       ?? SMTP_HOST;
    $port     = (int)($account['port'] ?? SMTP_PORT);
    $username = $account['username']   ?? SMTP_USER;
    $password = $account['password']   ?? SMTP_PASS;
    $fromMail = $account['from_email'] ?? SMTP_FROM;
    $fromName = $account['from_name']  ?? SMTP_FROM_NAME;
    $enc      = $account['encryption'] ?? 'ssl';

    $mail = new PHPMailer(true);

    try {
        // Server config
        $mail->isSMTP();
        $mail->Host       = $host;
        $mail->SMTPAuth   = true;
        $mail->Username   = $username;
        $mail->Password   = $password;
        $mail->SMTPSecure = $enc === 'tls' ? PHPMailer::ENCRYPTION_STARTTLS : PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port       = $port;
        $mail->CharSet    = 'UTF-8';
        $mail->Encoding   = 'base64';

        // Uncomment for debug during development:
        // $mail->SMTPDebug = SMTP::DEBUG_SERVER;

        // Recipients
        $mail->setFrom($fromMail, $fromName);
        $mail->addAddress(MAIL_TO, MAIL_TO_NAME);

        if ($replyTo && filter_var($replyTo, FILTER_VALIDATE_EMAIL)) {
            $mail->addReplyTo($replyTo, $replyName ?? $replyTo);
        }

        // Content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = wrapEmailHtml($subject, $htmlBody);
        $mail->AltBody = strip_tags(str_replace(['<br>', '<br/>', '</p>', '</div>'], "\n", $htmlBody));

        $mail->send();
        return ['success' => true, 'message' => 'Meddelande skickat.'];

    } catch (Exception $e) {
        error_log('PHPMailer error: ' . $mail->ErrorInfo);
        return ['success' => false, 'message' => 'E-postfel: ' . $mail->ErrorInfo];
    }
}

/**
 * Wrap body HTML in a branded email template.
 */
function wrapEmailHtml(string $subject, string $body): string
{
    return <<<HTML
    <!DOCTYPE html>
    <html lang="sv">
    <head>
      <meta charset="UTF-8">
      <meta name="viewport" content="width=device-width, initial-scale=1.0">
      <title>{$subject}</title>
      <style>
        body { font-family: 'Helvetica Neue', Arial, sans-serif; background: #F6F4F0; margin: 0; padding: 0; }
        .wrapper { max-width: 600px; margin: 32px auto; }
        .header { background: #111318; padding: 28px 32px; border-radius: 12px 12px 0 0; }
        .logo-mark { height: 34px; width: auto; vertical-align: middle; display: inline-block; }
        .logo-text { display: inline-block; vertical-align: middle; margin-left: 10px; }
        .logo-tag  { font-size: 11px; color: rgba(246,244,240,.4); letter-spacing: .1em; text-transform: uppercase; }
        .content { background: #ffffff; padding: 36px 32px; border-radius: 0 0 12px 12px; }
        h2 { font-family: Georgia, serif; color: #111318; font-size: 22px; margin: 0 0 20px; }
        .field { margin-bottom: 16px; padding-bottom: 16px; border-bottom: 1px solid #E8DCC8; }
        .field:last-child { border-bottom: none; }
        .label { font-size: 11px; font-weight: 600; color: #6B7280; letter-spacing: .1em; text-transform: uppercase; margin-bottom: 4px; }
        .value { font-size: 15px; color: #111318; line-height: 1.6; }
        .badge { display: inline-block; background: rgba(181,113,42,.12); color: #B5712A;
          font-size: 12px; font-weight: 600; padding: 4px 10px; border-radius: 99px; margin-bottom: 20px; }
        .cta-btn { display: inline-block; background: #B5712A; color: #ffffff;
          padding: 12px 24px; border-radius: 8px; font-weight: 600; font-size: 14px;
          text-decoration: none; margin-top: 20px; }
        .footer-note { font-size: 12px; color: #9CA3AF; margin-top: 24px; text-align: center; line-height: 1.6; }
      </style>
    </head>
    <body>
      <div class="wrapper">
        <div class="header">
          <div>
            <img class="logo-mark" src="https://m2team.se/assets/images/M2-AB-logotyp-wht.png" alt="M2 Bygg Team AB">
            <span class="logo-text">
              <span class="logo-tag">Göteborg · Hisings Backa</span>
            </span>
          </div>
        </div>
        <div class="content">
          <div class="badge">Ny förfrågan</div>
          <h2>{$subject}</h2>
          {$body}
          <a href="mailto:info@m2team.se" class="cta-btn">Svara på förfrågan</a>
        </div>
        <p class="footer-note">
          M2 Bygg Team AB · Lillhagsvägen 88, 442 43 Hisings Backa<br>
          031-96 88 88 · info@m2team.se · www.m2team.se
        </p>
      </div>
    </body>
    </html>
HTML;
}

/**
 * Sanitize a string for safe output in email HTML.
 */
function safe(string $val): string
{
    return htmlspecialchars(strip_tags(trim($val)), ENT_QUOTES, 'UTF-8');
}

/**
 * Get POST value safely.
 */
function post(string $key, string $default = ''): string
{
    return isset($_POST[$key]) ? trim($_POST[$key]) : $default;
}
