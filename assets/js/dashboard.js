(function () {
    'use strict';

    if (!document.getElementById('calls-table-body')) return;

    let pollTimer = null;
    let currentCallId = null;
    let currentPursuitId = null;
    let unitsCache = [];

    const priorities = ['low', 'medium', 'high', 'emergency'];
    const statuses = ['pending', 'active', 'closed'];

    function el(id) { return document.getElementById(id); }

    async function poll() {
        try {
            const data = await SMCD.apiGet('/api/poll.php');
            if (!data.success) return;

            renderStats(data.stats);
            renderCalls(data.calls);
            renderPursuits(data.pursuits);
            renderBolos(data.bolos);
            renderPanics(data.panics);
            renderUnits(data.units);
            unitsCache = data.units || [];

            el('last-sync').textContent = new Date().toLocaleTimeString('id-ID');
            el('calls-count').textContent = (data.calls || []).length;

            if (data.active_panic && !document.getElementById('btn-dismiss-panic')) {
                // dispatch has dismiss
            }
            if (data.active_panic) {
                SMCD.showPanic(data.active_panic);
            }

            updateAssignSelect();
        } catch (e) {
            console.error('Poll error', e);
        }
    }

    function renderStats(s) {
        if (!s) return;
        el('stat-active-calls').textContent = s.active_calls;
        el('stat-pending-calls').textContent = s.pending_calls;
        el('stat-pursuits').textContent = s.active_pursuits;
        el('stat-bolos').textContent = s.active_bolos;
        el('stat-units').textContent = s.online_units;
        el('stat-lspd').textContent = s.lspd_units;
        el('stat-bcso').textContent = s.bcso_units;
        el('stat-panic').textContent = s.panic_alerts;
    }

    function renderCalls(calls) {
        const tbody = el('calls-table-body');
        if (!calls || !calls.length) {
            tbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted py-3">No active calls</td></tr>';
            return;
        }
        tbody.innerHTML = calls.map(function (c) {
            return '<tr>' +
                '<td><strong>' + SMCD.escapeHtml(c.call_number) + '</strong></td>' +
                '<td>' + SMCD.escapeHtml(c.location) + '</td>' +
                '<td>' + SMCD.formatPriority(c.priority) + '</td>' +
                '<td><span class="badge bg-' + (c.status === 'active' ? 'success' : 'warning text-dark') + '">' + SMCD.escapeHtml(c.status) + '</span></td>' +
                '<td><button class="btn btn-sm btn-outline-primary btn-action btn-edit-call" data-id="' + c.id + '"><i class="bi bi-pencil"></i></button></td>' +
                '</tr>';
        }).join('');

        document.querySelectorAll('.btn-edit-call').forEach(function (btn) {
            btn.addEventListener('click', function () { openEditCall(parseInt(btn.dataset.id, 10)); });
        });
    }

    function renderPursuits(list) {
        const tbody = el('pursuits-table-body');
        if (!list || !list.length) {
            tbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted py-3">No active pursuits</td></tr>';
            return;
        }
        tbody.innerHTML = list.map(function (p) {
            return '<tr>' +
                '<td>' + SMCD.escapeHtml(p.pursuit_code) + '</td>' +
                '<td>' + SMCD.escapeHtml(p.vehicle_description) + '</td>' +
                '<td>' + SMCD.escapeHtml(p.plate || '-') + '</td>' +
                '<td>' + SMCD.escapeHtml(p.current_location || '-') + '</td>' +
                '<td><button class="btn btn-sm btn-outline-warning btn-action btn-manage-pursuit text-dark" data-id="' + p.id + '"><i class="bi bi-gear"></i></button></td>' +
                '</tr>';
        }).join('');

        document.querySelectorAll('.btn-manage-pursuit').forEach(function (btn) {
            btn.addEventListener('click', function () { openManagePursuit(parseInt(btn.dataset.id, 10)); });
        });
    }

    function renderBolos(list) {
        const tbody = el('bolos-table-body');
        if (!list || !list.length) {
            tbody.innerHTML = '<tr><td colspan="4" class="text-center text-muted py-3">No active BOLO</td></tr>';
            return;
        }
        tbody.innerHTML = list.map(function (b) {
            return '<tr>' +
                '<td>' + SMCD.escapeHtml(b.vehicle) + '</td>' +
                '<td>' + SMCD.escapeHtml(b.plate || '-') + '</td>' +
                '<td>' + SMCD.escapeHtml(b.reason) + '</td>' +
                '<td><button class="btn btn-sm btn-outline-danger btn-action btn-deactivate-bolo" data-id="' + b.id + '"><i class="bi bi-x"></i></button></td>' +
                '</tr>';
        }).join('');

        document.querySelectorAll('.btn-deactivate-bolo').forEach(function (btn) {
            btn.addEventListener('click', async function () {
                await SMCD.apiPost('/api/bolos.php', { action: 'deactivate', bolo_id: btn.dataset.id });
                poll();
            });
        });
    }

    function renderPanics(list) {
        const container = el('panic-list');
        if (!list || !list.length) {
            container.innerHTML = '<p class="text-muted text-center py-3 mb-0">No active panic alerts</p>';
            return;
        }
        container.innerHTML = list.map(function (p) {
            return '<div class="panic-item">' +
                '<strong class="text-danger">' + SMCD.escapeHtml(p.callsign) + '</strong> ' +
                SMCD.escapeHtml(p.officer_name) + ' (' + SMCD.escapeHtml(p.department) + ')<br>' +
                '<small>' + SMCD.escapeHtml(p.location) + ' — ' + SMCD.escapeHtml(p.created_at) + '</small>' +
                '<button class="btn btn-sm btn-outline-light float-end btn-resolve-panic" data-id="' + p.id + '">Resolve</button>' +
                '</div>';
        }).join('');

        document.querySelectorAll('.btn-resolve-panic').forEach(function (btn) {
            btn.addEventListener('click', async function () {
                await SMCD.apiPost('/api/panic.php', { action: 'resolve', panic_id: btn.dataset.id });
                SMCD.hidePanic();
                poll();
            });
        });
    }

    function deptLogo(dept) {
        const file = dept === 'LSPD' ? 'lspd.svg' : 'bcso.svg';
        return '<img src="/assets/img/' + file + '" class="dept-logo-sm me-1" alt="">';
    }

    function renderUnits(units) {
        const tbody = el('units-table-body');
        if (!units || !units.length) {
            tbody.innerHTML = '<tr><td colspan="7" class="text-center text-muted py-3">No units online</td></tr>';
            return;
        }
        const statusOpts = ['10-7', '10-8', '10-23', '10-38', '10-57', '10-95', '10-99'];
        tbody.innerHTML = units.map(function (u) {
            const opts = statusOpts.map(function (s) {
                return '<option value="' + s + '"' + (u.status_code === s ? ' selected' : '') + '>' + s + '</option>';
            }).join('');
            return '<tr>' +
                '<td>' + deptLogo(u.department) + SMCD.escapeHtml(u.department) + '</td>' +
                '<td><strong>' + SMCD.escapeHtml(u.callsign) + '</strong></td>' +
                '<td>' + SMCD.escapeHtml(u.character_name) + '</td>' +
                '<td>' + SMCD.escapeHtml(u.rank_title) + '</td>' +
                '<td><span class="badge bg-primary">' + SMCD.escapeHtml(u.status_code) + ' ' + SMCD.escapeHtml(u.status_label) + '</span></td>' +
                '<td><small>' + SMCD.escapeHtml(u.last_update) + '</small></td>' +
                '<td><select class="form-select form-select-sm dispatch-status-select" data-unit="' + u.id + '" style="width:110px">' + opts + '</select></td>' +
                '</tr>';
        }).join('');

        document.querySelectorAll('.dispatch-status-select').forEach(function (sel) {
            sel.addEventListener('change', async function () {
                await SMCD.apiPost('/api/units.php', {
                    action: 'set_status_dispatch',
                    unit_id: sel.dataset.unit,
                    status_code: sel.value,
                });
                poll();
            });
        });
    }

    function updateAssignSelect() {
        const sel = el('assign-unit-select');
        if (!sel) return;
        const current = sel.value;
        sel.innerHTML = '<option value="">Select unit...</option>' +
            unitsCache.map(function (u) {
                return '<option value="' + u.id + '">' + SMCD.escapeHtml(u.callsign) + ' (' + SMCD.escapeHtml(u.department) + ')</option>';
            }).join('');
        sel.value = current;
    }

    async function openEditCall(id) {
        currentCallId = id;
        const data = await SMCD.apiGet('/api/calls.php?action=get&call_id=' + id);
        if (!data.success) return;
        const c = data.call;
        el('edit-call-id').value = c.id;
        el('edit-call-number').textContent = c.call_number;
        el('edit-caller-name').value = c.caller_name || '';
        el('edit-phone').value = c.phone_number || '';
        el('edit-location').value = c.location;
        el('edit-description').value = c.description;
        el('edit-priority').innerHTML = priorities.map(function (p) {
            return '<option value="' + p + '"' + (c.priority === p ? ' selected' : '') + '>' + p + '</option>';
        }).join('');
        el('edit-status').innerHTML = statuses.filter(function (s) { return s !== 'closed'; }).map(function (s) {
            return '<option value="' + s + '"' + (c.status === s ? ' selected' : '') + '>' + s + '</option>';
        }).join('');

        const list = el('call-assignments-list');
        list.innerHTML = (c.assignments || []).map(function (a) {
            return '<div class="d-flex justify-content-between align-items-center border-bottom border-secondary py-1">' +
                '<span>' + SMCD.escapeHtml(a.callsign) + ' <span class="badge bg-info text-dark">' + SMCD.escapeHtml(a.assignment_type) + '</span></span>' +
                '<button type="button" class="btn btn-sm btn-outline-danger btn-unassign" data-unit="' + a.unit_id + '">Remove</button></div>';
        }).join('') || '<p class="text-muted small">No units assigned</p>';

        document.querySelectorAll('.btn-unassign').forEach(function (btn) {
            btn.addEventListener('click', async function () {
                await SMCD.apiPost('/api/calls.php', { action: 'unassign', call_id: id, unit_id: btn.dataset.unit });
                openEditCall(id);
                poll();
            });
        });

        bootstrap.Modal.getOrCreateInstance(el('modalEditCall')).show();
    }

    el('form-new-call').addEventListener('submit', async function (e) {
        e.preventDefault();
        const fd = new FormData(e.target);
        const payload = { action: 'create' };
        fd.forEach(function (v, k) { payload[k] = v; });
        const res = await SMCD.apiPost('/api/calls.php', payload);
        if (res.success) {
            e.target.reset();
            bootstrap.Modal.getInstance(el('modalNewCall')).hide();
            poll();
        } else alert(res.message);
    });

    el('form-edit-call').addEventListener('submit', async function (e) {
        e.preventDefault();
        const payload = {
            action: 'update',
            call_id: el('edit-call-id').value,
            caller_name: el('edit-caller-name').value,
            phone_number: el('edit-phone').value,
            location: el('edit-location').value,
            description: el('edit-description').value,
            priority: el('edit-priority').value,
            status: el('edit-status').value,
        };
        const res = await SMCD.apiPost('/api/calls.php', payload);
        if (res.success) {
            bootstrap.Modal.getInstance(el('modalEditCall')).hide();
            poll();
        } else alert(res.message);
    });

    el('btn-close-call').addEventListener('click', async function () {
        if (!confirm('Close this call?')) return;
        await SMCD.apiPost('/api/calls.php', { action: 'close', call_id: el('edit-call-id').value });
        bootstrap.Modal.getInstance(el('modalEditCall')).hide();
        poll();
    });

    el('btn-assign-unit').addEventListener('click', async function () {
        const unitId = el('assign-unit-select').value;
        if (!unitId) return;
        await SMCD.apiPost('/api/calls.php', {
            action: 'assign',
            call_id: currentCallId,
            unit_id: unitId,
            assignment_type: el('assign-type-select').value,
        });
        openEditCall(currentCallId);
        poll();
    });

    el('form-new-pursuit').addEventListener('submit', async function (e) {
        e.preventDefault();
        const fd = new FormData(e.target);
        const payload = { action: 'create' };
        fd.forEach(function (v, k) { payload[k] = v; });
        const res = await SMCD.apiPost('/api/pursuits.php', payload);
        if (res.success) {
            e.target.reset();
            bootstrap.Modal.getInstance(el('modalNewPursuit')).hide();
            poll();
        } else alert(res.message);
    });

    el('form-new-bolo').addEventListener('submit', async function (e) {
        e.preventDefault();
        const fd = new FormData(e.target);
        const payload = { action: 'create' };
        fd.forEach(function (v, k) { payload[k] = v; });
        const res = await SMCD.apiPost('/api/bolos.php', payload);
        if (res.success) {
            e.target.reset();
            bootstrap.Modal.getInstance(el('modalNewBolo')).hide();
            poll();
        } else alert(res.message);
    });

    async function openManagePursuit(id) {
        currentPursuitId = id;
        const data = await SMCD.apiGet('/api/pursuits.php?action=get&pursuit_id=' + id);
        if (!data.success) return;
        const p = data.pursuit;
        el('manage-pursuit-code').textContent = p.pursuit_code;

        const unitOptions = unitsCache.map(function (u) {
            return '<option value="' + u.id + '">' + SMCD.escapeHtml(u.callsign) + '</option>';
        }).join('');

        el('manage-pursuit-body').innerHTML =
            '<p><strong>Vehicle:</strong> ' + SMCD.escapeHtml(p.vehicle_description) + '</p>' +
            '<p><strong>Plate:</strong> ' + SMCD.escapeHtml(p.plate || '-') + ' | <strong>Location:</strong> ' + SMCD.escapeHtml(p.current_location || '-') + '</p>' +
            '<div class="mb-2"><label>Update Location</label><input type="text" class="form-control" id="pursuit-location-input" value="' + SMCD.escapeHtml(p.current_location || '') + '"></div>' +
            '<div class="d-flex flex-wrap gap-2 mb-3">' +
            '<button class="btn btn-sm btn-' + (p.pit_authorized == 1 ? 'success' : 'outline-secondary') + ' btn-toggle-pit">PIT Authorized</button>' +
            '<button class="btn btn-sm btn-' + (p.spike_authorized == 1 ? 'success' : 'outline-secondary') + ' btn-toggle-spike">Spike Authorized</button>' +
            '<button class="btn btn-sm btn-' + (p.air_unit_requested == 1 ? 'success' : 'outline-secondary') + ' btn-toggle-air">Air Unit Requested</button>' +
            '</div>' +
            '<div class="row g-2 mb-2"><div class="col"><select class="form-select" id="pursuit-unit-add">' + unitOptions + '</select></div>' +
            '<div class="col-auto"><button class="btn btn-primary btn-add-pursuit-unit">Add Unit</button></div></div>' +
            '<div class="row g-2 mb-2"><div class="col"><select class="form-select" id="pursuit-primary">' + unitOptions + '</select></div>' +
            '<div class="col-auto"><button class="btn btn-outline-primary btn-set-primary">Set Primary</button></div></div>' +
            '<div class="row g-2"><div class="col"><select class="form-select" id="pursuit-secondary">' + unitOptions + '</select></div>' +
            '<div class="col-auto"><button class="btn btn-outline-info btn-set-secondary text-dark">Set Secondary</button></div></div>' +
            '<ul class="list-group list-group-flush mt-3" id="pursuit-units-list"></ul>';

        const list = el('pursuit-units-list');
        list.innerHTML = (p.units || []).map(function (u) {
            return '<li class="list-group-item bg-dark d-flex justify-content-between">' +
                SMCD.escapeHtml(u.callsign) + ' (' + SMCD.escapeHtml(u.department) + ')' +
                '<button class="btn btn-sm btn-outline-danger btn-remove-pursuit-unit" data-unit="' + u.id + '">Remove</button></li>';
        }).join('') || '<li class="list-group-item bg-dark text-muted">No units</li>';

        el('manage-pursuit-body').querySelector('.btn-toggle-pit').addEventListener('click', function () {
            SMCD.apiPost('/api/pursuits.php', { action: 'toggle', pursuit_id: id, field: 'pit_authorized' }).then(function () { openManagePursuit(id); });
        });
        el('manage-pursuit-body').querySelector('.btn-toggle-spike').addEventListener('click', function () {
            SMCD.apiPost('/api/pursuits.php', { action: 'toggle', pursuit_id: id, field: 'spike_authorized' }).then(function () { openManagePursuit(id); });
        });
        el('manage-pursuit-body').querySelector('.btn-toggle-air').addEventListener('click', function () {
            SMCD.apiPost('/api/pursuits.php', { action: 'toggle', pursuit_id: id, field: 'air_unit_requested' }).then(function () { openManagePursuit(id); });
        });
        el('manage-pursuit-body').querySelector('.btn-add-pursuit-unit').addEventListener('click', async function () {
            await SMCD.apiPost('/api/pursuits.php', { action: 'add_unit', pursuit_id: id, unit_id: el('pursuit-unit-add').value });
            openManagePursuit(id);
            poll();
        });
        el('manage-pursuit-body').querySelector('.btn-set-primary').addEventListener('click', async function () {
            await SMCD.apiPost('/api/pursuits.php', { action: 'set_primary', pursuit_id: id, unit_id: el('pursuit-primary').value });
            openManagePursuit(id);
            poll();
        });
        el('manage-pursuit-body').querySelector('.btn-set-secondary').addEventListener('click', async function () {
            await SMCD.apiPost('/api/pursuits.php', { action: 'set_secondary', pursuit_id: id, unit_id: el('pursuit-secondary').value });
            openManagePursuit(id);
            poll();
        });
        document.querySelectorAll('.btn-remove-pursuit-unit').forEach(function (btn) {
            btn.addEventListener('click', async function () {
                await SMCD.apiPost('/api/pursuits.php', { action: 'remove_unit', pursuit_id: id, unit_id: btn.dataset.unit });
                openManagePursuit(id);
                poll();
            });
        });

        bootstrap.Modal.getOrCreateInstance(el('modalManagePursuit')).show();
    }

    el('btn-end-pursuit').addEventListener('click', async function () {
        if (!currentPursuitId || !confirm('End this pursuit?')) return;
        await SMCD.apiPost('/api/pursuits.php', { action: 'end', pursuit_id: currentPursuitId });
        bootstrap.Modal.getInstance(el('modalManagePursuit')).hide();
        poll();
    });

    poll();
    pollTimer = setInterval(poll, 3000);
})();
