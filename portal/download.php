<?php
require_once __DIR__ . '/includes/auth.php';
$pu  = portal_require();
$cid = (int)$pu['customer_id'];

$id = (int)($_GET['id'] ?? 0);
$s  = db()->prepare(
    "SELECT d.* FROM portal_documents d
     JOIN projects p ON p.id = d.project_id
     WHERE d.id = ? AND p.customer_id = ?"
);
$s->execute([$id, $cid]);
$doc = $s->fetch();

if (!$doc) { http_response_code(404); echo 'Fil hittades inte.'; exit; }

$path = __DIR__ . '/../data/portal-uploads/' . basename($doc['filename']);
if (!file_exists($path)) { http_response_code(404); echo 'Fil saknas på servern.'; exit; }

header('Content-Type: ' . ($doc['mime_type'] ?: 'application/octet-stream'));
header('Content-Disposition: attachment; filename="' . addslashes($doc['original_name']) . '"');
header('Content-Length: ' . filesize($path));
readfile($path);
