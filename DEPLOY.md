# M2 Platform — Deployment Guide

Arbetsflöde: **Lokal testning → zip → manuell uppladdning i cPanel File Manager**

GitHub används endast som källkodsbackup/historik — inte för automatisk deploy.
Inga git-baserade deploy-metoder (cPanel Git Version Control, Actions, FTP-deploy) används,
eftersom databasen och uppladdade filer på servern aldrig får skrivas över av ett deploy-steg.

---

## STEG 1 — Testa lokalt

### 1.1 Installera PHP 8

|OS|Kommando|
|-|-|
|**Mac**|`brew install php`|
|**Ubuntu/Debian**|`sudo apt install php-cli php-sqlite3`|
|**Windows**|Ladda ner från [windows.php.net/download](https://windows.php.net/download), lägg till i PATH. Aktivera `extension=pdo_sqlite` i `php.ini`|

Verifiera: `php -v` → ska visa PHP 8.x

### 1.2 Starta lokal server

```bash
# Mac/Linux
./start-local.sh

# Windows
start-local.bat

# Eller manuellt:
php -S localhost:8080 router.php
```

> `router.php` emulerar `.htaccess`-reglerna (rena URL:er som `/kontakt`) lokalt.
> Den används ALDRIG i produktion — där sköter Apache + `.htaccess` allt.

### 1.3 Testchecklista

|Test|URL|Förväntat|
|-|-|-|
|Startsida|`http://localhost:8080/`|Sidan renderas, meny + footer|
|Ren URL|`http://localhost:8080/kontakt`|Kontaktsidan (utan .php)|
|Tjänstesida|`http://localhost:8080/tjanster/takbyte`|Takbyte-sidan|
|404|`http://localhost:8080/finns-inte`|Egen 404-sida|
|**CRM login**|`http://localhost:8080/crm/login.php`|Mörk inloggningssida|
|**CRM**|Logga in: `admin@m2team.se` / `admin123`|Dashboard, DB skapas automatiskt i `/data/`|
|CRM flöde|Skapa lead → offert → acceptera|Projekt + faktura genereras automatiskt|

> **OBS:** E-postutskick (SMTP) fungerar inte lokalt förrän ett e-postkonto lagts till i
> `/crm/installningar.php` (eller riktiga SMTP-uppgifter finns) — det är normalt.
> CRM-leaden skapas ändå; endast mejlet misslyckas tyst (loggas i Inställningar → Notisloggar).

### 1.4 Nollställ lokal testdata

```bash
rm data/m2platform.sqlite*
```

Databasen återskapas tom (med admin-kontot) vid nästa CRM-besök.

---

## STEG 2 — Spara till GitHub (källkodshistorik, valfritt men rekommenderat)

```bash
cd m2-platform
git add .
git commit -m "Beskriv ändringen"
git push
```

> `.gitignore` ser till att databasen (`data/*.sqlite`), uppladdningar och lokala
> konfigfiler (`crm/config.local.php`, `send/config.local.php`) ALDRIG hamnar på GitHub.

---

## STEG 3 — Deploya till cPanel (manuell zip-uppladdning)

### 3.1 Skapa deploy-paketet lokalt

Zippa projektmappen **utan** följande (de ska inte överskriva live-data eller läcka secrets):

- `data/` (live-databasen ligger redan på servern — skriv aldrig över den)
- `uploads/portfolio/`, `uploads/services/` (CRM-adminuppladdat innehåll, redan på servern)
- `crm/config.local.php` / `send/config.local.php` (riktiga lösenord — skapas direkt på servern, aldrig i zip-filen)
- `.git/`, `.github/`

### 3.2 Ladda upp

1. cPanel → **File Manager** → gå till `public_html/`
2. **Upload** → välj zip-filen
3. Markera den uppladdade zip-filen → **Extract** (skriver över ändrade filer, rör inte `data/` eller `uploads/portfolio|services` eftersom de inte ingick i zip-filen)
4. Ta bort zip-filen efter extrahering

### 3.3 Engångskonfiguration på servern (görs en gång, inte vid varje deploy)

**a) Databas (MySQL):** skapa `crm/config.local.php` direkt i File Manager (denna fil ska
ALDRIG checkas in i git):

```php
<?php
define('DB_DRIVER', 'mysql');
define('DB_HOST', 'localhost');
define('DB_NAME', 'mteamse_platform');
define('DB_USER', 'mteamse_crm');
define('DB_PASS', 'ditt-riktiga-lösenord');
```

Tabeller + admin-konto (`admin@m2team.se` / `admin123`) skapas automatiskt vid nästa
besök på `/crm/`.

**b) SMTP (e-postutskick):** logga in i CRM → **Inställningar** → **E-postkonton (SMTP)**
→ lägg till kontot (mail.m2team.se, port 465, SSL, användarnamn + lösenord från
hostingleverantören) → markera som standardkonto → klicka **Testa** för att verifiera.
Flera konton kan läggas till (t.ex. ett separat Outlook/Microsoft 365-konto). Systemmejl
till kunder/leverantörer visas alltid som från `noreply@m2team.se` (svar går till
`info@m2team.se`) oavsett vilket SMTP-konto som relayar dem.

**c) Cron jobs** (cPanel → **Cron Jobs**):

```
0 3 * * *  php /home/ANVANDARNAMN/public_html/crm/cron-backup.php
0 8 * * *  php /home/ANVANDARNAMN/public_html/crm/cron-invoice-reminders.php
```

**d) SSL:** cPanel → SSL/TLS Status → aktivera AutoSSL / Let's Encrypt.

### 3.4 Efter första deployen (engångsåtgärder)

|#|Åtgärd|Var|
|-|-|-|
|1|**Byt CRM-admin-lösenord** (admin@m2team.se / admin123)|`/crm/anvandare.php`|
|2|Lägg till SMTP-konto (se 3.3b)|`/crm/installningar.php`|
|3|Aktivera AutoSSL / Let's Encrypt|cPanel → SSL/TLS Status|
|4|Kontrollera att `/data/` är skrivbar (755)|cPanel → File Manager|
|5|Schemalägg cron jobs (se 3.3c)|cPanel → Cron Jobs|
|6|Testa offertformuläret skarpt → kolla att lead dyker upp i CRM|webbplatsen + `/crm/leads.php`|
|7|Skicka in sitemap|Google Search Console → `https://www.m2team.se/sitemap.xml`|
|8|Skapa teamets CRM-konton med roller|`/crm/anvandare.php`|

### 3.5 Vid varje senare ändring

Repetera STEG 1 (testa lokalt) → STEG 3.1–3.2 (zippa om och extrahera). `data/`,
`uploads/portfolio/`, `uploads/services/` och `*config.local.php` rör du aldrig, så
databasen och kunduppladdat innehåll på servern påverkas inte.

---

## Felsökning

|Problem|Lösning|
|-|-|
|500-fel på alla sidor|PHP-version: cPanel → MultiPHP Manager → välj PHP 8.1+|
|Rena URL:er funkar inte|`.htaccess` saknas (visa dolda filer i File Manager) eller mod_rewrite av|
|CRM: "could not find driver"|cPanel → Select PHP Version → aktivera `pdo_sqlite` (eller `pdo_mysql`)|
|CRM: databas-fel (MySQL)|Kontrollera `crm/config.local.php` finns och har rätt DB_HOST/DB_NAME/DB_USER/DB_PASS|
|CRM: databas-fel (SQLite)|`/data/` ej skrivbar → chmod 755, kontrollera ägare|
|Inloggning misslyckas trots rätt lösenord|Kontrollera att `users`-tabellen faktiskt skapades/seedades i rätt databas — om du bytt från SQLite till MySQL mitt i utvecklingen kan adminkontot finnas i den gamla databasen|
|Mejl skickas inte|Kontrollera kontot under Inställningar → E-postkonton → klicka Testa för felmeddelande. Testa även port 587/TLS om 465/SSL blockeras av hosten|
|Cron jobs körs inte|Kontrollera fullständig sökväg till `php`-binären (`which php` i cPanel Terminal) och att skriptsökvägen matchar ditt cPanel-användarnamn|
