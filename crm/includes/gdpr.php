<?php
/**
 * GDPR data export & erasure helpers.
 *
 * Erasure note: Swedish bokföringslagen (accounting law) requires invoice records to be
 * retained for 7 years, so a full hard-delete of a customer who has invoices is not legally
 * safe. Instead, "erasure" anonymizes personal-identifying fields (name, email, phone,
 * address) while leaving financial records (invoices, amounts, dates) intact for compliance.
 */

require_once __DIR__ . '/db.php';

/** Build a full JSON-exportable data dump for a customer. */
function gdpr_query(string $sql, array $params): array {
    $s = db()->prepare($sql);
    $s->execute($params);
    return $s->fetchAll();
}

function gdpr_export_customer(int $customerId): array {
    $get = 'gdpr_query';

    $customer = $get("SELECT * FROM customers WHERE id = ?", [$customerId]);
    $projectIds = array_column($get("SELECT id FROM projects WHERE customer_id = ?", [$customerId]), 'id');

    return [
        'exported_at' => date('c'),
        'customer'    => $customer[0] ?? null,
        'leads'       => $get("SELECT * FROM leads WHERE customer_id = ?", [$customerId]),
        'quotes'      => $get("SELECT * FROM quotes WHERE customer_id = ?", [$customerId]),
        'projects'    => $get("SELECT * FROM projects WHERE customer_id = ?", [$customerId]),
        'invoices'    => $get("SELECT * FROM invoices WHERE customer_id = ?", [$customerId]),
        'portal_account' => $get("SELECT id, email, active, last_login, created_at FROM portal_users WHERE customer_id = ?", [$customerId]),
        'messages'    => $projectIds ? $get(
            "SELECT * FROM portal_messages WHERE project_id IN (" . implode(',', array_fill(0, count($projectIds), '?')) . ") AND sender_type='customer'",
            $projectIds
        ) : [],
        'quote_signatures' => $get(
            "SELECT qs.* FROM quote_signatures qs JOIN quotes q ON q.id = qs.quote_id WHERE q.customer_id = ?", [$customerId]
        ),
    ];
}

/** Build a full JSON-exportable data dump for a supplier. */
function gdpr_export_supplier(int $supplierId): array {
    $get = 'gdpr_query';

    return [
        'exported_at' => date('c'),
        'supplier'    => $get("SELECT * FROM suppliers WHERE id = ?", [$supplierId])[0] ?? null,
        'job_assignments' => $get("SELECT * FROM job_assignments WHERE supplier_id = ?", [$supplierId]),
        'time_reports'    => $get("SELECT * FROM time_reports WHERE supplier_id = ?", [$supplierId]),
        'documents'       => $get("SELECT id, original_name, category, created_at FROM supplier_documents WHERE supplier_id = ?", [$supplierId]),
        'portal_account'  => $get("SELECT id, email, active, last_login, created_at FROM supplier_users WHERE supplier_id = ?", [$supplierId]),
    ];
}

/** Anonymize a customer's PII while preserving financial records for legal retention. */
function gdpr_anonymize_customer(int $customerId): void {
    $pdo = db();
    $placeholder = 'Borttagen (GDPR #' . $customerId . ')';
    $pdo->prepare(
        "UPDATE customers SET name = ?, email = NULL, phone = NULL, address = NULL, notes = 'Personuppgifter borttagna enligt GDPR-begäran.' WHERE id = ?"
    )->execute([$placeholder, $customerId]);
    $pdo->prepare("UPDATE portal_users SET active = 0, email = ? WHERE customer_id = ?")
        ->execute(['deleted-' . $customerId . '@anonymized.local', $customerId]);
    audit('gdpr_anonymize', 'customer', $customerId, 'PII anonymized; financial records retained per bokföringslagen.');
}

/** Anonymize a supplier's PII while preserving job/payment history for legal retention. */
function gdpr_anonymize_supplier(int $supplierId): void {
    $pdo = db();
    $placeholder = 'Borttagen (GDPR #' . $supplierId . ')';
    $pdo->prepare(
        "UPDATE suppliers SET company = ?, contact = NULL, email = NULL, phone = NULL, notes = 'Personuppgifter borttagna enligt GDPR-begäran.' WHERE id = ?"
    )->execute([$placeholder, $supplierId]);
    $pdo->prepare("UPDATE supplier_users SET active = 0, email = ? WHERE supplier_id = ?")
        ->execute(['deleted-' . $supplierId . '@anonymized.local', $supplierId]);
    audit('gdpr_anonymize', 'supplier', $supplierId, 'PII anonymized; job/payment records retained per bokföringslagen.');
}
