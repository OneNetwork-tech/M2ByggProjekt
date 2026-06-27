# M2 Platform — Deployment Guide

Komplett arbetsflöde: **Lokal testning → GitHub → cPanel Shared Hosting**

```
┌─────────────┐      git push      ┌─────────────┐    auto-deploy    ┌─────────────┐
│   LOKALT    │ ─────────────────► │   GITHUB    │ ────────────────► │   CPANEL    │
│ php -S      │                    │  main branch│                   │ public\_html │
└─────────────┘                    └─────────────┘                   └─────────────┘
```

\---

## STEG 1 — Testa lokalt

### 1.1 Installera PHP 8

|OS|Kommando|
|-|-|
|**Mac**|`brew install php`|
|**Ubuntu/Debian**|`sudo apt install php-cli php-sqlite3`|
|**Windows**|Ladda ner från [windows.php.net/download](https://windows.php.net/download), lägg till i PATH. Aktivera `extension=pdo\_sqlite` i `php.ini`|

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
|Lokal SEO|`http://localhost:8080/goteborg`|Göteborg-sidan|
|404|`http://localhost:8080/finns-inte`|Egen 404-sida|
|**CRM login**|`http://localhost:8080/crm/login.php`|Mörk inloggningssida|
|**CRM**|Logga in: `admin@m2team.se` / `admin123`|Dashboard, DB skapas automatiskt i `/data/`|
|CRM flöde|Skapa lead → offert → acceptera|Projekt + faktura genereras automatiskt|

> \*\*OBS:\*\* E-postutskick (SMTP) fungerar inte lokalt utan riktiga SMTP-uppgifter — det är normalt.
> CRM-leaden skapas ändå; endast mejlet misslyckas tyst.

### 1.4 Nollställ lokal testdata

```bash
rm data/m2platform.sqlite\*
```

Databasen återskapas tom (med admin-kontot) vid nästa CRM-besök.

\---

## STEG 2 — Synka med GitHub

### 2.1 Skapa repository

1. Gå till [github.com/new](https://github.com/new)
2. Namn: `m2-platform` → **Private** → Create repository (utan README)

### 2.2 Initiera och pusha

```bash
cd m2-platform              # projektmappen
git init
git add .
git commit -m "M2 Platform v1.0 – website + CRM"
git branch -M main
git remote add origin https://github.com/DITT-ANVANDARNAMN/m2-platform.git
git push -u origin main
```

> `.gitignore` ser till att databasen (`data/\*.sqlite`), uppladdningar och
> lokala konfigfiler ALDRIG hamnar på GitHub.

### 2.3 Dagligt arbetsflöde

```bash
git add .
git commit -m "Beskriv ändringen"
git push
```

\---

## STEG 3 — Deploya till cPanel

Via cPanel Git Version Control — inga FTP-lösenord i GitHub.

\---

### cPanel Git Version Control

cPanel klonar repot direkt från GitHub och kör `.cpanel.yml` vid varje deploy.

#### A.1 Ge cPanel åtkomst till privat repo

1. cPanel → **Git Version Control** → kopiera SSH-nyckeln
(eller Terminal: `cat \~/.ssh/id\_rsa.pub`, skapa med `ssh-keygen` om saknas)
2. GitHub → repo → **Settings → Deploy keys → Add deploy key**
→ klistra in nyckeln → spara (read-only räcker)

#### A.2 Klona repot i cPanel

1. cPanel → **Git Version Control** → **Create**
2. Clone URL: `git@github.com:DITT-ANVANDARNAMN/m2-platform.git`
3. Repository Path: `repositories/m2-platform` (INTE public\_html!)
4. **Create**

#### A.3 Anpassa deploy-sökvägen

Öppna `.cpanel.yml` i repot och ändra raden till ditt cPanel-användarnamn:

```yaml
- export DEPLOYPATH=/home/DITT\_CPANEL\_ANVANDARNAMN/public\_html/
```

Committa och pusha ändringen.

#### A.4 Deploya

1. cPanel → **Git Version Control** → **Manage** → fliken **Pull or Deploy**
2. Klicka **Update from Remote** (hämtar senaste från GitHub)
3. Klicka **Deploy HEAD Commit** (kör `.cpanel.yml` → kopierar till public\_html)

Vid varje ny ändring: `git push` → upprepa steg A.4. Klart.

> `.cpanel.yml` skriver \*\*aldrig\*\* över den skarpa databasen eller uppladdningar.

\---

## STEG 4 — Efter första deployen (engångsåtgärder)

|#|Åtgärd|Var|
|-|-|-|
|1|**Byt CRM-admin-lösenord** (admin@m2team.se / admin123)|`/crm/anvandare.php`|
|2|Sätt riktiga SMTP-lösenordet (ersätt `PASSWORD`)|`send/mailer.php` på servern\*|
|3|Aktivera AutoSSL / Let's Encrypt|cPanel → SSL/TLS Status|
|4|Kontrollera att `/data/` är skrivbar (755)|cPanel → File Manager|
|5|Testa offertformuläret skarpt → kolla att lead dyker upp i CRM|webbplatsen + `/crm/leads.php`|
|6|Skicka in sitemap|Google Search Console → `https://www.m2team.se/sitemap.xml`|
|7|Skapa teamets CRM-konton med roller|`/crm/anvandare.php`|

\* **SMTP-lösenord:** lägg det ALDRIG i Git. Redigera `send/mailer.php` direkt på
servern via File Manager, ELLER skapa `send/config.local.php` (gitignorad):

```php
<?php define('SMTP\_PASS\_OVERRIDE', 'riktiga-lösenordet');
```

och i `mailer.php` läs: `defined('SMTP\_PASS\_OVERRIDE') ? SMTP\_PASS\_OVERRIDE : 'PASSWORD'`
(filen `@include`:as redan om den finns).

\---

## Databas: SQLite → MySQL (valfritt, vid hög volym)

1. cPanel → **MySQL Databases** → skapa databas + användare, ge ALL PRIVILEGES
2. `crm/config.php`:

```php
   define('DB\_DRIVER', 'mysql');
   define('DB\_HOST', 'localhost');
   define('DB\_NAME', 'm2team\_platform');
   define('DB\_USER', 'm2team\_crm');
   define('DB\_PASS', 'ditt-lösenord');
   ```

3. Tabeller + admin-konto skapas automatiskt vid nästa CRM-besök.

\---

## Felsökning

|Problem|Lösning|
|-|-|
|500-fel på alla sidor|PHP-version: cPanel → MultiPHP Manager → välj PHP 8.1+|
|Rena URL:er funkar inte|`.htaccess` saknas (visa dolda filer i File Manager) eller mod\_rewrite av|
|CRM: "could not find driver"|cPanel → Select PHP Version → aktivera `pdo\_sqlite` (eller `pdo\_mysql`)|
|CRM: databas-fel|`/data/` ej skrivbar → chmod 755, kontrollera ägare|
|Mejl skickas inte|Fel SMTP-lösenord i `send/mailer.php`, eller port 465 blockerad → testa 587/TLS|
|cPanel deploy gör inget|`.cpanel.yml` har fel DEPLOYPATH, eller okommittade ändringar i cPanel-repot|
|"Update from Remote" hämtar inget nytt|SSH-deploy-nyckeln saknas/fel i GitHub → repo Settings → Deploy keys|



