<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/init.php';

header('Content-Type: application/json; charset=utf-8');

if (empty($_SESSION['user_id'])) {
    json_response(['success' => false, 'message' => 'Unauthorized'], 401);
}

$user = current_user();

function api_require_csrf(): void
{
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        require_csrf();
    }
}
