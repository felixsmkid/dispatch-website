</main>

<?php if (!empty($showNavbar)): ?>
<footer class="footer mt-auto py-3 bg-dark border-top border-secondary">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col-md-6 text-center text-md-start">
                <img src="/assets/img/logo.svg" alt="SMCD" height="24" class="me-2">
                <strong class="text-primary"><?= e(APP_NAME) ?></strong>
                <span class="text-muted ms-2">| <?= e(APP_TAGLINE) ?></span>
            </div>
            <div class="col-md-6 text-center text-md-end text-muted small mt-2 mt-md-0">
                &copy; <?= date('Y') ?> <?= e(APP_SHORT) ?> &mdash; FiveM Roleplay CAD System
            </div>
        </div>
    </div>
</footer>
<?php endif; ?>

<div id="panic-overlay" class="panic-overlay d-none" aria-hidden="true">
    <div class="panic-overlay-content text-center">
        <i class="bi bi-exclamation-octagon-fill display-1 text-white mb-3"></i>
        <h1 class="display-4 fw-bold text-white mb-2">OFFICER PANIC</h1>
        <p class="lead text-white-50 mb-4">Emergency — Immediate Assistance Required</p>
        <div class="panic-details mx-auto">
            <p class="mb-1"><strong>Officer:</strong> <span id="panic-officer">-</span></p>
            <p class="mb-1"><strong>Callsign:</strong> <span id="panic-callsign">-</span></p>
            <p class="mb-1"><strong>Department:</strong> <span id="panic-department">-</span></p>
            <p class="mb-1"><strong>Location:</strong> <span id="panic-location">-</span></p>
            <p class="mb-0"><strong>Time:</strong> <span id="panic-time">-</span></p>
        </div>
        <?php if (!empty($canDismissPanic)): ?>
        <button type="button" class="btn btn-light btn-lg mt-4" id="btn-dismiss-panic">
            <i class="bi bi-check-circle"></i> Acknowledge &amp; Dismiss
        </button>
        <?php endif; ?>
    </div>
</div>

<audio id="panic-alarm" loop preload="auto">
    <source src="/assets/sounds/alarm.wav" type="audio/wav">
</audio>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="/assets/js/app.js"></script>
<?php if (!empty($extraScripts)): ?>
<?php foreach ($extraScripts as $script): ?>
<script src="<?= e($script) ?>"></script>
<?php endforeach; ?>
<?php endif; ?>
</body>
</html>
