<?php
/**
 * Authentication and authorization functions for EduSphere
 */

/**
 * Check if a user is logged in
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

/**
 * Check if the current user has a specific role
 */
function hasRole($role) {
    return isLoggedIn() && isset($_SESSION['role']) && $_SESSION['role'] === $role;
}

/**
 * Redirect to login if not authenticated
 */
function requireLogin() {
    if (!isLoggedIn()) {
        header("Location: " . BASE_URL . "/login.php");
        exit();
    }
}

/**
 * Redirect to admin dashboard if not admin
 */
function requireAdmin() {
    requireLogin();
    if (!hasRole('admin')) {
        header("Location: " . BASE_URL . "/error.php?code=403");
        exit();
    }
}

/**
 * Redirect to appropriate dashboard based on role
 */
function redirectByRole() {
    if (isLoggedIn()) {
        if (hasRole('admin')) {
            header("Location: " . BASE_URL . "/admin_dashboard.php");
        } else {
            header("Location: " . BASE_URL . "/index.php");
        }
        exit();
    }
}

/**
 * Validate email format
 */
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

/**
 * Validate password strength
 */
function isStrongPassword($password) {
    // At least 8 characters, 1 uppercase, 1 lowercase, 1 number
    return preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/', $password);
}

/**
 * Sanitize user input
 */
function sanitizeInput($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}
?>