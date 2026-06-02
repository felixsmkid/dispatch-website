<?php

require_once __DIR__ . '/bootstrap.php';

if (!can_manage_cad($user['role'])) {
    json_response(['success' => false, 'message' => 'Forbidden'], 403);
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && input('action') === 'get') {
    $id = (int) input('pursuit_id');
    $stmt = $pdo->prepare('SELECT p.*, pu.callsign AS primary_callsign, su.callsign AS secondary_callsign FROM pursuits p LEFT JOIN units pu ON pu.id=p.primary_unit_id LEFT JOIN units su ON su.id=p.secondary_unit_id WHERE p.id=?');
    $stmt->execute([$id]);
    $pursuit = $stmt->fetch();
    if (!$pursuit) {
        json_response(['success' => false, 'message' => 'Not found'], 404);
    }
    $stmt = $pdo->prepare('SELECT u.id, u.callsign, u.department FROM pursuit_units pu JOIN units u ON u.id=pu.unit_id WHERE pu.pursuit_id=?');
    $stmt->execute([$id]);
    $pursuit['units'] = $stmt->fetchAll();
    json_response(['success' => true, 'pursuit' => $pursuit]);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['success' => false, 'message' => 'Method not allowed'], 405);
}

api_require_csrf();
$action = input('action');

switch ($action) {
    case 'create':
        $vehicle = input('vehicle_description');
        if ($vehicle === '') {
            json_response(['success' => false, 'message' => 'Vehicle description required']);
        }
        $code = generate_pursuit_code($pdo);
        $stmt = $pdo->prepare('INSERT INTO pursuits (pursuit_code, vehicle_description, plate, occupants, charges, current_location, created_by) VALUES (?,?,?,?,?,?,?)');
        $stmt->execute([
            $code,
            $vehicle,
            input('plate') ?: null,
            input('occupants') ?: null,
            input('charges') ?: null,
            input('current_location') ?: null,
            $user['id'],
        ]);
        json_response(['success' => true, 'message' => 'Pursuit started', 'pursuit_code' => $code]);

    case 'update':
        $id = (int) input('pursuit_id');
        $stmt = $pdo->prepare('UPDATE pursuits SET vehicle_description=?, plate=?, occupants=?, charges=?, current_location=?, updated_at=NOW() WHERE id=?');
        $stmt->execute([
            input('vehicle_description'),
            input('plate') ?: null,
            input('occupants') ?: null,
            input('charges') ?: null,
            input('current_location') ?: null,
            $id,
        ]);
        json_response(['success' => true, 'message' => 'Pursuit updated']);

    case 'toggle':
        $id = (int) input('pursuit_id');
        $field = input('field');
        $allowed = ['pit_authorized', 'spike_authorized', 'air_unit_requested'];
        if (!in_array($field, $allowed, true)) {
            json_response(['success' => false, 'message' => 'Invalid field']);
        }
        $stmt = $pdo->prepare("UPDATE pursuits SET {$field} = NOT {$field} WHERE id = ?");
        $stmt->execute([$id]);
        json_response(['success' => true, 'message' => 'Updated']);

    case 'set_primary':
        $id = (int) input('pursuit_id');
        $unitId = (int) input('unit_id');
        $pdo->prepare('UPDATE pursuits SET primary_unit_id=? WHERE id=?')->execute([$unitId, $id]);
        $pdo->prepare('INSERT IGNORE INTO pursuit_units (pursuit_id, unit_id) VALUES (?,?)')->execute([$id, $unitId]);
        json_response(['success' => true, 'message' => 'Primary unit set']);

    case 'set_secondary':
        $id = (int) input('pursuit_id');
        $unitId = (int) input('unit_id');
        $pdo->prepare('UPDATE pursuits SET secondary_unit_id=? WHERE id=?')->execute([$unitId, $id]);
        $pdo->prepare('INSERT IGNORE INTO pursuit_units (pursuit_id, unit_id) VALUES (?,?)')->execute([$id, $unitId]);
        json_response(['success' => true, 'message' => 'Secondary unit set']);

    case 'add_unit':
        $id = (int) input('pursuit_id');
        $unitId = (int) input('unit_id');
        $stmt = $pdo->prepare('INSERT IGNORE INTO pursuit_units (pursuit_id, unit_id) VALUES (?,?)');
        $stmt->execute([$id, $unitId]);
        json_response(['success' => true, 'message' => 'Unit added']);

    case 'remove_unit':
        $id = (int) input('pursuit_id');
        $unitId = (int) input('unit_id');
        $pdo->prepare('DELETE FROM pursuit_units WHERE pursuit_id=? AND unit_id=?')->execute([$id, $unitId]);
        $pdo->prepare('UPDATE pursuits SET primary_unit_id=NULL WHERE id=? AND primary_unit_id=?')->execute([$id, $unitId]);
        $pdo->prepare('UPDATE pursuits SET secondary_unit_id=NULL WHERE id=? AND secondary_unit_id=?')->execute([$id, $unitId]);
        json_response(['success' => true, 'message' => 'Unit removed']);

    case 'end':
        $id = (int) input('pursuit_id');
        $pdo->prepare("UPDATE pursuits SET status='ended', ended_at=NOW() WHERE id=?")->execute([$id]);
        json_response(['success' => true, 'message' => 'Pursuit ended']);

    default:
        json_response(['success' => false, 'message' => 'Unknown action'], 400);
}
