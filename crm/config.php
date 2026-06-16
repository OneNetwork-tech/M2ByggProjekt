<?php
/**
 * M2 Platform — Configuration
 * Switch DB_DRIVER to 'mysql' for production MySQL on cPanel.
 */

define('APP_NAME',    'M2 Platform');
define('APP_URL',     'https://www.m2team.se/crm');
define('APP_VERSION', '1.0.0');

// ── DATABASE ──────────────────────────────────────────────
define('DB_DRIVER', 'sqlite');                       // 'sqlite' | 'mysql'
define('DB_SQLITE_PATH', __DIR__ . '/../data/m2platform.sqlite');
// MySQL settings (used when DB_DRIVER = 'mysql'):
define('DB_HOST', 'localhost');
define('DB_NAME', 'm2team_platform');
define('DB_USER', 'm2team_crm');
define('DB_PASS', 'CHANGE_ME');

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

// ── ROLES ─────────────────────────────────────────────────
const ROLES = [
  'super_admin' => 'Super Admin',
  'sales'       => 'Säljansvarig',
  'project'     => 'Projektledare',
  'finance'     => 'Ekonomi',
  'support'     => 'Kundsupport',
];

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
