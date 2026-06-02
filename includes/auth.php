<?php

function require_login(): void
{
    if (empty($_SESSION['user_id'])) {
        redirect('/login.php');
    }
}

function require_role(array $roles): void
{
    require_login();
    if (!in_array($_SESSION['user_role'] ?? '', $roles, true)) {
        http_response_code(403);
        die('Akses ditolak.');
    }
}

function require_cad_access(): void
{
    require_login();
    if (!can_manage_cad($_SESSION['user_role'] ?? '')) {
        redirect('/officer.php');
    }
}

function current_user(): array
{
    return [
        'id' => (int) ($_SESSION['user_id'] ?? 0),
        'username' => $_SESSION['username'] ?? '',
        'role' => $_SESSION['user_role'] ?? '',
        'display_name' => $_SESSION['display_name'] ?? '',
    ];
}

function login_user(array $user): void
{
    session_regenerate_id(true);
    $_SESSION['user_id'] = (int) $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['user_role'] = $user['role'];
    $_SESSION['display_name'] = $user['display_name'] ?? $user['username'];
}

function logout_user(): void
{
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }
    session_destroy();
}

function attempt_login(PDO $pdo, string $username, string $password): ?array
{
    $stmt = $pdo->prepare('SELECT * FROM users WHERE username = ? AND is_active = 1 LIMIT 1');
    $stmt->execute([$username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$user || !password_verify($password, $user['password'])) {
        return null;
    }
    return $user;
}
