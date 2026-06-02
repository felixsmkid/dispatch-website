<?php

require_once __DIR__ . '/bootstrap.php';

$role = $user['role'];
$isDispatch = can_access_dispatch($role);

// Stats
$stats = [
    'active_calls' => (int) $pdo->query("SELECT COUNT(*) FROM calls WHERE status = 'active'")->fetchColumn(),
    'pending_calls' => (int) $pdo->query("SELECT COUNT(*) FROM calls WHERE status = 'pending'")->fetchColumn(),
    'active_pursuits' => (int) $pdo->query("SELECT COUNT(*) FROM pursuits WHERE status = 'active'")->fetchColumn(),
    'active_bolos' => (int) $pdo->query("SELECT COUNT(*) FROM bolos WHERE status = 'active'")->fetchColumn(),
    'online_units' => (int) $pdo->query('SELECT COUNT(*) FROM units WHERE is_online = 1')->fetchColumn(),
    'lspd_units' => (int) $pdo->query("SELECT COUNT(*) FROM units WHERE is_online = 1 AND department = 'LSPD'")->fetchColumn(),
    'bcso_units' => (int) $pdo->query("SELECT COUNT(*) FROM units WHERE is_online = 1 AND department = 'BCSO'")->fetchColumn(),
    'panic_alerts' => (int) $pdo->query('SELECT COUNT(*) FROM panic_alerts WHERE is_active = 1')->fetchColumn(),
];

// Calls with assignments
$callsSql = "SELECT c.*, 
    (SELECT GROUP_CONCAT(CONCAT(u.callsign, ':', ca.assignment_type) SEPARATOR '|') 
     FROM call_assignments ca JOIN units u ON u.id = ca.unit_id WHERE ca.call_id = c.id) AS assignments
    FROM calls c WHERE c.status != 'closed' ORDER BY 
    FIELD(c.priority, 'emergency', 'high', 'medium', 'low'), c.created_at DESC";
$calls = $pdo->query($callsSql)->fetchAll();

// Pursuits
$pursuitsSql = "SELECT p.*, 
    pu.callsign AS primary_callsign, su.callsign AS secondary_callsign
    FROM pursuits p
    LEFT JOIN units pu ON pu.id = p.primary_unit_id
    LEFT JOIN units su ON su.id = p.secondary_unit_id
    WHERE p.status = 'active' ORDER BY p.created_at DESC";
$pursuits = $pdo->query($pursuitsSql)->fetchAll();

foreach ($pursuits as &$p) {
    $stmt = $pdo->prepare('SELECT u.id, u.callsign, u.department FROM pursuit_units pu JOIN units u ON u.id = pu.unit_id WHERE pu.pursuit_id = ?');
    $stmt->execute([$p['id']]);
    $p['units'] = $stmt->fetchAll();
}
unset($p);

// BOLOs
$bolos = $pdo->query("SELECT * FROM bolos WHERE status = 'active' ORDER BY created_at DESC")->fetchAll();

// Panic
$panics = $pdo->query('SELECT * FROM panic_alerts WHERE is_active = 1 ORDER BY created_at DESC')->fetchAll();

// Units
$units = $pdo->query('SELECT * FROM units WHERE is_online = 1 ORDER BY department, callsign')->fetchAll();

// Officer assigned calls
$officerCalls = [];
if (is_officer_role($role)) {
    $unit = get_user_unit($pdo, $user['id']);
    if ($unit) {
        $stmt = $pdo->prepare("SELECT c.* FROM calls c
            JOIN call_assignments ca ON ca.call_id = c.id
            WHERE ca.unit_id = ? AND c.status != 'closed'
            ORDER BY c.created_at DESC");
        $stmt->execute([$unit['id']]);
        $officerCalls = $stmt->fetchAll();
    }
}

// Active panic for overlay (most recent)
$activePanic = $panics[0] ?? null;

json_response([
    'success' => true,
    'timestamp' => date('c'),
    'stats' => $stats,
    'calls' => $calls,
    'pursuits' => $pursuits,
    'bolos' => $bolos,
    'panics' => $panics,
    'units' => $units,
    'officer_calls' => $officerCalls,
    'active_panic' => $activePanic,
    'is_dispatch' => $isDispatch,
]);
