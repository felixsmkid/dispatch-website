<?php
require_once __DIR__ . '/includes/init.php';

if (!empty($_SESSION['user_id'])) {
    $role = $_SESSION['user_role'] ?? '';
    if (can_access_dispatch($role)) {
        redirect('/dashboard.php');
    }
    redirect('/officer.php');
}

redirect('/login.php');
