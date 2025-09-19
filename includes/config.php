<?php
// Error reporting - enable for debugging, disable for production
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Session configuration
session_set_cookie_params([
    'lifetime' => 86400,
    'path' => '/',
    'domain' => '',
    'secure' => isset($_SERVER['HTTPS']),
    'httponly' => true,
    'samesite' => 'Strict'
]);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Security headers
header('X-Frame-Options: SAMEORIGIN');
header('X-XSS-Protection: 1; mode=block');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: strict-origin-when-cross-origin');

// Database configuration - UPDATE THESE VALUES FOR YOUR ENVIRONMENT
define('DB_HOST', 'localhost');
define('DB_NAME', 'edusphere');
define('DB_USER', 'root');
define('DB_PASS', '');

// Application paths
$base_url = 'http://' . $_SERVER['HTTP_HOST'] . '/edusphere';
define('BASE_URL', $base_url);
define('UPLOAD_PATH', realpath(dirname(__FILE__) . '/../assets/uploads/') . '/');

// Create uploads directory if it doesn't exist
if (!file_exists(UPLOAD_PATH)) {
    mkdir(UPLOAD_PATH, 0755, true);
    mkdir(UPLOAD_PATH . 'pdfs/', 0755, true);
}

// Include other required files
require_once 'database.php';

// Load auth.php if it exists
if (file_exists('auth.php')) {
    require_once 'auth.php';
} else {
    // Define basic auth functions if auth.php is missing
    function isLoggedIn() { return isset($_SESSION['user_id']); }
    function hasRole($role) { return isLoggedIn() && isset($_SESSION['role']) && $_SESSION['role'] === $role; }
}

// Load functions.php if it exists
if (file_exists('functions.php')) {
    require_once 'functions.php';
} else {
    // Define basic functions if functions.php is missing
    function sanitizeInput($input) { 
        return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8'); 
    }
}
?>