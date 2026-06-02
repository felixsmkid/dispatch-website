<?php
require_once __DIR__ . '/includes/init.php';
require_cad_access();

$user = current_user();
$pageTitle = 'Dispatch Dashboard';
$showNavbar = true;
$canDismissPanic = true;
$extraScripts = ['/assets/js/dashboard.js'];
require __DIR__ . '/includes/header.php';
?>
<div class="container-fluid px-3 py-2">
    <div class="d-flex flex-wrap align-items-center justify-content-between mb-3 gap-2">
        <div>
            <h4 class="mb-0 text-primary"><i class="bi bi-broadcast-pin"></i> Central Dispatch Console</h4>
            <small class="text-muted">Realtime CAD — Last sync: <span id="last-sync">-</span></small>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <button class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#modalNewCall"><i class="bi bi-telephone-plus"></i> New Call</button>
            <button class="btn btn-sm btn-warning text-dark" data-bs-toggle="modal" data-bs-target="#modalNewPursuit"><i class="bi bi-car-front"></i> New Pursuit</button>
            <button class="btn btn-sm btn-info text-dark" data-bs-toggle="modal" data-bs-target="#modalNewBolo"><i class="bi bi-search"></i> New BOLO</button>
        </div>
    </div>

    <!-- Stats Row -->
    <div class="row g-2 mb-3" id="stats-row">
        <div class="col-6 col-md-4 col-xl"><div class="stat-card"><span class="stat-label">Active Calls</span><span class="stat-value text-success" id="stat-active-calls">0</span></div></div>
        <div class="col-6 col-md-4 col-xl"><div class="stat-card"><span class="stat-label">Pending Calls</span><span class="stat-value text-warning" id="stat-pending-calls">0</span></div></div>
        <div class="col-6 col-md-4 col-xl"><div class="stat-card"><span class="stat-label">Active Pursuits</span><span class="stat-value text-danger" id="stat-pursuits">0</span></div></div>
        <div class="col-6 col-md-4 col-xl"><div class="stat-card"><span class="stat-label">Active BOLO</span><span class="stat-value text-info" id="stat-bolos">0</span></div></div>
        <div class="col-6 col-md-4 col-xl"><div class="stat-card"><span class="stat-label">Online Units</span><span class="stat-value" id="stat-units">0</span></div></div>
        <div class="col-6 col-md-4 col-xl"><div class="stat-card"><span class="stat-label">LSPD Units</span><span class="stat-value text-primary" id="stat-lspd">0</span></div></div>
        <div class="col-6 col-md-4 col-xl"><div class="stat-card"><span class="stat-label">BCSO Units</span><span class="stat-value text-warning" id="stat-bcso">0</span></div></div>
        <div class="col-6 col-md-4 col-xl"><div class="stat-card border-danger"><span class="stat-label">Panic Alerts</span><span class="stat-value text-danger" id="stat-panic">0</span></div></div>
    </div>

    <div class="row g-3">
        <!-- Calls -->
        <div class="col-xl-6">
            <div class="card cad-card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span><i class="bi bi-telephone-forward"></i> 911 Call Center</span>
                    <span class="badge bg-secondary" id="calls-count">0</span>
                </div>
                <div class="card-body p-0 table-responsive" style="max-height:380px;overflow-y:auto">
                    <table class="table table-dark table-sm table-hover mb-0">
                        <thead class="sticky-top bg-dark"><tr><th>#</th><th>Location</th><th>Priority</th><th>Status</th><th></th></tr></thead>
                        <tbody id="calls-table-body"></tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Pursuits -->
        <div class="col-xl-6">
            <div class="card cad-card h-100">
                <div class="card-header"><i class="bi bi-speedometer2"></i> Active Pursuits</div>
                <div class="card-body p-0 table-responsive" style="max-height:380px;overflow-y:auto">
                    <table class="table table-dark table-sm table-hover mb-0">
                        <thead class="sticky-top bg-dark"><tr><th>ID</th><th>Vehicle</th><th>Plate</th><th>Location</th><th></th></tr></thead>
                        <tbody id="pursuits-table-body"></tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- BOLO -->
        <div class="col-lg-6">
            <div class="card cad-card">
                <div class="card-header"><i class="bi bi-binoculars"></i> Active BOLO</div>
                <div class="card-body p-0 table-responsive" style="max-height:280px;overflow-y:auto">
                    <table class="table table-dark table-sm table-hover mb-0">
                        <thead><tr><th>Vehicle</th><th>Plate</th><th>Reason</th><th></th></tr></thead>
                        <tbody id="bolos-table-body"></tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Panic -->
        <div class="col-lg-6">
            <div class="card cad-card border-danger">
                <div class="card-header text-danger"><i class="bi bi-exclamation-octagon"></i> Panic Alerts</div>
                <div class="card-body p-0" id="panic-list" style="max-height:280px;overflow-y:auto"></div>
            </div>
        </div>

        <!-- Unit Status Board -->
        <div class="col-12">
            <div class="card cad-card">
                <div class="card-header d-flex justify-content-between">
                    <span><i class="bi bi-people"></i> Unit Status Board</span>
                    <div>
                        <img src="/assets/img/lspd.svg" height="24" class="me-2" alt="LSPD">
                        <img src="/assets/img/bcso.svg" height="24" alt="BCSO">
                    </div>
                </div>
                <div class="card-body p-0 table-responsive">
                    <table class="table table-dark table-sm table-hover mb-0">
                        <thead><tr><th>Dept</th><th>Callsign</th><th>Name</th><th>Rank</th><th>Status</th><th>Last Update</th><th></th></tr></thead>
                        <tbody id="units-table-body"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<input type="hidden" id="csrf-token" value="<?= e(csrf_token()) ?>">

<!-- Modal: New Call -->
<div class="modal fade" id="modalNewCall" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content bg-dark">
            <div class="modal-header border-secondary">
                <h5 class="modal-title"><i class="bi bi-telephone-plus"></i> Create 911 Call</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="form-new-call">
                <div class="modal-body">
                    <div class="row g-2">
                        <div class="col-md-6"><label class="form-label">Caller Name</label><input type="text" name="caller_name" class="form-control"></div>
                        <div class="col-md-6"><label class="form-label">Phone Number</label><input type="text" name="phone_number" class="form-control"></div>
                        <div class="col-12"><label class="form-label">Location *</label><input type="text" name="location" class="form-control" required></div>
                        <div class="col-12"><label class="form-label">Description *</label><textarea name="description" class="form-control" rows="3" required></textarea></div>
                        <div class="col-md-6"><label class="form-label">Priority</label>
                            <select name="priority" class="form-select">
                                <option value="low">Low</option>
                                <option value="medium" selected>Medium</option>
                                <option value="high">High</option>
                                <option value="emergency">Emergency</option>
                            </select>
                        </div>
                        <div class="col-md-6"><label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                <option value="pending">Pending</option>
                                <option value="active">Active</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-secondary">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create Call</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Edit Call -->
<div class="modal fade" id="modalEditCall" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content bg-dark">
            <div class="modal-header border-secondary">
                <h5 class="modal-title">Edit Call <span id="edit-call-number"></span></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="form-edit-call">
                <input type="hidden" name="call_id" id="edit-call-id">
                <div class="modal-body">
                    <div class="row g-2">
                        <div class="col-md-6"><label class="form-label">Caller Name</label><input type="text" name="caller_name" id="edit-caller-name" class="form-control"></div>
                        <div class="col-md-6"><label class="form-label">Phone</label><input type="text" name="phone_number" id="edit-phone" class="form-control"></div>
                        <div class="col-12"><label class="form-label">Location</label><input type="text" name="location" id="edit-location" class="form-control" required></div>
                        <div class="col-12"><label class="form-label">Description</label><textarea name="description" id="edit-description" class="form-control" rows="3" required></textarea></div>
                        <div class="col-md-4"><label class="form-label">Priority</label><select name="priority" id="edit-priority" class="form-select"></select></div>
                        <div class="col-md-4"><label class="form-label">Status</label><select name="status" id="edit-status" class="form-select"></select></div>
                    </div>
                    <hr>
                    <h6>Unit Assignment</h6>
                    <div id="call-assignments-list" class="mb-2"></div>
                    <div class="row g-2 align-items-end">
                        <div class="col-md-5"><select id="assign-unit-select" class="form-select"><option value="">Select unit...</option></select></div>
                        <div class="col-md-4">
                            <select id="assign-type-select" class="form-select">
                                <option value="assigned">Assign</option>
                                <option value="primary">Primary</option>
                                <option value="secondary">Secondary</option>
                            </select>
                        </div>
                        <div class="col-md-3"><button type="button" class="btn btn-outline-primary w-100" id="btn-assign-unit">Assign</button></div>
                    </div>
                </div>
                <div class="modal-footer border-secondary">
                    <button type="button" class="btn btn-danger" id="btn-close-call">Close Call</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: New Pursuit -->
<div class="modal fade" id="modalNewPursuit" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content bg-dark">
            <div class="modal-header border-secondary">
                <h5 class="modal-title"><i class="bi bi-car-front"></i> New Pursuit</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="form-new-pursuit">
                <div class="modal-body">
                    <div class="row g-2">
                        <div class="col-md-6"><label class="form-label">Vehicle Description *</label><input type="text" name="vehicle_description" class="form-control" required></div>
                        <div class="col-md-6"><label class="form-label">Plate</label><input type="text" name="plate" class="form-control"></div>
                        <div class="col-md-6"><label class="form-label">Occupants</label><input type="text" name="occupants" class="form-control"></div>
                        <div class="col-md-6"><label class="form-label">Current Location</label><input type="text" name="current_location" class="form-control"></div>
                        <div class="col-12"><label class="form-label">Charges</label><textarea name="charges" class="form-control" rows="2"></textarea></div>
                    </div>
                </div>
                <div class="modal-footer border-secondary">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning text-dark">Start Pursuit</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Manage Pursuit -->
<div class="modal fade" id="modalManagePursuit" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content bg-dark">
            <div class="modal-header border-secondary">
                <h5 class="modal-title">Pursuit <span id="manage-pursuit-code"></span></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="manage-pursuit-body"></div>
            <div class="modal-footer border-secondary">
                <button type="button" class="btn btn-danger" id="btn-end-pursuit">End Pursuit</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal: New BOLO -->
<div class="modal fade" id="modalNewBolo" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content bg-dark">
            <div class="modal-header border-secondary">
                <h5 class="modal-title"><i class="bi bi-search"></i> New BOLO</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="form-new-bolo">
                <div class="modal-body">
                    <div class="mb-2"><label class="form-label">Vehicle *</label><input type="text" name="vehicle" class="form-control" required></div>
                    <div class="mb-2"><label class="form-label">Plate</label><input type="text" name="plate" class="form-control"></div>
                    <div class="mb-2"><label class="form-label">Description *</label><textarea name="description" class="form-control" rows="2" required></textarea></div>
                    <div class="mb-2"><label class="form-label">Reason *</label><input type="text" name="reason" class="form-control" required></div>
                    <div class="mb-2"><label class="form-label">Date</label><input type="date" name="bolo_date" class="form-control" value="<?= date('Y-m-d') ?>"></div>
                </div>
                <div class="modal-footer border-secondary">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-info text-dark">Create BOLO</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
