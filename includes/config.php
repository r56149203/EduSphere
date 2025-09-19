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

// Application paths - dynamic detection
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'];
$script_path = dirname($_SERVER['SCRIPT_NAME']);

// Remove 'admin' from path if we're in admin directory
if (strpos($script_path, '/admin') !== false) {
    $script_path = str_replace('/admin', '', $script_path);
}

// Ensure the path doesn't end with a slash
$script_path = rtrim($script_path, '/');

// Set base URL
$base_url = $protocol . '://' . $host . $script_path;
define('BASE_URL', $base_url);

// Set upload path (absolute server path)
define('UPLOAD_PATH', realpath(dirname(__FILE__) . '/../assets/uploads/') . '/');

// Create uploads directory if it doesn't exist
if (!file_exists(UPLOAD_PATH)) {
    mkdir(UPLOAD_PATH, 0755, true);
    mkdir(UPLOAD_PATH . 'pdfs/', 0755, true);
}

// Include other required files
require_once 'database.php';

// Check if auth.php exists and include it
if (file_exists(__DIR__ . '/auth.php')) {
    require_once 'auth.php';
} else {
    // Fallback function definitions if auth.php is missing
    function isLoggedIn() { return isset($_SESSION['user_id']); }
    function hasRole($role) { return isLoggedIn() && isset($_SESSION['role']) && $_SESSION['role'] === $role; }
    function requireLogin() {
        if (!isLoggedIn()) {
            header("Location: " . BASE_URL . "/login.php");
            exit();
        }
    }
    function requireAdmin() {
        requireLogin();
        if (!hasRole('admin')) {
            header("Location: " . BASE_URL . "/error.php?code=403");
            exit();
        }
    }
    function redirectByRole() {
        if (isLoggedIn()) {
            if (hasRole('admin')) {
                header("Location: " . BASE_URL . "/admin/dashboard.php");
            } else {
                header("Location: " . BASE_URL . "/index.php");
            }
            exit();
        }
    }
    function sanitizeInput($input) {
        return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }
    function isValidEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL);
    }
    function isStrongPassword($password) {
        return preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/', $password);
    }
}

// Check if functions.php exists and include it
if (file_exists(__DIR__ . '/functions.php')) {
    require_once 'functions.php';
} else {
    // Fallback functions if functions.php is missing
    function generateCsrfToken() {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
    
    function validateCsrfToken($token) {
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }
    
    function formatFileSize($bytes) {
        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        } elseif ($bytes > 1) {
            return $bytes . ' bytes';
        } elseif ($bytes == 1) {
            return '1 byte';
        } else {
            return '0 bytes';
        }
    }
    
    function getFileExtension($filename) {
        return strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    }
}
?>