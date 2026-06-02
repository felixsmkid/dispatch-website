<?php
require_once __DIR__ . '/includes/init.php';
require_login();

$user = current_user();
if (!is_officer_role($user['role'])) {
    redirect('/dashboard.php');
}

$unit = get_user_unit($pdo, $user['id']);
if (!$unit) {
    redirect('/unit-setup.php');
}

$department = $unit['department'];
$statuses = unit_statuses();

$pageTitle = 'Unit Panel';
$showNavbar = true;
$canDismissPanic = false;
$extraScripts = ['/assets/js/officer.js'];
require __DIR__ . '/includes/header.php';
?>
<div class="container-fluid py-3">
    <div class="row g-3">
        <div class="col-lg-4">
            <div class="card border-primary h-100">
                <div class="card-header d-flex align-items-center gap-2">
                    <img src="/assets/img/<?= strtolower($department) ?>.svg" alt="" height="40">
                    <div>
                        <h5 class="mb-0"><?= e($unit['callsign']) ?></h5>
                        <small class="text-muted"><?= e($unit['character_name']) ?> &mdash; <?= e($unit['rank_title']) ?></small>
                    </div>
                </div>
                <div class="card-body">
                    <p class="mb-2"><strong>Department:</strong> <?= e($department) ?></p>
                    <p class="mb-3"><strong>Status:</strong> <span class="badge bg-success" id="current-status-badge"><?= e($unit['status_code']) ?> <?= e($unit['status_label']) ?></span></p>

                    <label class="form-label">Update Status</label>
                    <select id="unit-status-select" class="form-select mb-3">
                        <?php foreach ($statuses as $code => $label): ?>
                        <option value="<?= e($code) ?>" <?= $unit['status_code'] === $code ? 'selected' : '' ?>><?= e($code) ?> — <?= e($label) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button type="button" id="btn-update-status" class="btn btn-outline-primary w-100 mb-3">
                        <i class="bi bi-arrow-repeat"></i> Update Status
                    </button>

                    <div class="mb-3">
                        <label class="form-label">Current Location (for Panic)</label>
                        <input type="text" id="officer-location" class="form-control" placeholder="Street / intersection">
                    </div>

                    <button type="button" id="btn-panic" class="btn btn-danger btn-lg w-100 panic-btn">
                        <i class="bi bi-exclamation-triangle-fill"></i> PANIC BUTTON
                    </button>
                    <p class="text-muted small mt-2 mb-0 text-center">10-78 Emergency Assistance</p>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card h-100">
                <div class="card-header"><i class="bi bi-telephone-inbound"></i> My Assigned Calls</div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-dark table-hover table-sm mb-0">
                            <thead>
                                <tr>
                                    <th>Call #</th>
                                    <th>Location</th>
                                    <th>Priority</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody id="officer-calls-body">
                                <tr><td colspan="4" class="text-center text-muted py-3">Loading...</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<input type="hidden" id="csrf-token" value="<?= e(csrf_token()) ?>">
<?php require __DIR__ . '/includes/footer.php'; ?>
