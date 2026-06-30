<?php
/**
 * M2 Platform — Database Layer
 * PDO wrapper with auto-migration. SQLite default, MySQL-portable.
 */

require_once __DIR__ . '/../config.php';

function db(): PDO {
    static $pdo = null;
    if ($pdo !== null) return $pdo;

    if (DB_DRIVER === 'mysql') {
        $pdo = new PDO(
            'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
            DB_USER, DB_PASS,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
             PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
        );
    } else {
        $dir = dirname(DB_SQLITE_PATH);
        if (!is_dir($dir)) mkdir($dir, 0755, true);
        $pdo = new PDO('sqlite:' . DB_SQLITE_PATH, null, null,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
             PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]);
        $pdo->exec('PRAGMA journal_mode = WAL');
        $pdo->exec('PRAGMA foreign_keys = ON');
    }

    migrate($pdo);
    return $pdo;
}

function migrate(PDO $pdo, ?string $driver = null): void {
    $isMysql = ($driver ?? DB_DRIVER) === 'mysql';
    $PK   = $isMysql ? 'INT AUTO_INCREMENT PRIMARY KEY' : 'INTEGER PRIMARY KEY AUTOINCREMENT';
    $NOW  = $isMysql ? 'CURRENT_TIMESTAMP' : "datetime('now','localtime')";
    $TXT  = 'TEXT';
    $VKEY = $isMysql ? 'VARCHAR(191)' : 'TEXT'; // for columns used as a PRIMARY/UNIQUE KEY — MySQL rejects TEXT in a key without an explicit length

    $tables = [

    "CREATE TABLE IF NOT EXISTS users (
        id $PK,
        name $TXT NOT NULL,
        email $VKEY NOT NULL UNIQUE,
        password_hash $TXT NOT NULL,
        role $TXT NOT NULL DEFAULT 'sales',
        phone $TXT,
        active INTEGER DEFAULT 1,
        last_login $TXT,
        created_at $TXT DEFAULT ($NOW)
    )",

    "CREATE TABLE IF NOT EXISTS customers (
        id $PK,
        name $TXT NOT NULL,
        email $TXT,
        phone $TXT,
        address $TXT,
        city $TXT,
        postal_code $TXT,
        org_nr $TXT,
        type $TXT DEFAULT 'private',
        notes $TXT,
        created_at $TXT DEFAULT ($NOW)
    )",

    "CREATE TABLE IF NOT EXISTS leads (
        id $PK,
        lead_no $VKEY UNIQUE,
        name $TXT NOT NULL,
        email $TXT,
        phone $TXT,
        address $TXT,
        city $TXT,
        service $TXT,
        sub_service $TXT,
        source $TXT DEFAULT 'Webbformulär',
        message $TXT,
        stage $TXT DEFAULT 'new',
        value_estimate REAL DEFAULT 0,
        assigned_to INTEGER,
        customer_id INTEGER,
        lost_reason $TXT,
        created_at $TXT DEFAULT ($NOW),
        updated_at $TXT DEFAULT ($NOW)
    )",

    "CREATE TABLE IF NOT EXISTS quotes (
        id $PK,
        quote_no $VKEY UNIQUE,
        lead_id INTEGER,
        customer_id INTEGER,
        title $TXT NOT NULL,
        status $TXT DEFAULT 'draft',
        valid_until $TXT,
        work_cost REAL DEFAULT 0,
        material_cost REAL DEFAULT 0,
        subtotal REAL DEFAULT 0,
        vat REAL DEFAULT 0,
        rot_deduction REAL DEFAULT 0,
        total REAL DEFAULT 0,
        notes $TXT,
        created_by INTEGER,
        sent_at $TXT,
        viewed_at $TXT,
        accepted_at $TXT,
        created_at $TXT DEFAULT ($NOW)
    )",

    "CREATE TABLE IF NOT EXISTS quote_items (
        id $PK,
        quote_id INTEGER NOT NULL,
        description $TXT NOT NULL,
        qty REAL DEFAULT 1,
        unit $TXT DEFAULT 'st',
        unit_price REAL DEFAULT 0,
        is_work INTEGER DEFAULT 1,
        total REAL DEFAULT 0,
        sort_order INTEGER DEFAULT 0
    )",

    "CREATE TABLE IF NOT EXISTS projects (
        id $PK,
        project_no $VKEY UNIQUE,
        customer_id INTEGER,
        quote_id INTEGER,
        title $TXT NOT NULL,
        address $TXT,
        city $TXT,
        status $TXT DEFAULT 'planning',
        budget REAL DEFAULT 0,
        start_date $TXT,
        end_date $TXT,
        supplier_id INTEGER,
        manager_id INTEGER,
        progress INTEGER DEFAULT 0,
        next_step $TXT,
        created_at $TXT DEFAULT ($NOW)
    )",

    "CREATE TABLE IF NOT EXISTS suppliers (
        id $PK,
        company $TXT NOT NULL,
        contact $TXT,
        email $TXT,
        phone $TXT,
        specialty $TXT,
        org_nr $TXT,
        status $TXT DEFAULT 'pending',
        rating REAL DEFAULT 0,
        notes $TXT,
        created_at $TXT DEFAULT ($NOW)
    )",

    "CREATE TABLE IF NOT EXISTS invoices (
        id $PK,
        invoice_no $VKEY UNIQUE,
        customer_id INTEGER,
        project_id INTEGER,
        quote_id INTEGER,
        status $TXT DEFAULT 'draft',
        issue_date $TXT,
        due_date $TXT,
        subtotal REAL DEFAULT 0,
        vat REAL DEFAULT 0,
        rot_deduction REAL DEFAULT 0,
        total REAL DEFAULT 0,
        paid_amount REAL DEFAULT 0,
        notes $TXT,
        created_at $TXT DEFAULT ($NOW)
    )",

    "CREATE TABLE IF NOT EXISTS invoice_items (
        id $PK,
        invoice_id INTEGER NOT NULL,
        description $TXT NOT NULL,
        qty REAL DEFAULT 1,
        unit $TXT DEFAULT 'st',
        unit_price REAL DEFAULT 0,
        total REAL DEFAULT 0
    )",

    "CREATE TABLE IF NOT EXISTS payments (
        id $PK,
        invoice_id INTEGER NOT NULL,
        amount REAL NOT NULL,
        method $TXT DEFAULT 'Bankgiro',
        paid_at $TXT,
        note $TXT,
        created_at $TXT DEFAULT ($NOW)
    )",

    "CREATE TABLE IF NOT EXISTS invoice_reminders (
        id $PK,
        invoice_id INTEGER NOT NULL,
        days_overdue INTEGER NOT NULL,
        sent_at $TXT DEFAULT ($NOW)
    )",

    "CREATE TABLE IF NOT EXISTS blog_posts (
        id $PK,
        slug $VKEY UNIQUE NOT NULL,
        title $TXT NOT NULL,
        excerpt $TXT,
        body $TXT,
        cover_image $TXT,
        category $TXT DEFAULT 'tak',
        status $TXT DEFAULT 'draft',
        author_id INTEGER,
        read_minutes INTEGER DEFAULT 5,
        published_at $TXT,
        created_at $TXT DEFAULT ($NOW),
        updated_at $TXT DEFAULT ($NOW)
    )",

    "CREATE TABLE IF NOT EXISTS blog_comments (
        id $PK,
        post_id INTEGER NOT NULL,
        portal_user_id INTEGER,
        author_name $TXT NOT NULL,
        body $TXT NOT NULL,
        hidden INTEGER DEFAULT 0,
        created_at $TXT DEFAULT ($NOW)
    )",

    "CREATE TABLE IF NOT EXISTS emails (
        id $PK,
        to_email $TXT NOT NULL,
        to_name $TXT,
        entity_type $TXT,
        entity_id INTEGER,
        subject $TXT NOT NULL,
        body $TXT NOT NULL,
        status $TXT NOT NULL DEFAULT 'sent',
        error $TXT,
        sent_by INTEGER,
        created_at $TXT DEFAULT ($NOW)
    )",

    "CREATE TABLE IF NOT EXISTS meetings (
        id $PK,
        title $TXT NOT NULL,
        contact_type $TXT NOT NULL DEFAULT 'contact',
        contact_id INTEGER,
        contact_name $TXT NOT NULL,
        contact_email $TXT,
        contact_phone $TXT,
        location $TXT,
        meeting_date $TXT NOT NULL,
        start_time $TXT,
        end_time $TXT,
        notes $TXT,
        created_by INTEGER,
        created_at $TXT DEFAULT ($NOW)
    )",

    "CREATE TABLE IF NOT EXISTS services (
        id $PK,
        category $TXT NOT NULL DEFAULT 'Övrigt',
        icon_key $TXT DEFAULT 'tools',
        title $TXT NOT NULL,
        slug $VKEY UNIQUE NOT NULL,
        description $TXT,
        price_label $TXT,
        detail_body $TXT,
        cover_image $TXT,
        sort_order INTEGER DEFAULT 0,
        visible INTEGER DEFAULT 1,
        created_at $TXT DEFAULT ($NOW),
        updated_at $TXT DEFAULT ($NOW)
    )",

    "CREATE TABLE IF NOT EXISTS portfolio_projects (
        id $PK,
        title $TXT NOT NULL,
        subtitle $TXT,
        category $TXT NOT NULL DEFAULT 'tak',
        image $TXT NOT NULL,
        height INTEGER DEFAULT 280,
        sort_order INTEGER DEFAULT 0,
        visible INTEGER DEFAULT 1,
        created_at $TXT DEFAULT ($NOW)
    )",

    "CREATE TABLE IF NOT EXISTS supplier_ratings (
        id $PK,
        job_assignment_id INTEGER NOT NULL,
        supplier_id INTEGER NOT NULL,
        project_id INTEGER NOT NULL,
        rating INTEGER NOT NULL DEFAULT 5,
        note $TXT,
        rated_by INTEGER,
        created_at $TXT DEFAULT ($NOW)
    )",

    "CREATE TABLE IF NOT EXISTS reviews (
        id $PK,
        project_id INTEGER NOT NULL,
        customer_id INTEGER NOT NULL,
        portal_user_id INTEGER,
        rating INTEGER NOT NULL DEFAULT 5,
        body $TXT NOT NULL,
        visible INTEGER DEFAULT 1,
        created_at $TXT DEFAULT ($NOW)
    )",

    "CREATE TABLE IF NOT EXISTS timeline (
        id $PK,
        entity_type $TXT NOT NULL,
        entity_id INTEGER NOT NULL,
        type $TXT DEFAULT 'note',
        title $TXT,
        body $TXT,
        created_by INTEGER,
        created_at $TXT DEFAULT ($NOW)
    )",

    "CREATE TABLE IF NOT EXISTS tasks (
        id $PK,
        title $TXT NOT NULL,
        due_date $TXT,
        done INTEGER DEFAULT 0,
        assigned_to INTEGER,
        entity_type $TXT,
        entity_id INTEGER,
        created_at $TXT DEFAULT ($NOW)
    )",

    "CREATE TABLE IF NOT EXISTS notifications (
        id $PK,
        user_id INTEGER NOT NULL,
        title $TXT NOT NULL,
        body $TXT,
        link $TXT,
        read_at $TXT,
        created_at $TXT DEFAULT ($NOW)
    )",

    "CREATE TABLE IF NOT EXISTS audit_logs (
        id $PK,
        user_id INTEGER,
        action $TXT NOT NULL,
        entity_type $TXT,
        entity_id INTEGER,
        detail $TXT,
        created_at $TXT DEFAULT ($NOW)
    )",

    "CREATE TABLE IF NOT EXISTS settings (
        skey $VKEY PRIMARY KEY,
        svalue $TXT
    )",

    /* ── PHASE 2: PORTAL TABLES ─────────────────────────── */

    "CREATE TABLE IF NOT EXISTS portal_users (
        id $PK,
        customer_id INTEGER NOT NULL UNIQUE,
        email $VKEY NOT NULL UNIQUE,
        password_hash $TXT NOT NULL,
        active INTEGER DEFAULT 1,
        last_login $TXT,
        created_at $TXT DEFAULT ($NOW)
    )",

    "CREATE TABLE IF NOT EXISTS portal_invites (
        id $PK,
        customer_id INTEGER NOT NULL,
        token $VKEY NOT NULL UNIQUE,
        email $TXT NOT NULL,
        used_at $TXT,
        expires_at $TXT NOT NULL,
        created_at $TXT DEFAULT ($NOW)
    )",

    "CREATE TABLE IF NOT EXISTS supplier_users (
        id $PK,
        supplier_id INTEGER NOT NULL UNIQUE,
        email $VKEY NOT NULL UNIQUE,
        password_hash $TXT NOT NULL,
        active INTEGER DEFAULT 1,
        last_login $TXT,
        created_at $TXT DEFAULT ($NOW)
    )",

    "CREATE TABLE IF NOT EXISTS supplier_invites (
        id $PK,
        supplier_id INTEGER NOT NULL,
        token $VKEY NOT NULL UNIQUE,
        email $TXT NOT NULL,
        used_at $TXT,
        expires_at $TXT NOT NULL,
        created_at $TXT DEFAULT ($NOW)
    )",

    "CREATE TABLE IF NOT EXISTS password_resets (
        id $PK,
        user_type $TXT NOT NULL,
        user_id INTEGER NOT NULL,
        token $VKEY NOT NULL UNIQUE,
        used_at $TXT,
        expires_at $TXT NOT NULL,
        created_at $TXT DEFAULT ($NOW)
    )",

    "CREATE TABLE IF NOT EXISTS job_assignments (
        id $PK,
        project_id INTEGER NOT NULL,
        supplier_id INTEGER NOT NULL,
        status $TXT DEFAULT 'pending',
        description $TXT,
        crm_note $TXT,
        supplier_note $TXT,
        offered_at $TXT DEFAULT ($NOW),
        responded_at $TXT,
        start_date $TXT,
        end_date $TXT,
        estimated_hours REAL,
        rate REAL,
        agreed_amount REAL DEFAULT 0,
        created_at $TXT DEFAULT ($NOW)
    )",

    "CREATE TABLE IF NOT EXISTS time_reports (
        id $PK,
        job_assignment_id INTEGER,
        supplier_id INTEGER NOT NULL,
        project_id INTEGER NOT NULL,
        report_date $TXT NOT NULL,
        hours REAL DEFAULT 0,
        amount REAL,
        description $TXT,
        approved INTEGER DEFAULT 0,
        paid_at $TXT,
        created_at $TXT DEFAULT ($NOW)
    )",

    "CREATE TABLE IF NOT EXISTS gdpr_requests (
        id $PK,
        type $TXT NOT NULL,
        entity_type $TXT NOT NULL,
        entity_id INTEGER NOT NULL,
        requested_by $TXT NOT NULL DEFAULT 'self',
        status $TXT NOT NULL DEFAULT 'pending',
        notes $TXT,
        requested_at $TXT DEFAULT ($NOW),
        resolved_at $TXT,
        resolved_by INTEGER
    )",

    "CREATE TABLE IF NOT EXISTS login_attempts (
        id $PK,
        scope $TXT NOT NULL,
        identifier $TXT NOT NULL,
        ip_address $TXT NOT NULL,
        success INTEGER DEFAULT 0,
        created_at $TXT DEFAULT ($NOW)
    )",

    "CREATE TABLE IF NOT EXISTS accounting_sync_log (
        id $PK,
        provider $TXT NOT NULL,
        entity_type $TXT NOT NULL,
        entity_id INTEGER NOT NULL,
        external_id $TXT,
        action $TXT NOT NULL,
        status $TXT NOT NULL,
        response $TXT,
        created_at $TXT DEFAULT ($NOW)
    )",

    "CREATE TABLE IF NOT EXISTS quote_signatures (
        id $PK,
        quote_id INTEGER NOT NULL UNIQUE,
        signer_name $TXT NOT NULL,
        signer_email $TXT,
        signature_data $TXT NOT NULL,
        consent_text $TXT NOT NULL,
        ip_address $TXT,
        user_agent $TXT,
        signed_at $TXT DEFAULT ($NOW)
    )",

    "CREATE TABLE IF NOT EXISTS site_visits (
        id $PK,
        title $TXT NOT NULL,
        visit_date $TXT NOT NULL,
        visit_time $TXT,
        lead_id INTEGER,
        customer_id INTEGER,
        project_id INTEGER,
        supplier_id INTEGER,
        assigned_to INTEGER,
        notes $TXT,
        created_by INTEGER,
        created_at $TXT DEFAULT ($NOW)
    )",

    "CREATE TABLE IF NOT EXISTS notifications_log (
        id $PK,
        channel $TXT NOT NULL,
        recipient $TXT NOT NULL,
        subject $TXT,
        entity_type $TXT,
        entity_id INTEGER,
        status $TXT NOT NULL,
        error $TXT,
        created_at $TXT DEFAULT ($NOW)
    )",

    "CREATE TABLE IF NOT EXISTS job_photos (
        id $PK,
        job_assignment_id INTEGER NOT NULL,
        supplier_id INTEGER NOT NULL,
        stored_name $TXT NOT NULL,
        original_name $TXT NOT NULL,
        caption $TXT,
        created_at $TXT DEFAULT ($NOW)
    )",

    "CREATE TABLE IF NOT EXISTS supplier_documents (
        id $PK,
        supplier_id INTEGER NOT NULL,
        original_name $TXT NOT NULL,
        stored_name $TXT NOT NULL,
        mime_type $TXT,
        size_bytes INTEGER DEFAULT 0,
        category $TXT DEFAULT 'Övrigt',
        note $TXT,
        created_at $TXT DEFAULT ($NOW)
    )",

    "CREATE TABLE IF NOT EXISTS portal_messages (
        id $PK,
        project_id INTEGER NOT NULL,
        sender_type $TXT NOT NULL,
        sender_id INTEGER NOT NULL,
        body $TXT NOT NULL,
        read_at $TXT,
        created_at $TXT DEFAULT ($NOW)
    )",

    "CREATE TABLE IF NOT EXISTS portal_documents (
        id $PK,
        project_id INTEGER NOT NULL,
        uploaded_by_type $TXT NOT NULL,
        uploaded_by_id INTEGER NOT NULL,
        filename $TXT NOT NULL,
        original_name $TXT NOT NULL,
        mime_type $TXT,
        filesize INTEGER DEFAULT 0,
        description $TXT,
        created_at $TXT DEFAULT ($NOW)
    )",

    "CREATE TABLE IF NOT EXISTS email_accounts (
        id $PK,
        label $TXT NOT NULL,
        host $TXT NOT NULL,
        port INTEGER NOT NULL DEFAULT 465,
        encryption $TXT NOT NULL DEFAULT 'ssl',
        username $TXT NOT NULL,
        password $TXT NOT NULL,
        from_email $TXT NOT NULL,
        from_name $TXT NOT NULL DEFAULT 'M2 Bygg Team AB',
        imap_host $TXT,
        imap_port INTEGER NOT NULL DEFAULT 993,
        imap_encryption $TXT NOT NULL DEFAULT 'ssl',
        is_default INTEGER NOT NULL DEFAULT 0,
        active INTEGER NOT NULL DEFAULT 1,
        created_at $TXT DEFAULT ($NOW)
    )",

    // Leverantörsfakturor — invoices suppliers submit to M2 for completed work (payables).
    // Deliberately separate from the customer-facing invoices table above (receivables);
    // the two have different statuses, different portals, and different email flows.
    "CREATE TABLE IF NOT EXISTS supplier_invoices (
        id $PK,
        invoice_no $VKEY UNIQUE,
        supplier_id INTEGER NOT NULL,
        project_id INTEGER,
        status $TXT DEFAULT 'pending',
        amount REAL DEFAULT 0,
        vat REAL DEFAULT 0,
        total REAL DEFAULT 0,
        paid_amount REAL DEFAULT 0,
        due_date $TXT,
        description $TXT,
        created_at $TXT DEFAULT ($NOW)
    )",

    "CREATE TABLE IF NOT EXISTS supplier_payments (
        id $PK,
        supplier_invoice_id INTEGER NOT NULL,
        amount REAL NOT NULL,
        method $TXT,
        paid_at $TXT,
        note $TXT,
        created_at $TXT DEFAULT ($NOW)
    )",
    ];

    foreach ($tables as $sql) $pdo->exec($sql);

    // Add columns to existing tables that predate them (CREATE TABLE IF NOT EXISTS won't alter live tables)
    add_column_if_missing($pdo, 'portfolio_projects', 'linked_project_id', 'INTEGER');
    add_column_if_missing($pdo, 'reviews', 'reply_body', 'TEXT');
    add_column_if_missing($pdo, 'reviews', 'reply_at', 'TEXT');
    add_column_if_missing($pdo, 'email_accounts', 'imap_host', 'TEXT');
    add_column_if_missing($pdo, 'email_accounts', 'imap_port', 'INTEGER NOT NULL DEFAULT 993');
    add_column_if_missing($pdo, 'email_accounts', 'imap_encryption', "TEXT NOT NULL DEFAULT 'ssl'");

    // Seed admin user if none exists
    $count = $pdo->query("SELECT COUNT(*) AS c FROM users")->fetch()['c'];
    if ($count == 0) {
        $stmt = $pdo->prepare("INSERT INTO users (name, email, password_hash, role) VALUES (?,?,?,?)");
        $stmt->execute(['Admin', 'admin@m2team.se', password_hash('admin123', PASSWORD_DEFAULT), 'super_admin']);
    }
}

/**
 * Adds a column to an existing table if it isn't already there. CREATE TABLE IF NOT EXISTS
 * only handles brand-new installs — existing tables need an explicit ALTER for new fields.
 */
function add_column_if_missing(PDO $pdo, string $table, string $column, string $definition): void {
    $isMysql = DB_DRIVER === 'mysql';
    if ($isMysql) {
        $exists = $pdo->prepare("SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = ? AND column_name = ?");
        $exists->execute([$table, $column]);
        if ((int)$exists->fetchColumn() > 0) return;
    } else {
        $cols = $pdo->query("PRAGMA table_info(" . $table . ")")->fetchAll();
        foreach ($cols as $c) if ($c['name'] === $column) return;
    }
    $pdo->exec("ALTER TABLE $table ADD COLUMN $column $definition");
}

/* ── HELPERS ──────────────────────────────────────────────── */

/** Driver-aware "current timestamp" SQL fragment for use inside raw query strings. */
function now_expr(): string {
    return DB_DRIVER === 'mysql' ? 'NOW()' : "datetime('now','localtime')";
}

/** Driver-aware "today's date" SQL fragment (no time component) for use inside raw query strings. */
function today_expr(): string {
    return DB_DRIVER === 'mysql' ? 'CURDATE()' : "date('now')";
}

/**
 * Driver-aware "today + N days" SQL fragment. $days may be negative for "N days ago".
 * Returns a literal computed in PHP, so it's safe to splice directly into a query string.
 */
function date_offset_expr(int $days): string {
    return "'" . date('Y-m-d', strtotime(($days >= 0 ? "+{$days}" : (string)$days) . ' days')) . "'";
}

/** Generate next sequential document number, e.g. L-2025-0001 */
function next_number(string $prefix, string $table, string $col): string {
    $year = date('Y');
    $like = "$prefix-$year-%";
    $row = db()->prepare("SELECT $col FROM $table WHERE $col LIKE ? ORDER BY id DESC LIMIT 1");
    $row->execute([$like]);
    $last = $row->fetchColumn();
    $n = $last ? ((int)substr($last, -4)) + 1 : 1;
    return sprintf('%s-%s-%04d', $prefix, $year, $n);
}

/** Insert a timeline event */
function log_timeline(string $entityType, int $entityId, string $type, string $title, string $body = '', ?int $userId = null): void {
    $stmt = db()->prepare("INSERT INTO timeline (entity_type, entity_id, type, title, body, created_by) VALUES (?,?,?,?,?,?)");
    $stmt->execute([$entityType, $entityId, $type, $title, $body, $userId]);
}

/** Insert audit log */
function audit(string $action, string $entityType = '', int $entityId = 0, string $detail = ''): void {
    $uid = $_SESSION['user_id'] ?? null;
    $stmt = db()->prepare("INSERT INTO audit_logs (user_id, action, entity_type, entity_id, detail) VALUES (?,?,?,?,?)");
    $stmt->execute([$uid, $action, $entityType, $entityId, $detail]);
}

/** Notify a user */
function notify(int $userId, string $title, string $body = '', string $link = ''): void {
    $stmt = db()->prepare("INSERT INTO notifications (user_id, title, body, link) VALUES (?,?,?,?)");
    $stmt->execute([$userId, $title, $body, $link]);
}

/** Notify all users with a given role */
function notify_role(string $role, string $title, string $body = '', string $link = ''): void {
    $users = db()->prepare("SELECT id FROM users WHERE role IN (?, 'super_admin') AND active = 1");
    $users->execute([$role]);
    foreach ($users->fetchAll() as $u) notify($u['id'], $title, $body, $link);
}

/**
 * DB-backed login rate limiter. Blocks after $maxAttempts failed attempts within
 * $windowMinutes, keyed by scope ('crm','portal','leverantor') + identifier (email) + IP.
 */
function rate_limit_check(string $scope, string $identifier, int $maxAttempts = 5, int $windowMinutes = 15): bool {
    $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    $cutoff = date('Y-m-d H:i:s', strtotime("-{$windowMinutes} minutes"));
    $s = db()->prepare(
        "SELECT COUNT(*) FROM login_attempts
         WHERE scope = ? AND (identifier = ? OR ip_address = ?) AND success = 0
           AND created_at > ?"
    );
    $s->execute([$scope, strtolower(trim($identifier)), $ip, $cutoff]);
    return (int)$s->fetchColumn() < $maxAttempts;
}

function rate_limit_record(string $scope, string $identifier, bool $success): void {
    $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    db()->prepare("INSERT INTO login_attempts (scope, identifier, ip_address, success) VALUES (?,?,?,?)")
        ->execute([$scope, strtolower(trim($identifier)), $ip, $success ? 1 : 0]);
    // Opportunistic cleanup of old rows so the table doesn't grow forever
    if (random_int(1, 50) === 1) {
        $cutoff = date('Y-m-d H:i:s', strtotime('-7 days'));
        db()->prepare("DELETE FROM login_attempts WHERE created_at < ?")->execute([$cutoff]);
    }
}

/**
 * Password reset tokens — shared across all three login types ('crm', 'portal', 'supplier').
 * Mirrors the existing portal_invites/supplier_invites pattern (random token + expiry + used_at).
 */
function create_password_reset_token(string $userType, int $userId, int $expiresMinutes = 60): string {
    $token   = bin2hex(random_bytes(32));
    $expires = date('Y-m-d H:i:s', strtotime("+{$expiresMinutes} minutes"));
    // Invalidate any earlier unused reset requests for this user so only the latest link works.
    db()->prepare("UPDATE password_resets SET used_at = " . now_expr() . " WHERE user_type = ? AND user_id = ? AND used_at IS NULL")
        ->execute([$userType, $userId]);
    db()->prepare("INSERT INTO password_resets (user_type, user_id, token, expires_at) VALUES (?,?,?,?)")
        ->execute([$userType, $userId, $token, $expires]);
    return $token;
}

function find_password_reset(string $userType, string $token): ?array {
    if ($token === '') return null;
    $s = db()->prepare(
        "SELECT * FROM password_resets WHERE user_type = ? AND token = ? AND used_at IS NULL AND expires_at > " . now_expr() . ""
    );
    $s->execute([$userType, $token]);
    return $s->fetch() ?: null;
}

function consume_password_reset(int $resetId): void {
    db()->prepare("UPDATE password_resets SET used_at = " . now_expr() . " WHERE id = ?")->execute([$resetId]);
}

/** Key/value settings store — used for OAuth tokens and other runtime config. */
function get_setting(string $key, ?string $default = null): ?string {
    $s = db()->prepare("SELECT svalue FROM settings WHERE skey = ?");
    $s->execute([$key]);
    $v = $s->fetchColumn();
    return $v === false ? $default : $v;
}

function set_setting(string $key, string $value): void {
    $isMysql = DB_DRIVER === 'mysql';
    if ($isMysql) {
        db()->prepare("INSERT INTO settings (skey, svalue) VALUES (?,?) ON DUPLICATE KEY UPDATE svalue = ?")
            ->execute([$key, $value, $value]);
    } else {
        db()->prepare("INSERT INTO settings (skey, svalue) VALUES (?,?) ON CONFLICT(skey) DO UPDATE SET svalue = ?")
            ->execute([$key, $value, $value]);
    }
}

/**
 * Outgoing email accounts (SMTP), configurable from crm/installningar.php instead of
 * hardcoded constants. Supports multiple accounts (e.g. a separate Outlook/Microsoft 365
 * account alongside the main mail.m2team.se one); crm_send_mail()/sendMail() use the
 * default active account, or a specific one if $accountId is passed.
 */
function get_default_email_account(): ?array {
    $s = db()->query("SELECT * FROM email_accounts WHERE active = 1 ORDER BY is_default DESC, id ASC LIMIT 1");
    return $s->fetch() ?: null;
}

function get_email_account(int $id): ?array {
    $s = db()->prepare("SELECT * FROM email_accounts WHERE id = ? AND active = 1");
    $s->execute([$id]);
    return $s->fetch() ?: null;
}

/**
 * Preset icon library for services — lets staff pick a service icon from a dropdown
 * instead of hand-writing SVG paths. Shared between the CRM editor and the public render.
 */
function service_icon_presets(): array {
    return [
        'roof'    => '<path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/>',
        'paint'   => '<path d="M12 19l7-7 3 3-7 7-3-3z"/><path d="M18 13l-1.5-7.5L2 2l3.5 14.5L13 18l5-5z"/>',
        'wrench'  => '<path d="M14.7 6.3a1 1 0 000 1.4l1.6 1.6a1 1 0 001.4 0l3.77-3.77a6 6 0 01-7.94 7.94l-6.91 6.91a2.12 2.12 0 01-3-3l6.91-6.91a6 6 0 017.94-7.94l-3.76 3.76z"/>',
        'wash'    => '<path d="M12 2.69l5.66 5.66a8 8 0 11-11.31 0z"/>',
        'wall'    => '<rect x="2" y="3" width="20" height="14" rx="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/>',
        'panel'   => '<rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18M3 15h18M9 3v18M15 3v18"/>',
        'balcony' => '<rect x="3" y="11" width="18" height="10" rx="1"/><path d="M3 11V7a2 2 0 012-2h14a2 2 0 012 2v4"/><line x1="12" y1="6" x2="12" y2="11"/>',
        'ground'  => '<path d="M2 20h20M4 20V10l8-8 8 8v10"/>',
        'tiles'   => '<rect x="3" y="3" width="8" height="8"/><rect x="13" y="3" width="8" height="8"/><rect x="3" y="13" width="8" height="8"/><rect x="13" y="13" width="8" height="8"/>',
        'spray'   => '<polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a1 1 0 011-1h4a1 1 0 011 1v2"/>',
        'shield'  => '<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>',
        'tools'   => '<circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 00.33 1.82l.06.06a2 2 0 11-2.83 2.83l-.06-.06a1.65 1.65 0 00-1.82-.33 1.65 1.65 0 00-1 1.51V21a2 2 0 11-4 0v-.09A1.65 1.65 0 008 19.4a1.65 1.65 0 00-1.82.33l-.06.06a2 2 0 11-2.83-2.83l.06-.06a1.65 1.65 0 00.33-1.82 1.65 1.65 0 00-1.51-1H2a2 2 0 110-4h.09A1.65 1.65 0 003.6 8a1.65 1.65 0 00-.33-1.82l-.06-.06a2 2 0 112.83-2.83l.06.06a1.65 1.65 0 001.82.33H8a1.65 1.65 0 001-1.51V2a2 2 0 114 0v.09a1.65 1.65 0 001 1.51 1.65 1.65 0 001.82-.33l.06-.06a2 2 0 112.83 2.83l-.06.06a1.65 1.65 0 00-.33 1.82V8a1.65 1.65 0 001.51 1H21a2 2 0 110 4h-.09a1.65 1.65 0 00-1.51 1z"/>',
    ];
}

function service_icon_svg(?string $key): string {
    $presets = service_icon_presets();
    return $presets[$key] ?? $presets['tools'];
}

/**
 * Handles an optional image upload for CRM-managed public content (portfolio, services).
 * Returns the public URL path to use, or null if no file was uploaded (caller should
 * fall back to a manually-typed URL in that case). Throws via $error by reference on failure.
 */
function handle_public_image_upload(string $fieldName, string $subdir, ?string &$error): ?string {
    if (empty($_FILES[$fieldName]) || $_FILES[$fieldName]['error'] === UPLOAD_ERR_NO_FILE) return null;
    $f = $_FILES[$fieldName];
    $allowedMime = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];

    if ($f['error'] !== UPLOAD_ERR_OK) { $error = 'Uppladdningsfel (' . $f['error'] . ').'; return null; }
    if ($f['size'] > 8_388_608) { $error = 'Bilden är för stor (max 8 MB).'; return null; }
    if (!in_array(mime_content_type($f['tmp_name']), $allowedMime, true)) { $error = 'Endast JPG, PNG, WEBP eller GIF tillåts.'; return null; }

    $dir = dirname(__DIR__) . '/uploads/' . $subdir . '/';
    if (!is_dir($dir)) mkdir($dir, 0755, true);

    $ext    = strtolower(pathinfo($f['name'], PATHINFO_EXTENSION));
    $stored = bin2hex(random_bytes(8)) . '.' . $ext;
    move_uploaded_file($f['tmp_name'], $dir . $stored);

    return '/uploads/' . $subdir . '/' . $stored;
}
