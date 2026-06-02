(function () {
    'use strict';

    const D = window.SMCD_PREVIEW;
    if (!D) return;

    function esc(s) {
        const d = document.createElement('div');
        d.textContent = s == null ? '' : String(s);
        return d.innerHTML;
    }

    function priorityBadge(p) {
        const map = { emergency: 'danger', high: 'warning text-dark', medium: 'info text-dark', low: 'secondary' };
        return '<span class="badge bg-' + (map[p] || 'secondary') + '">' + esc(p) + '</span>';
    }

    function deptImg(dept) {
        return '<img src="assets/img/' + (dept === 'LSPD' ? 'lspd' : 'bcso') + '.svg" class="dept-logo-sm me-1" alt="">';
    }

    window.renderPreviewDashboard = function () {
        const s = D.stats;
        const set = function (id, v) { const el = document.getElementById(id); if (el) el.textContent = v; };
        set('stat-active-calls', s.active_calls);
        set('stat-pending-calls', s.pending_calls);
        set('stat-pursuits', s.active_pursuits);
        set('stat-bolos', s.active_bolos);
        set('stat-units', s.online_units);
        set('stat-lspd', s.lspd_units);
        set('stat-bcso', s.bcso_units);
        set('stat-panic', s.panic_alerts);
        set('last-sync', new Date().toLocaleTimeString('id-ID') + ' (demo)');
        set('calls-count', D.calls.length);

        const callsBody = document.getElementById('calls-table-body');
        if (callsBody) {
            callsBody.innerHTML = D.calls.map(function (c) {
                return '<tr><td><strong>' + esc(c.call_number) + '</strong></td><td>' + esc(c.location) + '</td><td>' + priorityBadge(c.priority) + '</td><td><span class="badge bg-' + (c.status === 'active' ? 'success' : 'warning text-dark') + '">' + esc(c.status) + '</span></td><td><button class="btn btn-sm btn-outline-primary btn-action" disabled><i class="bi bi-pencil"></i></button></td></tr>';
            }).join('');
        }

        const pursBody = document.getElementById('pursuits-table-body');
        if (pursBody) {
            pursBody.innerHTML = D.pursuits.map(function (p) {
                return '<tr><td>' + esc(p.pursuit_code) + '</td><td>' + esc(p.vehicle_description) + '</td><td>' + esc(p.plate) + '</td><td>' + esc(p.current_location) + '</td><td><button class="btn btn-sm btn-outline-warning btn-action text-dark" disabled><i class="bi bi-gear"></i></button></td></tr>';
            }).join('');
        }

        const boloBody = document.getElementById('bolos-table-body');
        if (boloBody) {
            boloBody.innerHTML = D.bolos.map(function (b) {
                return '<tr><td>' + esc(b.vehicle) + '</td><td>' + esc(b.plate) + '</td><td>' + esc(b.reason) + '</td><td><button class="btn btn-sm btn-outline-danger btn-action" disabled><i class="bi bi-x"></i></button></td></tr>';
            }).join('');
        }

        const panicList = document.getElementById('panic-list');
        if (panicList) {
            panicList.innerHTML = D.panics.map(function (p) {
                return '<div class="panic-item"><strong class="text-danger">' + esc(p.callsign) + '</strong> ' + esc(p.officer_name) + ' (' + esc(p.department) + ')<br><small>' + esc(p.location) + ' — ' + esc(p.created_at) + '</small><button class="btn btn-sm btn-outline-light float-end" disabled>Resolve</button></div>';
            }).join('');
        }

        const unitsBody = document.getElementById('units-table-body');
        if (unitsBody) {
            unitsBody.innerHTML = D.units.map(function (u) {
                return '<tr><td>' + deptImg(u.department) + esc(u.department) + '</td><td><strong>' + esc(u.callsign) + '</strong></td><td>' + esc(u.character_name) + '</td><td>' + esc(u.rank_title) + '</td><td><span class="badge bg-primary">' + esc(u.status_code) + ' ' + esc(u.status_label) + '</span></td><td><small>' + esc(u.last_update) + '</small></td><td><select class="form-select form-select-sm" disabled style="width:110px"><option>' + esc(u.status_code) + '</option></select></td></tr>';
            }).join('');
        }
    };

    window.renderPreviewOfficer = function () {
        const tbody = document.getElementById('officer-calls-body');
        if (tbody && D.officer_calls) {
            tbody.innerHTML = D.officer_calls.map(function (c) {
                return '<tr><td>' + esc(c.call_number) + '</td><td>' + esc(c.location) + '</td><td>' + priorityBadge(c.priority) + '</td><td><span class="badge bg-success">' + esc(c.status) + '</span></td></tr>';
            }).join('');
        }
    };

    window.showPreviewPanic = function () {
        const p = D.panics[0];
        if (!p) return;
        document.getElementById('panic-officer').textContent = p.officer_name;
        document.getElementById('panic-callsign').textContent = p.callsign;
        document.getElementById('panic-department').textContent = p.department;
        document.getElementById('panic-location').textContent = p.location;
        document.getElementById('panic-time').textContent = p.created_at;
        document.getElementById('panic-overlay').classList.remove('d-none');
        const alarm = document.getElementById('panic-alarm');
        if (alarm) alarm.play().catch(function () {});
    };
})();
