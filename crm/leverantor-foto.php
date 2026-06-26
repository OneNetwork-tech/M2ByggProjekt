<?php
require_once __DIR__ . '/includes/auth.php';
require_login();

$id = (int)($_GET['id'] ?? 0);
$s = db()->prepare("SELECT * FROM job_photos WHERE id=?");
$s->execute([$id]);
$photo = $s->fetch();
if (!$photo) { http_response_code(404); exit('Not found'); }

$path = dirname(__DIR__) . '/data/portal-uploads/job-photos/' . $photo['stored_name'];
if (!is_file($path)) { http_response_code(404); exit('File missing'); }

$mime = mime_content_type($path) ?: 'image/jpeg';
header('Content-Type: ' . $mime);
header('Content-Length: ' . filesize($path));
header('Cache-Control: private, max-age=86400');
readfile($path);
exit;
