# M2 Platform

Komplett webbplattform för **M2 Bygg Team AB** — marknadsföringssajt + CRM/affärssystem.
Byggd i ren PHP 8 för cPanel shared hosting. Inga ramverk, inga byggsteg, noll konfiguration.

## Innehåll

```
├── index.php, kontakt.php, ...     Webbplats (34 sidor)
├── tjanster/                       11 tjänstesidor
├── goteborg/ ... molnlycke/        10 lokala SEO-sidor
├── blogg/                          Blogg
├── includes/                       Delad header/footer (redigera EN gång)
├── css/ js/                        En CSS + en JS för hela sajten
├── send/                           PHPMailer SMTP + CRM-automation
├── crm/                            ★ M2 Platform CRM (Phase 1)
│   ├── leads, kunder, offerter, projekt, fakturor, leverantörer
│   ├── RBAC (5 roller), automation engine, granskningslogg
│   └── SQLite (auto) eller MySQL (en rad i config.php)
└── data/                           Databas (gitignorad, skapas automatiskt)
```

## Snabbstart lokalt

```bash
./start-local.sh        # Mac/Linux  (Windows: start-local.bat)
```
- Webbplats: http://localhost:8080
- CRM: http://localhost:8080/crm/login.php — `admin@m2team.se` / `admin123`

## Deployment

Se **[DEPLOY.md](DEPLOY.md)** — komplett guide: lokal testning → GitHub → cPanel.

## CRM-automation

| Händelse | Automatik |
|---|---|
| Offertformulär på webben | → Lead i CRM + notis till sälj |
| Offert accepteras | → Kund + Projekt + Fakturautkast skapas |
| Betalning registreras | → Fakturastatus uppdateras (delbetald/betald) |
| Projekt slutförs | → Recensionsförfrågan till support |
| Partneransökan | → Leverantör (väntar verifiering) |

## Säkerhet

bcrypt-lösenord · CSRF-skydd · RBAC · prepared statements · sessionshärdning ·
skyddad databas-mapp · noindex på CRM · granskningslogg

---
© M2 Bygg Team AB · 031-96 88 88 · info@m2team.se
