# M2 Bygg Team AB – Installation Guide

## Stack
- PHP 7.4+ (cPanel shared hosting)
- PHPMailer 6.x (included in /vendor/phpmailer/)
- Tailwind CSS (CDN – no build step)
- Vanilla JS (single file: /js/main.js)
- Apache + .htaccess (clean URLs)

## File Structure
```
public_html/
├── index.php           ← Startsida
├── kontakt.php         ← Kontakt & offert
├── om-oss.php          ← Om oss
├── projekt.php         ← Portfolio
├── tjanster/
│   ├── index.php       ← Tjänsteöversikt
│   ├── takbyte.php
│   ├── takrenovering.php
│   └── ... (alla undertjänster)
├── blogg/
│   ├── index.php
│   └── [slug].php
├── includes/
│   ├── header.php      ← Shared header (nav)
│   └── footer.php      ← Shared footer
├── send/
│   ├── mailer.php      ← SMTP config (PHPMailer)
│   ├── contact.php     ← Contact form handler
│   └── partner.php     ← Partner form handler
├── vendor/
│   └── phpmailer/src/  ← PHPMailer library
├── css/
│   └── main.css        ← All styles
├── js/
│   └── main.js         ← All JS
├── img/                ← Images
├── .htaccess           ← URL rewriting + security
└── composer.json
```

## Deployment Steps

### 1. Upload files
Upload all files to `public_html/` via cPanel File Manager or FTP.
Make sure `.htaccess` is uploaded (enable "Show Hidden Files" in File Manager).

### 2. Set SMTP password
Edit `/send/mailer.php` and replace `PASSWORD` on line:
```php
define('SMTP_PASS', 'PASSWORD');
```
with the real password.

### 3. Permissions
```
chmod 755 public_html/
chmod 755 public_html/send/
chmod 644 public_html/send/*.php
chmod 644 public_html/send/mailer.php
```

### 4. Test email
Visit: `https://www.m2team.se/kontakt`
Fill in the form and submit. Check that:
- You receive email at info@m2team.se
- Customer receives auto-reply

### 5. SSL
Activate Let's Encrypt in cPanel → SSL/TLS.
.htaccess will auto-redirect HTTP → HTTPS.

### 6. PHPMailer via Composer (alternative)
If cPanel has Composer support:
```bash
cd public_html
composer install
```
Then update mailer.php line 13 to use autoload:
```php
require_once __DIR__ . '/../vendor/autoload.php';
```

## SMTP Settings Reference
| Setting | Value |
|---|---|
| Username | noreply@m2team.se |
| Password | (set in mailer.php) |
| Outgoing SMTP | mail.m2team.se |
| SMTP Port | 465 (SSL) |
| Incoming IMAP | mail.m2team.se |
| IMAP Port | 993 |
| Encryption | SSL/TLS |

## URL Structure (Clean URLs via .htaccess)
| URL | File |
|---|---|
| / | index.php |
| /kontakt | kontakt.php |
| /om-oss | om-oss.php |
| /projekt | projekt.php |
| /tjanster | tjanster/index.php |
| /tjanster/takbyte | tjanster/takbyte.php |
| /blogg | blogg/index.php |
| /prisguide | prisguide.php |
| /offert | offert.php |
| /faq | faq.php |

---

## M2 Platform CRM (NYTT)

CRM-systemet ligger i `/crm/` och delar hosting med webbplatsen.

- **Inloggning:** `https://www.m2team.se/crm/login.php`
- **Standardkonto:** admin@m2team.se / admin123 — **BYT DIREKT**
- **Databas:** SQLite skapas automatiskt i `/data/` (skyddad). MySQL-växling i `crm/config.php`.
- **Automation:** Webbformulärets leads + partneransökningar hamnar automatiskt i CRM:et.

Full dokumentation: `crm/README.md`
