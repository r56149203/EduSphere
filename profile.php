<?php
require_once 'includes/config.php';
requireLogin();

$page_title = "My Profile - EduSphere";
include_once 'includes/header.php';

try {
    $database = new Database();
    $db = $database->getConnection();
    
    // Get user details
    $query = "SELECT * FROM users WHERE user_id = :user_id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Handle profile update
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $full_name = sanitizeInput($_POST['full_name']);
        $email = sanitizeInput($_POST['email']);
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];
        
        // Validate email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = "Please enter a valid email address.";
        } elseif ($new_password && $new_password !== $confirm_password) {
            $error = "New passwords do not match.";
        } elseif ($new_password && !preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/', $new_password)) {
            $error = "New password must be at least 8 characters long and contain at least one uppercase letter, one lowercase letter, and one number.";
        } else {
            // Check if email already exists (excluding current user)
            $query = "SELECT user_id FROM users WHERE email = :email AND user_id != :user_id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
            $stmt->execute();
            
            if ($stmt->rowCount() > 0) {
                $error = "Email address is already registered.";
            } else {
                // Verify current password if changing password
                if ($new_password) {
                    if (!password_verify($current_password, $user['password_hash'])) {
                        $error = "Current password is incorrect.";
                    } else {
                        $password_hash = password_hash($new_password, PASSWORD_DEFAULT);
                    }
                }
                
                if (empty($error)) {
                    // Update user
                    if ($new_password) {
                        $query = "UPDATE users SET full_name = :full_name, email = :email, password_hash = :password_hash WHERE user_id = :user_id";
                    } else {
                        $query = "UPDATE users SET full_name = :full_name, email = :email WHERE user_id = :user_id";
                    }
                    
                    $stmt = $db->prepare($query);
                    $stmt->bindParam(':full_name', $full_name);
                    $stmt->bindParam(':email', $email);
                    if ($new_password) {
                        $stmt->bindParam(':password_hash', $password_hash);
                    }
                    $stmt->bindParam(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
                    
                    if ($stmt->execute()) {
                        $_SESSION['full_name'] = $full_name;
                        $_SESSION['email'] = $email;
                        $success = "Profile updated successfully!";
                        logActivity($_SESSION['user_id'], 'update_profile', "Updated profile information");
                        
                        // Refresh user data
                        $query = "SELECT * FROM users WHERE user_id = :user_id";
                        $stmt = $db->prepare($query);
                        $stmt->bindParam(':user_id', $_SESSION['user_id'], PDO::PARAM_INT);
                        $stmt->execute();
                        $user = $stmt->fetch(PDO::FETCH_ASSOC);
                    } else {
                        $error = "Failed to update profile. Please try again.";
                    }
                }
            }
        }
    }
    
} catch(PDOException $e) {
    error_log("Profile error: " . $e->getMessage());
    $error = "A system error occurred. Please try again.";
}
?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h3 class="card-title mb-0">My Profile</h3>
                </div>
                <div class="card-body">
                    <?php if (!empty($error)): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>
                    
                    <?php if (!empty($success)): ?>
                        <div class="alert alert-success"><?php echo $success; ?></div>
                    <?php endif; ?>
                    
                    <form method="POST" action="">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="full_name" class="form-label">Full Name</label>
                                    <input type="text" class="form-control" id="full_name" name="full_name" value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email Address</label>
                                    <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Role</label>
                            <input type="text" class="form-control" value="<?php echo ucfirst($user['role']); ?>" disabled>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Member Since</label>
                            <input type="text" class="form-control" value="<?php echo date('F j, Y', strtotime($user['created_at'])); ?>" disabled>
                        </div>
                        
                        <hr>
                        
                        <h5 class="mb-3">Change Password</h5>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="current_password" class="form-label">Current Password</label>
                                    <input type="password" class="form-control" id="current_password" name="current_password">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="new_password" class="form-label">New Password</label>
                                    <input type="password" class="form-control" id="new_password" name="new_password">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="confirm_password" class="form-label">Confirm Password</label>
                                    <input type="password" class="form-control" id="confirm_password" name="confirm_password">
                                </div>
                            </div>
                        </div>
                        <div class="form-text">Leave password fields blank to keep current password.</div>
                        
                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary">Update Profile</button>
                            <a href="<?php echo BASE_URL; ?>/index.php" class="btn btn-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include_once 'includes/footer.php'; ?>