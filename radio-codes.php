<?php
require_once __DIR__ . '/includes/init.php';
require_login();

$radioCodes = [
    '10-Codes' => [
        '10-1' => 'Meeting',
        '10-2' => 'Good Signal',
        '10-3' => 'Stop Transmission',
        '10-4' => 'OK',
        '10-7' => 'Not In Service',
        '10-8' => 'Back In Service',
        '10-9' => 'Repeat',
        '10-10' => 'Fight In Progress',
        '10-11' => 'Someone With Firearms',
        '10-12' => 'Stand By',
        '10-13A' => 'Officer Down Emergency',
        '10-13B' => 'Officer Down Non-Emergency',
        '10-14' => 'Medic Down',
        '10-20' => 'Location',
        '10-23' => 'Arrive On Location',
        '10-31A' => 'Burglary',
        '10-31B' => 'Robbery Store',
        '10-34' => 'Illegal Activity',
        '10-38' => 'Traffic Stop',
        '10-41' => 'On Duty',
        '10-42' => 'Off Duty',
        '10-45A' => 'Illegal Hunting',
        '10-47' => 'Injured Person',
        '10-50' => 'Accident',
        '10-52' => 'Need EMS',
        '10-57' => 'Pursuit In Progress',
        '10-60' => 'Vehicle Description',
        '10-60A' => 'Car Jacking',
        '10-60B' => 'Vehicle Theft',
        '10-61' => 'Suspect Description',
        '10-71A' => 'Shots Fired On Foot',
        '10-71B' => 'Shots Fired From Vehicle',
        '10-76' => 'En Route',
        '10-77' => 'Need Assistance Non-Emergency',
        '10-78' => 'Need Assistance Emergency',
        '10-80' => 'Felony Stop',
        '10-90' => 'Bank Robbery',
        '10-94' => 'Illegal Racing',
        '10-95' => 'Suspect In Custody',
        '10-99' => 'Investigation Area',
    ],
    'Signal Codes' => [
        'Code 0' => 'Robbery Principal',
        'Code 2' => 'Lights Only',
        'Code 3' => 'Lights and Sirens',
        'Code 4' => 'Situation Under Control',
        'Code 6' => 'In Operation',
    ],
    'Unit Status (CAD)' => unit_statuses(),
];

$pageTitle = 'Radio Codes';
$showNavbar = true;
require __DIR__ . '/includes/header.php';
?>
<div class="container py-4">
    <div class="text-center mb-4">
        <h2 class="text-primary"><i class="bi bi-broadcast"></i> Radio Codes Reference</h2>
        <p class="text-muted"><?= e(APP_SHORT) ?> — Emergency Communications Center</p>
    </div>
    <div class="row g-4">
        <?php foreach ($radioCodes as $section => $codes): ?>
        <div class="col-lg-6">
            <div class="card cad-card h-100">
                <div class="card-header"><i class="bi bi-radioactive"></i> <?= e($section) ?></div>
                <div class="card-body p-0">
                    <table class="table table-dark table-sm mb-0">
                        <thead><tr><th style="width:30%">Code</th><th>Meaning</th></tr></thead>
                        <tbody>
                        <?php foreach ($codes as $code => $meaning): ?>
                        <tr>
                            <td><span class="badge bg-primary"><?= e($code) ?></span></td>
                            <td><?= e($meaning) ?></td>
                        </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
