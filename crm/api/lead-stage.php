<?php
/** AJAX: update lead stage (kanban drag & drop) */
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';
header('Content-Type: application/json');
$me = current_user();
if (!$me) { http_response_code(401); echo json_encode(['success'=>false]); exit; }

$input = json_decode(file_get_contents('php://input'), true);
$id    = (int)($input['id'] ?? 0);
$stage = $input['stage'] ?? '';

if (!$id || !isset(LEAD_STAGES[$stage])) { echo json_encode(['success'=>false,'message'=>'Ogiltig data']); exit; }

db()->prepare("UPDATE leads SET stage=?, updated_at=datetime('now','localtime') WHERE id=?")->execute([$stage, $id]);
log_timeline('lead', $id, 'status', 'Status ändrad till ' . LEAD_STAGES[$stage]['label'] . ' (drag & drop)', '', $me['id']);
audit('lead_stage', 'lead', $id, $stage);
echo json_encode(['success'=>true]);
