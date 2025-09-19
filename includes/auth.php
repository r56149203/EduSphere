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

/**
 * Check if a user exists with the given email
 */
function userExists($email) {
    try {
        $database = new Database();
        $db = $database->getConnection();
        
        $query = "SELECT user_id FROM users WHERE email = :email";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        
        return $stmt->rowCount() > 0;
    } catch(PDOException $e) {
        error_log("User exists check error: " . $e->getMessage());
        return false;
    }
}

/**
 * Register a new user
 */
function registerUser($full_name, $email, $password, $role = 'student') {
    try {
        $database = new Database();
        $db = $database->getConnection();
        
        // Hash password
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        
        // Insert new user
        $query = "INSERT INTO users (full_name, email, password_hash, role) VALUES (:full_name, :email, :password_hash, :role)";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':full_name', $full_name);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':password_hash', $password_hash);
        $stmt->bindParam(':role', $role);
        
        return $stmt->execute();
    } catch(PDOException $e) {
        error_log("Registration error: " . $e->getMessage());
        return false;
    }
}

/**
 * Authenticate user login
 */
function authenticateUser($email, $password) {
    try {
        $database = new Database();
        $db = $database->getConnection();
        
        $query = "SELECT * FROM users WHERE email = :email LIMIT 1";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (password_verify($password, $user['password_hash'])) {
                return $user;
            }
        }
        
        return false;
    } catch(PDOException $e) {
        error_log("Authentication error: " . $e->getMessage());
        return false;
    }
}

/**
 * Get user by ID
 */
function getUserById($user_id) {
    try {
        $database = new Database();
        $db = $database->getConnection();
        
        $query = "SELECT * FROM users WHERE user_id = :user_id LIMIT 1";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        error_log("Get user error: " . $e->getMessage());
        return false;
    }
}

/**
 * Update user role
 */
function updateUserRole($user_id, $role) {
    try {
        $database = new Database();
        $db = $database->getConnection();
        
        $query = "UPDATE users SET role = :role WHERE user_id = :user_id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':role', $role);
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        
        return $stmt->execute();
    } catch(PDOException $e) {
        error_log("Update user role error: " . $e->getMessage());
        return false;
    }
}

/**
 * Get all users
 */
function getAllUsers() {
    try {
        $database = new Database();
        $db = $database->getConnection();
        
        $query = "SELECT * FROM users ORDER BY full_name";
        $stmt = $db->prepare($query);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        error_log("Get all users error: " . $e->getMessage());
        return [];
    }
}
?>