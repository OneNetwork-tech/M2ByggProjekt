<?php
/**
 * M2 Platform — Configuration
 * Switch DB_DRIVER to 'mysql' for production MySQL on cPanel.
 */

define('APP_NAME',    'M2 Platform');
define('APP_URL',     'https://www.m2team.se/crm');
define('APP_VERSION', '1.0.0');

// ── DATABASE ──────────────────────────────────────────────
define('DB_SQLITE_PATH', __DIR__ . '/../data/m2platform.sqlite');

// Local dev (no config.local.php present) defaults to zero-config SQLite. Production on
// cPanel switches to MySQL via config.local.php — a file that is NOT committed to git (see
// .gitignore) and must be created directly on the server (cPanel File Manager), so the real
// DB_HOST/DB_NAME/DB_USER/DB_PASS never end up in the GitHub repo.
if (file_exists(__DIR__ . '/config.local.php')) {
    require __DIR__ . '/config.local.php';   // must define DB_DRIVER='mysql' + DB_HOST/DB_NAME/DB_USER/DB_PASS
} else {
    define('DB_DRIVER', 'sqlite');
}

// ── SECURITY ──────────────────────────────────────────────
define('SESSION_NAME', 'm2platform_session');
define('SESSION_LIFETIME', 60 * 60 * 8);             // 8 hours
define('CSRF_KEY', 'm2_csrf_token');

// ── COMPANY ───────────────────────────────────────────────
define('COMPANY_NAME',  'M2 Bygg Team AB');
define('COMPANY_PHONE', '031-96 88 88');
define('COMPANY_EMAIL', 'info@m2team.se');
define('COMPANY_ADDR',  'Lillhagsvägen 88, 442 43 Hisings Backa');
define('VAT_RATE', 0.25);                            // 25% moms
define('ROT_RATE', 0.30);                            // 30% ROT på arbete
define('GOOGLE_REVIEW_URL', 'https://g.page/r/review'); // TODO: replace with real Google Business review link

// ── SMS PROVIDER (46elks) — uncomment + fill in to enable SMS ──
// define('SMS_PROVIDER_USER', 'your-46elks-api-username');
// define('SMS_PROVIDER_PASS', 'your-46elks-api-password');
// define('SMS_FROM', 'M2Team');

// ── STRIPE (online invoice payment) ─────────────────────────
// define('STRIPE_SECRET_KEY', 'sk_test_...');
// define('STRIPE_PUBLISHABLE_KEY', 'pk_test_...');
// define('STRIPE_WEBHOOK_SECRET', 'whsec_...');

// ── ACCOUNTING SYNC (Fortnox / Visma eEkonomi) ──────────────
// define('FORTNOX_CLIENT_ID', '...');
// define('FORTNOX_CLIENT_SECRET', '...');
// define('VISMA_CLIENT_ID', '...');
// define('VISMA_CLIENT_SECRET', '...');

// ── ROLES ─────────────────────────────────────────────────
const ROLES = [
  'super_admin' => 'Super Admin',
  'admin'       => 'Admin',
  'sales'       => 'Säljansvarig',
  'project'     => 'Projektledare',
  'finance'     => 'Ekonomi',
  'support'     => 'Kundsupport',
];

// Roles that are considered "admin-tier" — only super_admin may create, promote into,
// demote out of, or deactivate accounts with one of these roles. The 'admin' role itself
// has full app access (see require_role() in crm/includes/auth.php) but is blocked from
// managing other admin-tier accounts, so it can't grant itself or anyone else super_admin.
const ADMIN_TIER_ROLES = ['super_admin', 'admin'];

// ── LEAD STAGES (per blueprint) ───────────────────────────
const LEAD_STAGES = [
  'new'        => ['label' => 'Ny',             'color' => '#0066FF'],
  'contacted'  => ['label' => 'Kontaktad',      'color' => '#7C3AED'],
  'site_visit' => ['label' => 'Besök bokat',    'color' => '#0891B2'],
  'quote_sent' => ['label' => 'Offert skickad', 'color' => '#D97706'],
  'negotiation'=> ['label' => 'Förhandling',    'color' => '#DB2777'],
  'won'        => ['label' => 'Vunnen',         'color' => '#059669'],
  'lost'       => ['label' => 'Förlorad',       'color' => '#6B7280'],
];

// ── PROJECT STATUSES (per blueprint) ──────────────────────
const PROJECT_STATUSES = [
  'lead'        => ['label' => 'Lead',          'color' => '#6B7280'],
  'inspection'  => ['label' => 'Besiktning',    'color' => '#0891B2'],
  'planning'    => ['label' => 'Planering',     'color' => '#7C3AED'],
  'scheduled'   => ['label' => 'Schemalagd',    'color' => '#D97706'],
  'in_progress' => ['label' => 'Pågående',      'color' => '#0066FF'],
  'quality'     => ['label' => 'Kvalitetskontroll','color' => '#DB2777'],
  'completed'   => ['label' => 'Slutförd',      'color' => '#059669'],
  'closed'      => ['label' => 'Stängd',        'color' => '#374151'],
];

// ── QUOTE STATUSES ────────────────────────────────────────
const QUOTE_STATUSES = [
  'draft'    => ['label' => 'Utkast',    'color' => '#6B7280'],
  'sent'     => ['label' => 'Skickad',   'color' => '#0066FF'],
  'viewed'   => ['label' => 'Visad',     'color' => '#0891B2'],
  'accepted' => ['label' => 'Accepterad','color' => '#059669'],
  'rejected' => ['label' => 'Avvisad',   'color' => '#DC2626'],
];

// ── INVOICE STATUSES ──────────────────────────────────────
const INVOICE_STATUSES = [
  'draft'     => ['label' => 'Utkast',        'color' => '#6B7280'],
  'sent'      => ['label' => 'Skickad',       'color' => '#0066FF'],
  'partial'   => ['label' => 'Delbetald',     'color' => '#D97706'],
  'paid'      => ['label' => 'Betald',        'color' => '#059669'],
  'overdue'   => ['label' => 'Förfallen',     'color' => '#DC2626'],
  'cancelled' => ['label' => 'Makulerad',     'color' => '#374151'],
];

// ── SUPPLIER (LEVERANTÖR) INVOICE STATUSES ────────────────
// Separate from INVOICE_STATUSES above: these are invoices suppliers submit to M2 for
// completed work (payables), not invoices M2 sends to customers (receivables).
const SUPPLIER_INVOICE_STATUSES = [
  'pending'  => ['label' => 'Väntar granskning', 'color' => '#D97706'],
  'approved' => ['label' => 'Godkänd',           'color' => '#0891B2'],
  'paid'     => ['label' => 'Betald',            'color' => '#059669'],
  'rejected' => ['label' => 'Avvisad',           'color' => '#DC2626'],
];

const SUPPLIER_STATUSES = [
  'pending'  => ['label' => 'Väntar verifiering', 'color' => '#D97706'],
  'verified' => ['label' => 'Verifierad',          'color' => '#0891B2'],
  'active'   => ['label' => 'Aktiv',               'color' => '#059669'],
  'inactive' => ['label' => 'Inaktiv',             'color' => '#6B7280'],
];

const SERVICES = [
  'Takbyte','Takrenovering','Takmålning','Taktvätt','Plåtarbeten',
  'Fasadmålning','Fasadrenovering','Fasadtvätt',
  'Markarbete','Stenläggning','Klottersanering','Övrigt',
];

date_default_timezone_set('Europe/Stockholm');
