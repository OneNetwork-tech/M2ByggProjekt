# M2 Platform — CRM & Construction Management (Phase 1)

Komplett CRM-system för M2 Bygg Team AB enligt produkt-blueprint.
PHP 8 + SQLite (noll konfiguration) eller MySQL. Byggd för cPanel shared hosting.

## Inloggning

URL: `https://www.m2team.se/crm/login.php`

**Standardkonto (BYT DIREKT EFTER FÖRSTA INLOGGNING):**
- E-post: `admin@m2team.se`
- Lösenord: `admin123`

Byt lösenord under **Användare → Lösenord** efter första inloggningen.

## Moduler (Phase 1 enligt blueprint)

| Modul | Fil | Funktioner |
|---|---|---|
| Dashboard | `index.php` | KPI:er: leads/mån, konvertering, offertacceptans, intäkter, aktiva projekt |
| Lead Management | `leads.php`, `lead.php` | Kanban med drag & drop, 7 stadier, tidslinje, kommunikationslogg, konvertera till kund |
| Kunder | `kunder.php`, `kund.php` | Profil, projekt, offerter, fakturor, historik, livstidsvärde |
| Offerter | `offerter.php`, `offert.php`, `offert-pdf.php` | Radbyggare, automatisk ROT-beräkning, fastpris, PDF/utskrift, statusflöde |
| Projekt | `projekt.php`, `projekt-detalj.php` | 8-stegs statuspipeline, tidslinje, leverantörstilldelning, förlopp |
| Fakturor | `fakturor.php`, `faktura.php` | Moms 25%, ROT-avdrag, delbetalningar, förfallodatum, utskrift |
| Leverantörer | `leverantorer.php` | Registrering, verifieringsstatus, projektkoppling |
| Kommunikation | `meddelanden.php` | Samlad tidslinje över alla entiteter + notiser |
| Användare | `anvandare.php` | RBAC: Super Admin, Sälj, Projekt, Ekonomi, Support |
| Inställningar | `installningar.php` | Granskningslogg, DB-statistik, systeminfo |

## Automation Engine (enligt blueprint)

| Trigger | Automatisk åtgärd |
|---|---|
| Webbformulär skickas | Lead skapas (L-YYYY-XXXX) + tidslinje + notis till sälj |
| Partnerformulär skickas | Leverantör skapas som "Väntar verifiering" + notis |
| Offert accepteras | Kund skapas (om lead) → Lead = Vunnen → Projekt skapas → Fakturautkast genereras med alla rader |
| Projekt slutförs | Notis till support: be kund om recension |
| Betalning registreras | Fakturastatus uppdateras automatiskt (delbetald/betald) |
| Förfallodatum passerat | Status → Förfallen |

## Numrering

- Leads: `L-2026-0001`
- Offerter: `O-2026-0001`
- Projekt: `P-2026-0001`
- Fakturor: `F-2026-0001`

## ROT-beräkning

30% av arbetskostnaden **inkl. moms**, max 50 000 kr/person/år.
Markera rader som "Arbete" i offertbyggaren — material exkluderas automatiskt.

## Databas

**SQLite (standard):** Noll konfiguration. Skapas automatiskt i `/data/m2platform.sqlite` vid första besöket. Skyddad via `.htaccess`.

**Byt till MySQL** (rekommenderas vid hög volym):
1. Skapa databas + användare i cPanel → MySQL Databases
2. I `crm/config.php`: ändra `DB_DRIVER` till `'mysql'` och fyll i `DB_HOST/DB_NAME/DB_USER/DB_PASS`
3. Tabeller migreras automatiskt vid nästa sidladdning

## Säkerhet

- Lösenord: `password_hash()` (bcrypt)
- Sessioner: HttpOnly, SameSite=Lax, strict mode, 8h livstid
- CSRF-skydd på alla formulär
- RBAC på alla sidor (`require_role()`)
- Prepared statements överallt (SQL injection-säkert)
- `noindex,nofollow` på alla CRM-sidor
- Granskningslogg över alla kritiska händelser

## Deployment-checklista

1. Ladda upp hela projektet till `public_html/`
2. Kontrollera att `/data/` är skrivbar (chmod 755, ägd av webbservern)
3. Logga in på `/crm/login.php` → byt admin-lösenord
4. Skapa användare för teamet med rätt roller
5. Testa: skicka en offertförfrågan via webben → kontrollera att lead dyker upp

## Kommande faser (per blueprint)

- **Phase 2:** Kundportal, leverantörsportal med jobbacceptans
- **Phase 3:** Avtal med digital signering, nyhetsbrev, avancerad rapportering
- **Phase 4:** Mobiloptimering (bottom nav finns redan), SMS-gateway
- **Phase 5:** AI-assistent, prediktiv analys
