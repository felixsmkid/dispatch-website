<?php
require_once __DIR__ . '/includes/init.php';

if (!empty($_SESSION['user_id'])) {
    redirect('/index.php');
}

$error = '';

if (is_post()) {
  if (!verify_csrf(input('csrf_token'))) {
        $error = 'Sesi tidak valid. Silakan coba lagi.';
    } else {
        $username = input('username');
        $password = input('password');

        if ($username === '' || $password === '') {
            $error = 'Username dan password wajib diisi.';
        } else {
            $user = attempt_login($pdo, $username, $password);
            if ($user) {
                login_user($user);
                if (is_officer_role($user['role'])) {
                    $unit = get_user_unit($pdo, (int) $user['id']);
                    if (!$unit) {
                        redirect('/unit-setup.php');
                    }
                    redirect('/officer.php');
                }
                redirect('/dashboard.php');
            }
            $error = 'Username atau password salah.';
        }
    }
}

$pageTitle = 'Login';
require __DIR__ . '/includes/header.php';
?>
<div class="login-page min-vh-100 d-flex align-items-center justify-content-center">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-5 col-lg-4">
                <div class="card login-card border-primary shadow-lg">
                    <div class="card-body p-4 p-md-5 text-center">
                        <img src="/assets/img/logo.svg" alt="SMCD" class="login-logo mb-3">
                        <h1 class="h3 text-primary fw-bold mb-0"><?= e(APP_SHORT) ?></h1>
                        <p class="text-muted small mb-1"><?= e(APP_NAME) ?></p>
                        <p class="badge bg-primary-subtle text-primary mb-4"><?= e(APP_TAGLINE) ?></p>

                        <?php if ($error): ?>
                        <div class="alert alert-danger py-2"><?= e($error) ?></div>
                        <?php endif; ?>

                        <form method="post" action="/login.php" autocomplete="off">
                            <?= csrf_field() ?>
                            <div class="mb-3 text-start">
                                <label class="form-label"><i class="bi bi-person"></i> Username</label>
                                <input type="text" name="username" class="form-control form-control-lg" required autofocus>
                            </div>
                            <div class="mb-4 text-start">
                                <label class="form-label"><i class="bi bi-lock"></i> Password</label>
                                <input type="password" name="password" class="form-control form-control-lg" required>
                            </div>
                            <button type="submit" class="btn btn-primary btn-lg w-100">
                                <i class="bi bi-box-arrow-in-right"></i> Sign In
                            </button>
                        </form>

                        <p class="text-muted small mt-4 mb-0">
                            <i class="bi bi-headset"></i> 911 Dispatch &bull; Emergency Communications
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
