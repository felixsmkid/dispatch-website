<?php

declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', '1');
    ini_set('session.use_strict_mode', '1');
    ini_set('session.cookie_samesite', 'Strict');
    session_start();
}

$configFile = __DIR__ . '/../config/database.php';
if (!file_exists($configFile)) {
    die('Konfigurasi database belum ada. Salin config/database.php.example ke config/database.php');
}

require_once $configFile;
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/csrf.php';
require_once __DIR__ . '/auth.php';

try {
    $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
} catch (PDOException $e) {
    die('Koneksi database gagal. Periksa config/database.php dan pastikan database sudah diimport.');
}

define('APP_NAME', 'Satu Mimpi Central Dispatch');
define('APP_SHORT', 'SMCD');
define('APP_TAGLINE', 'Emergency Communications Center');
define('BASE_PATH', rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/\\'));
define('BASE_URL', (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . (BASE_PATH === '' || BASE_PATH === '/' ? '' : BASE_PATH));
