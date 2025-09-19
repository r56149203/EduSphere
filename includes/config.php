<?php
// Error reporting
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../errors.log');

// Session configuration
session_set_cookie_params([
    'lifetime' => 86400,
    'path' => '/',
    'domain' => '',
    'secure' => isset($_SERVER['HTTPS']),
    'httponly' => true,
    'samesite' => 'Strict'
]);
session_start();

// Security headers
header('X-Frame-Options: SAMEORIGIN');
header('X-XSS-Protection: 1; mode=block');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: strict-origin-when-cross-origin');

// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'edusphere');
define('DB_USER', 'root');
define('DB_PASS', '');

// Application paths
define('BASE_URL', 'http://' . $_SERVER['HTTP_HOST'] . '/edusphere');
define('UPLOAD_PATH', __DIR__ . '/../assets/uploads/');

// Include other required files
require_once 'database.php';
require_once 'auth.php';
require_once 'functions.php';
?>