<?php
/** AJAX: mark task done */
require_once __DIR__ . '/../includes/auth.php';
header('Content-Type: application/json');
if (!current_user()) { http_response_code(401); echo json_encode(['success'=>false]); exit; }
$input = json_decode(file_get_contents('php://input'), true);
$id = (int)($input['id'] ?? 0);
db()->prepare("UPDATE tasks SET done=1 WHERE id=?")->execute([$id]);
echo json_encode(['success'=>true]);
