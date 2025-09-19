<?php
require_once '../includes/config.php';
requireAdmin();

$page_title = "Manage Users - EduSphere";
include_once '../includes/header.php';

try {
    $database = new Database();
    $db = $database->getConnection();
    
    // Get all users
    $query = "SELECT * FROM users ORDER BY created_at DESC";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Handle role update
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_role'])) {
        $user_id = intval($_POST['user_id']);
        $new_role = sanitizeInput($_POST['role']);
        
        $query = "UPDATE users SET role = :role WHERE user_id = :user_id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':role', $new_role);
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        
        if ($stmt->execute()) {
            $success = "User role updated successfully!";
            logActivity($_SESSION['user_id'], 'update_user_role', "Updated user $user_id to $new_role");
            
            // Refresh users list
            $query = "SELECT * FROM users ORDER BY created_at DESC";
            $stmt = $db->prepare($query);
            $stmt->execute();
            $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } else {
            $error = "Failed to update user role.";
        }
    }
    
    // Handle user deletion
    if (isset($_GET['delete'])) {
        $user_id = intval($_GET['delete']);
        
        // Prevent admin from deleting themselves
        if ($user_id !== $_SESSION['user_id']) {
            $query = "DELETE FROM users WHERE user_id = :user_id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
            
            if ($stmt->execute()) {
                $success = "User deleted successfully!";
                logActivity($_SESSION['user_id'], 'delete_user', "Deleted user $user_id");
                
                // Refresh users list
                $query = "SELECT * FROM users ORDER BY created_at DESC";
                $stmt = $db->prepare($query);
                $stmt->execute();
                $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
            } else {
                $error = "Failed to delete user.";
            }
        } else {
            $error = "You cannot delete your own account.";
        }
    }
    
} catch(PDOException $e) {
    error_log("Manage users error: " . $e->getMessage());
    $error = "Failed to load users.";
}
?>

<div class="container-fluid mt-4">
    <h1 class="h2 mb-4">Manage Users</h1>
    
    <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <?php if (!empty($success)): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
    <?php endif; ?>
    
    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">All Users</h5>
        </div>
        <div class="card-body">
            <?php if (count($users) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Joined</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $user): ?>
                                <tr>
                                    <td><?php echo $user['user_id']; ?></td>
                                    <td><?php echo htmlspecialchars($user['full_name']); ?></td>
                                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                                    <td>
                                        <form method="POST" class="d-inline">
                                            <input type="hidden" name="user_id" value="<?php echo $user['user_id']; ?>">
                                            <select name="role" class="form-select form-select-sm" onchange="this.form.submit()">
                                                <option value="student" <?php echo $user['role'] === 'student' ? 'selected' : ''; ?>>Student</option>
                                                <option value="teacher" <?php echo $user['role'] === 'teacher' ? 'selected' : ''; ?>>Teacher</option>
                                                <option value="admin" <?php echo $user['role'] === 'admin' ? 'selected' : ''; ?>>Admin</option>
                                            </select>
                                            <input type="hidden" name="update_role" value="1">
                                        </form>
                                    </td>
                                    <td><?php echo date('M j, Y', strtotime($user['created_at'])); ?></td>
                                    <td>
                                        <?php if ($user['user_id'] !== $_SESSION['user_id']): ?>
                                            <a href="manage_users.php?delete=<?php echo $user['user_id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this user?')">Delete</a>
                                        <?php else: ?>
                                            <span class="text-muted">Current user</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p class="text-muted">No users found.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include_once '../includes/footer.php'; ?>