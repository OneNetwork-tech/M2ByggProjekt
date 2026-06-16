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

function migrate(PDO $pdo): void {
    $isMysql = DB_DRIVER === 'mysql';
    $PK   = $isMysql ? 'INT AUTO_INCREMENT PRIMARY KEY' : 'INTEGER PRIMARY KEY AUTOINCREMENT';
    $NOW  = $isMysql ? 'CURRENT_TIMESTAMP' : "datetime('now','localtime')";
    $TXT  = 'TEXT';

    $tables = [

    "CREATE TABLE IF NOT EXISTS users (
        id $PK,
        name $TXT NOT NULL,
        email $TXT NOT NULL UNIQUE,
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
        lead_no $TXT UNIQUE,
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
        quote_no $TXT UNIQUE,
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
        project_no $TXT UNIQUE,
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
        invoice_no $TXT UNIQUE,
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
        skey $TXT PRIMARY KEY,
        svalue $TXT
    )",

    /* ── PHASE 2: PORTAL TABLES ─────────────────────────── */

    "CREATE TABLE IF NOT EXISTS portal_users (
        id $PK,
        customer_id INTEGER NOT NULL UNIQUE,
        email $TXT NOT NULL UNIQUE,
        password_hash $TXT NOT NULL,
        active INTEGER DEFAULT 1,
        last_login $TXT,
        created_at $TXT DEFAULT ($NOW)
    )",

    "CREATE TABLE IF NOT EXISTS portal_invites (
        id $PK,
        customer_id INTEGER NOT NULL,
        token $TXT NOT NULL UNIQUE,
        email $TXT NOT NULL,
        used_at $TXT,
        expires_at $TXT NOT NULL,
        created_at $TXT DEFAULT ($NOW)
    )",

    "CREATE TABLE IF NOT EXISTS supplier_users (
        id $PK,
        supplier_id INTEGER NOT NULL UNIQUE,
        email $TXT NOT NULL UNIQUE,
        password_hash $TXT NOT NULL,
        active INTEGER DEFAULT 1,
        last_login $TXT,
        created_at $TXT DEFAULT ($NOW)
    )",

    "CREATE TABLE IF NOT EXISTS supplier_invites (
        id $PK,
        supplier_id INTEGER NOT NULL,
        token $TXT NOT NULL UNIQUE,
        email $TXT NOT NULL,
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
    ];

    foreach ($tables as $sql) $pdo->exec($sql);

    // Seed admin user if none exists
    $count = $pdo->query("SELECT COUNT(*) AS c FROM users")->fetch()['c'];
    if ($count == 0) {
        $stmt = $pdo->prepare("INSERT INTO users (name, email, password_hash, role) VALUES (?,?,?,?)");
        $stmt->execute(['Admin', 'admin@m2team.se', password_hash('admin123', PASSWORD_DEFAULT), 'super_admin']);
    }
}

/* ── HELPERS ──────────────────────────────────────────────── */

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
