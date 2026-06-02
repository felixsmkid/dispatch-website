(function () {
    'use strict';

    if (!document.getElementById('btn-panic')) return;

    async function poll() {
        try {
            const data = await SMCD.apiGet('/api/poll.php');
            if (!data.success) return;

            const tbody = document.getElementById('officer-calls-body');
            const calls = data.officer_calls || [];
            if (!calls.length) {
                tbody.innerHTML = '<tr><td colspan="4" class="text-center text-muted py-3">No assigned calls</td></tr>';
            } else {
                tbody.innerHTML = calls.map(function (c) {
                    return '<tr>' +
                        '<td>' + SMCD.escapeHtml(c.call_number) + '</td>' +
                        '<td>' + SMCD.escapeHtml(c.location) + '</td>' +
                        '<td>' + SMCD.formatPriority(c.priority) + '</td>' +
                        '<td><span class="badge bg-' + (c.status === 'active' ? 'success' : 'warning text-dark') + '">' + SMCD.escapeHtml(c.status) + '</span></td>' +
                        '</tr>';
                }).join('');
            }

            if (data.unit_status) {
                const badge = document.getElementById('current-status-badge');
                if (badge) badge.textContent = data.unit_status;
            }

            if (data.active_panic) {
                SMCD.showPanic(data.active_panic);
            }
        } catch (e) {
            console.error(e);
        }
    }

    document.getElementById('btn-update-status').addEventListener('click', async function () {
        const code = document.getElementById('unit-status-select').value;
        const res = await SMCD.apiPost('/api/units.php', { action: 'update_status', status_code: code });
        if (res.success) {
            const badge = document.getElementById('current-status-badge');
            badge.textContent = res.status_code + ' ' + res.status_label;
            badge.className = 'badge bg-primary';
        } else {
            alert(res.message || 'Failed');
        }
    });

    document.getElementById('btn-panic').addEventListener('click', async function () {
        if (!confirm('ACTIVATE PANIC BUTTON? Emergency assistance will be dispatched.')) return;
        const location = document.getElementById('officer-location').value || 'Unknown Location';
        const res = await SMCD.apiPost('/api/panic.php', { action: 'activate', location: location });
        if (res.success) {
            poll();
        } else {
            alert(res.message);
        }
    });

    poll();
    setInterval(poll, 3000);
})();
