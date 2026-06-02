<?php

require_once __DIR__ . '/bootstrap.php';

if (!can_manage_cad($user['role'])) {
    json_response(['success' => false, 'message' => 'Forbidden'], 403);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['success' => false, 'message' => 'Method not allowed'], 405);
}

api_require_csrf();
$action = input('action');

switch ($action) {
    case 'create':
        $vehicle = input('vehicle');
        $description = input('description');
        $reason = input('reason');
        $date = input('bolo_date', date('Y-m-d'));

        if ($vehicle === '' || $description === '' || $reason === '') {
            json_response(['success' => false, 'message' => 'Vehicle, description, dan reason wajib diisi.']);
        }

        $stmt = $pdo->prepare('INSERT INTO bolos (vehicle, plate, description, reason, bolo_date, created_by) VALUES (?,?,?,?,?,?)');
        $stmt->execute([$vehicle, input('plate') ?: null, $description, $reason, $date, $user['id']]);
        json_response(['success' => true, 'message' => 'BOLO created']);

    case 'deactivate':
        $id = (int) input('bolo_id');
        $pdo->prepare("UPDATE bolos SET status='inactive' WHERE id=?")->execute([$id]);
        json_response(['success' => true, 'message' => 'BOLO deactivated']);

    default:
        json_response(['success' => false, 'message' => 'Unknown action'], 400);
}
