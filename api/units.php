<?php

require_once __DIR__ . '/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['success' => false, 'message' => 'Method not allowed'], 405);
}

api_require_csrf();
$action = input('action');

switch ($action) {
    case 'update_status':
        if (!is_officer_role($user['role'])) {
            json_response(['success' => false, 'message' => 'Forbidden'], 403);
        }
        $unit = get_user_unit($pdo, $user['id']);
        if (!$unit) {
            json_response(['success' => false, 'message' => 'Unit not registered']);
        }
        $code = input('status_code');
        $statuses = unit_statuses();
        if (!isset($statuses[$code])) {
            json_response(['success' => false, 'message' => 'Invalid status code']);
        }
        $stmt = $pdo->prepare('UPDATE units SET status_code=?, status_label=?, last_update=NOW() WHERE id=?');
        $stmt->execute([$code, $statuses[$code], $unit['id']]);
        json_response(['success' => true, 'message' => 'Status updated', 'status_code' => $code, 'status_label' => $statuses[$code]]);

    case 'set_status_dispatch':
        if (!can_manage_cad($user['role'])) {
            json_response(['success' => false, 'message' => 'Forbidden'], 403);
        }
        $unitId = (int) input('unit_id');
        $code = input('status_code');
        $statuses = unit_statuses();
        if (!isset($statuses[$code])) {
            json_response(['success' => false, 'message' => 'Invalid status']);
        }
        $stmt = $pdo->prepare('UPDATE units SET status_code=?, status_label=?, last_update=NOW() WHERE id=?');
        $stmt->execute([$code, $statuses[$code], $unitId]);
        json_response(['success' => true, 'message' => 'Unit status updated']);

    default:
        json_response(['success' => false, 'message' => 'Unknown action'], 400);
}
