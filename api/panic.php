<?php

require_once __DIR__ . '/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['success' => false, 'message' => 'Method not allowed'], 405);
}

api_require_csrf();
$action = input('action');

switch ($action) {
    case 'activate':
        if (!is_officer_role($user['role'])) {
            json_response(['success' => false, 'message' => 'Forbidden'], 403);
        }
        $unit = get_user_unit($pdo, $user['id']);
        if (!$unit) {
            json_response(['success' => false, 'message' => 'Unit not found']);
        }
        $location = input('location') ?: 'Unknown Location';
        $stmt = $pdo->prepare('INSERT INTO panic_alerts (unit_id, officer_name, callsign, location, department) VALUES (?,?,?,?,?)');
        $stmt->execute([
            $unit['id'],
            $unit['character_name'],
            $unit['callsign'],
            $location,
            $unit['department'],
        ]);
        json_response(['success' => true, 'message' => 'Panic activated', 'panic_id' => (int) $pdo->lastInsertId()]);

    case 'resolve':
        if (!can_manage_cad($user['role'])) {
            json_response(['success' => false, 'message' => 'Forbidden'], 403);
        }
        $panicId = (int) input('panic_id');
        $stmt = $pdo->prepare('UPDATE panic_alerts SET is_active=0, resolved_at=NOW(), resolved_by=? WHERE id=?');
        $stmt->execute([$user['id'], $panicId]);
        json_response(['success' => true, 'message' => 'Panic resolved']);

    case 'resolve_all':
        if (!can_manage_cad($user['role'])) {
            json_response(['success' => false, 'message' => 'Forbidden'], 403);
        }
        $pdo->prepare('UPDATE panic_alerts SET is_active=0, resolved_at=NOW(), resolved_by=? WHERE is_active=1')->execute([$user['id']]);
        json_response(['success' => true, 'message' => 'All panics resolved']);

    default:
        json_response(['success' => false, 'message' => 'Unknown action'], 400);
}
