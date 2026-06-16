<?php
require_once __DIR__ . '/includes/auth.php';
$su  = supp_require();
$sid = (int)$su['supplier_id'];
$id  = (int)($_GET['id'] ?? 0);

$s = db()->prepare("SELECT * FROM supplier_documents WHERE id=? AND supplier_id=?");
$s->execute([$id, $sid]);
$doc = $s->fetch();
if (!$doc) { http_response_code(404); exit('Not found'); }

$path = dirname(__DIR__) . '/data/portal-uploads/supplier/' . $doc['stored_name'];
if (!is_file($path)) { http_response_code(404); exit('File missing'); }

header('Content-Type: ' . $doc['mime_type']);
header('Content-Disposition: attachment; filename="' . addslashes($doc['original_name']) . '"');
header('Content-Length: ' . filesize($path));
header('Cache-Control: private, no-cache');
readfile($path);
exit;
