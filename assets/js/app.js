(function () {
    'use strict';

    const loader = document.getElementById('smcd-loader');
    if (loader) {
        window.addEventListener('load', function () {
            setTimeout(function () {
                loader.classList.add('hidden');
            }, 600);
        });
    }

    window.SMCD = window.SMCD || {};

    SMCD.getCsrf = function () {
        const el = document.getElementById('csrf-token');
        return el ? el.value : '';
    };

    SMCD.apiPost = async function (url, data) {
        const body = new URLSearchParams(data);
        body.append('csrf_token', SMCD.getCsrf());
        const res = await fetch(url, {
            method: 'POST',
            headers: { 'X-CSRF-Token': SMCD.getCsrf() },
            body: body,
        });
        return res.json();
    };

    SMCD.apiGet = async function (url) {
        const res = await fetch(url);
        return res.json();
    };

    let lastPanicId = null;
    window.panicDismissed = false;
    const alarm = document.getElementById('panic-alarm');
    const overlay = document.getElementById('panic-overlay');

    SMCD.showPanic = function (panic) {
        if (!panic || !overlay) return;
        if (panic.id != null && panic.id !== lastPanicId) {
            window.panicDismissed = false;
        }
        if (window.panicDismissed && panic.id === lastPanicId) return;

        document.getElementById('panic-officer').textContent = panic.officer_name || '-';
        document.getElementById('panic-callsign').textContent = panic.callsign || '-';
        document.getElementById('panic-department').textContent = panic.department || '-';
        document.getElementById('panic-location').textContent = panic.location || '-';
        document.getElementById('panic-time').textContent = panic.created_at || '-';

        overlay.classList.remove('d-none');
        overlay.setAttribute('aria-hidden', 'false');

        if (alarm) {
            alarm.play().catch(function () {});
        }
        lastPanicId = panic.id;
    };

    SMCD.hidePanic = function () {
        if (overlay) {
            overlay.classList.add('d-none');
            overlay.setAttribute('aria-hidden', 'true');
        }
        if (alarm) {
            alarm.pause();
            alarm.currentTime = 0;
        }
        window.panicDismissed = true;
    };

    const dismissBtn = document.getElementById('btn-dismiss-panic');
    if (dismissBtn) {
        dismissBtn.addEventListener('click', async function () {
            const panicId = lastPanicId;
            if (panicId) {
                await SMCD.apiPost('/api/panic.php', { action: 'resolve', panic_id: panicId });
            } else {
                await SMCD.apiPost('/api/panic.php', { action: 'resolve_all' });
            }
            SMCD.hidePanic();
            window.panicDismissed = true;
        });
    }

    SMCD.escapeHtml = function (str) {
        const div = document.createElement('div');
        div.textContent = str == null ? '' : String(str);
        return div.innerHTML;
    };

    SMCD.formatPriority = function (p) {
        const map = { emergency: 'danger', high: 'warning text-dark', medium: 'info text-dark', low: 'secondary' };
        const cls = map[p] || 'secondary';
        return '<span class="badge bg-' + cls + '">' + SMCD.escapeHtml(p) + '</span>';
    };
})();
