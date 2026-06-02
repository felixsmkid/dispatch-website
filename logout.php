<?php
require_once __DIR__ . '/includes/init.php';

if (!empty($_SESSION['user_id'])) {
    $userId = (int) $_SESSION['user_id'];
    $stmt = $pdo->prepare('UPDATE units SET is_online = 0 WHERE user_id = ?');
    $stmt->execute([$userId]);
}

logout_user();
redirect('/login.php');
