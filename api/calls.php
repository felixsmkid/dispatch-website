<?php

require_once __DIR__ . '/bootstrap.php';

if (!can_manage_cad($user['role'])) {
    json_response(['success' => false, 'message' => 'Forbidden'], 403);
}

$action = input('action');

if ($_SERVER['REQUEST_METHOD'] === 'GET' && $action === 'get') {
    $id = (int) input('call_id');
    $stmt = $pdo->prepare('SELECT * FROM calls WHERE id = ?');
    $stmt->execute([$id]);
    $call = $stmt->fetch();
    if (!$call) {
        json_response(['success' => false, 'message' => 'Call not found'], 404);
    }
    $stmt = $pdo->prepare('SELECT ca.*, u.callsign, u.character_name, u.department FROM call_assignments ca JOIN units u ON u.id = ca.unit_id WHERE ca.call_id = ?');
    $stmt->execute([$id]);
    $call['assignments'] = $stmt->fetchAll();
    json_response(['success' => true, 'call' => $call]);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['success' => false, 'message' => 'Method not allowed'], 405);
}

api_require_csrf();
$action = input('action');

switch ($action) {
    case 'create':
        $location = input('location');
        $description = input('description');
        $priority = input('priority', 'medium');
        $status = input('status', 'pending');
        $callerName = input('caller_name');
        $phone = input('phone_number');

        if ($location === '' || $description === '') {
            json_response(['success' => false, 'message' => 'Location dan description wajib diisi.']);
        }
        if (!in_array($priority, call_priorities(), true)) {
            $priority = 'medium';
        }
        if (!in_array($status, ['pending', 'active'], true)) {
            $status = 'pending';
        }

        $callNumber = generate_call_number($pdo);
        $stmt = $pdo->prepare('INSERT INTO calls (call_number, caller_name, phone_number, location, description, priority, status, created_by) VALUES (?,?,?,?,?,?,?,?)');
        $stmt->execute([$callNumber, $callerName ?: null, $phone ?: null, $location, $description, $priority, $status, $user['id']]);
        json_response(['success' => true, 'message' => 'Call created', 'call_number' => $callNumber]);

    case 'update':
        $id = (int) input('call_id');
        $location = input('location');
        $description = input('description');
        $priority = input('priority');
        $status = input('status');
        $callerName = input('caller_name');
        $phone = input('phone_number');

        if ($location === '' || $description === '') {
            json_response(['success' => false, 'message' => 'Location dan description wajib diisi.']);
        }

        $stmt = $pdo->prepare('UPDATE calls SET caller_name=?, phone_number=?, location=?, description=?, priority=?, status=?, updated_at=NOW() WHERE id=?');
        $stmt->execute([$callerName ?: null, $phone ?: null, $location, $description, $priority, $status, $id]);
        json_response(['success' => true, 'message' => 'Call updated']);

    case 'close':
        $id = (int) input('call_id');
        $stmt = $pdo->prepare("UPDATE calls SET status='closed', closed_at=NOW() WHERE id=?");
        $stmt->execute([$id]);
        json_response(['success' => true, 'message' => 'Call closed']);

    case 'assign':
        $callId = (int) input('call_id');
        $unitId = (int) input('unit_id');
        $type = input('assignment_type', 'assigned');

        if (!in_array($type, ['primary', 'secondary', 'assigned'], true)) {
            $type = 'assigned';
        }

        if ($type === 'primary') {
            $pdo->prepare("UPDATE call_assignments SET assignment_type='assigned' WHERE call_id=? AND assignment_type='primary'")->execute([$callId]);
        }
        if ($type === 'secondary') {
            $pdo->prepare("UPDATE call_assignments SET assignment_type='assigned' WHERE call_id=? AND assignment_type='secondary'")->execute([$callId]);
        }

        $stmt = $pdo->prepare('INSERT INTO call_assignments (call_id, unit_id, assignment_type) VALUES (?,?,?) ON DUPLICATE KEY UPDATE assignment_type=VALUES(assignment_type)');
        $stmt->execute([$callId, $unitId, $type]);

        if ($type === 'primary' || $type === 'secondary') {
            $pdo->prepare("UPDATE calls SET status='active' WHERE id=? AND status='pending'")->execute([$callId]);
        }
        json_response(['success' => true, 'message' => 'Unit assigned']);

    case 'unassign':
        $callId = (int) input('call_id');
        $unitId = (int) input('unit_id');
        $stmt = $pdo->prepare('DELETE FROM call_assignments WHERE call_id=? AND unit_id=?');
        $stmt->execute([$callId, $unitId]);
        json_response(['success' => true, 'message' => 'Unit removed']);

    default:
        json_response(['success' => false, 'message' => 'Unknown action'], 400);
}
