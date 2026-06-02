<?php
$user = current_user();
$pageTitle = $pageTitle ?? APP_SHORT;
$bodyClass = $bodyClass ?? '';
?>
<!DOCTYPE html>
<html lang="id" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?= e(APP_NAME) ?> - <?= e(APP_TAGLINE) ?>">
    <title><?= e($pageTitle) ?> | <?= e(APP_SHORT) ?></title>
    <link rel="icon" type="image/svg+xml" href="/assets/img/favicon.svg">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="/assets/css/style.css" rel="stylesheet">
</head>
<body class="<?= e($bodyClass) ?>">
<div id="smcd-loader" class="smcd-loader">
    <div class="smcd-loader-inner text-center">
        <img src="/assets/img/logo.svg" alt="<?= e(APP_SHORT) ?>" class="smcd-loader-logo mb-3">
        <h4 class="text-primary fw-bold mb-1"><?= e(APP_SHORT) ?></h4>
        <p class="text-muted small mb-3"><?= e(APP_TAGLINE) ?></p>
        <div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>
    </div>
</div>

<?php if (!empty($showNavbar)): ?>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark border-bottom border-primary sticky-top">
    <div class="container-fluid">
        <a class="navbar-brand d-flex align-items-center gap-2" href="/dashboard.php">
            <img src="/assets/img/logo.svg" alt="SMCD" height="32">
            <span>
                <strong class="text-primary"><?= e(APP_SHORT) ?></strong>
                <small class="d-block text-muted lh-1" style="font-size:.65rem"><?= e(APP_TAGLINE) ?></small>
            </span>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="mainNav">
            <ul class="navbar-nav me-auto">
                <?php if (can_access_dispatch($user['role'])): ?>
                <li class="nav-item"><a class="nav-link" href="/dashboard.php"><i class="bi bi-grid-1x2-fill"></i> Dispatch</a></li>
                <?php endif; ?>
                <?php if (is_officer_role($user['role'])): ?>
                <li class="nav-item"><a class="nav-link" href="/officer.php"><i class="bi bi-shield-fill"></i> Unit Panel</a></li>
                <?php endif; ?>
                <li class="nav-item"><a class="nav-link" href="/radio-codes.php"><i class="bi bi-broadcast"></i> Radio Codes</a></li>
            </ul>
            <ul class="navbar-nav">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                        <i class="bi bi-person-badge"></i> <?= e($user['display_name']) ?>
                        <span class="badge bg-primary ms-1"><?= e(role_label($user['role'])) ?></span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><span class="dropdown-item-text text-muted small">@<?= e($user['username']) ?></span></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="/logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>
<?php endif; ?>

<main class="<?= !empty($showNavbar) ? 'py-3' : '' ?>">
