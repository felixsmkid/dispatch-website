<?php
require_once __DIR__ . '/includes/init.php';
require_login();

$user = current_user();
if (!is_officer_role($user['role'])) {
    redirect('/dashboard.php');
}

$department = department_for_role($user['role']);
$error = '';
$success = '';

$existing = get_user_unit($pdo, $user['id']);
if ($existing && is_post() === false) {
    redirect('/officer.php');
}

if (is_post()) {
    require_csrf();
    $characterName = input('character_name');
    $callsign = input('callsign');
    $rank = input('rank');

    $err = validate_required([
        'Nama karakter' => $characterName,
        'Callsign' => $callsign,
        'Pangkat' => $rank,
    ]);
    if ($err) {
        $error = $err;
    } else {
        $statuses = unit_statuses();
        $stmt = $pdo->prepare('SELECT id FROM units WHERE user_id = ?');
        $stmt->execute([$user['id']]);
        $unitId = $stmt->fetchColumn();

        if ($unitId) {
            $stmt = $pdo->prepare('UPDATE units SET character_name=?, callsign=?, rank_title=?, department=?, status_code=?, status_label=?, is_online=1, last_update=NOW() WHERE user_id=?');
            $stmt->execute([$characterName, $callsign, $rank, $department, '10-8', $statuses['10-8'], $user['id']]);
        } else {
            $stmt = $pdo->prepare('INSERT INTO units (user_id, character_name, callsign, rank_title, department, status_code, status_label) VALUES (?,?,?,?,?,?,?)');
            $stmt->execute([$user['id'], $characterName, $callsign, $rank, $department, '10-8', $statuses['10-8']]);
        }
        redirect('/officer.php');
    }
}

$pageTitle = 'Unit Setup';
$showNavbar = true;
require __DIR__ . '/includes/header.php';
?>
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="card border-primary">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-person-badge"></i> Active Unit Registration</h5>
                </div>
                <div class="card-body">
                    <div class="text-center mb-4">
                        <img src="/assets/img/<?= strtolower($department) ?>.svg" alt="<?= e($department) ?>" height="64">
                        <h6 class="mt-2"><?= $department === 'LSPD' ? 'Los Santos Police Department' : "Blaine County Sheriff's Office" ?></h6>
                    </div>

                    <?php if ($error): ?>
                    <div class="alert alert-danger"><?= e($error) ?></div>
                    <?php endif; ?>

                    <form method="post">
                        <?= csrf_field() ?>
                        <div class="mb-3">
                            <label class="form-label">Character Name</label>
                            <input type="text" name="character_name" class="form-control" value="<?= e(input('character_name')) ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Callsign</label>
                            <input type="text" name="callsign" class="form-control" placeholder="e.g. 1-Adam-12" value="<?= e(input('callsign')) ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Rank</label>
                            <input type="text" name="rank" class="form-control" placeholder="e.g. Officer / Deputy" value="<?= e(input('rank')) ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Department</label>
                            <input type="text" class="form-control" value="<?= e($department) ?>" readonly>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-radioactive"></i> Go On Duty (10-41)
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
