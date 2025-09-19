<?php
require_once '../includes/config.php';
requireAdmin();

$page_title = "Admin Dashboard - EduSphere";
include_once '../includes/header.php';

try {
    $database = new Database();
    $db = $database->getConnection();
    
    // Get stats for dashboard
    $queries = [
        'users' => "SELECT COUNT(*) as count FROM users",
        'classes' => "SELECT COUNT(*) as count FROM classes",
        'subjects' => "SELECT COUNT(*) as count FROM subjects",
        'chapters' => "SELECT COUNT(*) as count FROM chapters",
        'resources' => "SELECT COUNT(*) as count FROM resources",
        'teachers' => "SELECT COUNT(*) as count FROM users WHERE role = 'teacher'",
        'students' => "SELECT COUNT(*) as count FROM users WHERE role = 'student'"
    ];
    
    $stats = [];
    foreach ($queries as $key => $query) {
        $stmt = $db->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $stats[$key] = $result['count'];
    }
    
    // Get recent resources
    $query = "SELECT r.*, c.class_name, s.subject_name, ch.chapter_name, u.full_name 
              FROM resources r 
              JOIN classes c ON r.class_id = c.class_id 
              JOIN subjects s ON r.subject_id = s.subject_id 
              JOIN chapters ch ON r.chapter_id = ch.chapter_id 
              JOIN users u ON r.uploaded_by = u.user_id 
              ORDER BY r.upload_date DESC 
              LIMIT 5";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $recent_resources = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get recent users
    $query = "SELECT user_id, full_name, email, role, created_at 
              FROM users 
              ORDER BY created_at DESC 
              LIMIT 5";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $recent_users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch(PDOException $e) {
    error_log("Dashboard error: " . $e->getMessage());
    $error = "Failed to load dashboard data.";
}
?>

<div class="container-fluid mt-4">
    <h1 class="h2 mb-4">Admin Dashboard</h1>
    
    <!-- Quick Stats -->
    <div class="row mb-4">
        <div class="col-md-2 col-6 mb-3">
            <div class="card bg-primary text-white text-center p-3">
                <h3><?php echo $stats['users']; ?></h3>
                <p class="mb-0">Total Users</p>
            </div>
        </div>
        <div class="col-md-2 col-6 mb-3">
            <div class="card bg-success text-white text-center p-3">
                <h3><?php echo $stats['teachers']; ?></h3>
                <p class="mb-0">Teachers</p>
            </div>
        </div>
        <div class="col-md-2 col-6 mb-3">
            <div class="card bg-info text-white text-center p-3">
                <h3><?php echo $stats['students']; ?></h3>
                <p class="mb-0">Students</p>
            </div>
        </div>
        <div class="col-md-2 col-6 mb-3">
            <div class="card bg-warning text-dark text-center p-3">
                <h3><?php echo $stats['classes']; ?></h3>
                <p class="mb-0">Classes</p>
            </div>
        </div>
        <div class="col-md-2 col-6 mb-3">
            <div class="card bg-danger text-white text-center p-3">
                <h3><?php echo $stats['resources']; ?></h3>
                <p class="mb-0">Resources</p>
            </div>
        </div>
    </div>
    
    <div class="row">
        <!-- Quick Actions -->
        <div class="col-md-4 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Quick Actions</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="add_resource.php" class="btn btn-primary mb-2">Add New Resource</a>
                        <a href="manage_users.php" class="btn btn-secondary mb-2">Manage Users</a>
                        <a href="manage_content.php" class="btn btn-info mb-2">Manage Content</a>
                        <a href="../index.php" class="btn btn-outline-primary">View Site</a>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Recent Resources -->
        <div class="col-md-8">
            <div class="row">
                <div class="col-12 mb-4">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0">Recently Added Resources</h5>
                            <a href="manage_content.php" class="btn btn-sm btn-outline-primary">View All</a>
                        </div>
                        <div class="card-body">
                            <?php if (count($recent_resources) > 0): ?>
                                <div class="list-group">
                                    <?php foreach ($recent_resources as $resource): ?>
                                        <div class="list-group-item">
                                            <div class="d-flex w-100 justify-content-between">
                                                <h6 class="mb-1"><?php echo htmlspecialchars($resource['title']); ?></h6>
                                                <small><?php echo date('M j, Y', strtotime($resource['upload_date'])); ?></small>
                                            </div>
                                            <p class="mb-1">
                                                <span class="badge bg-secondary"><?php echo ucfirst($resource['type']); ?></span>
                                                <span class="badge bg-light text-dark"><?php echo htmlspecialchars($resource['class_name']); ?></span>
                                                <span class="badge bg-light text-dark"><?php echo htmlspecialchars($resource['subject_name']); ?></span>
                                            </p>
                                            <small>Uploaded by: <?php echo htmlspecialchars($resource['full_name']); ?></small>
                                            <div class="mt-2">
                                                <a href="edit_resource.php?id=<?php echo $resource['resource_id']; ?>" class="btn btn-sm btn-outline-primary">Edit</a>
                                                <a href="delete_resource.php?id=<?php echo $resource['resource_id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure you want to delete this resource?')">Delete</a>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <p class="text-muted">No resources added yet.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <div class="col-12">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0">Recent Users</h5>
                            <a href="manage_users.php" class="btn btn-sm btn-outline-primary">View All</a>
                        </div>
                        <div class="card-body">
                            <?php if (count($recent_users) > 0): ?>
                                <div class="list-group">
                                    <?php foreach ($recent_users as $user): ?>
                                        <div class="list-group-item">
                                            <div class="d-flex w-100 justify-content-between">
                                                <h6 class="mb-1"><?php echo htmlspecialchars($user['full_name']); ?></h6>
                                                <small><?php echo date('M j, Y', strtotime($user['created_at'])); ?></small>
                                            </div>
                                            <p class="mb-1">
                                                <span class="badge bg-<?php echo $user['role'] === 'admin' ? 'danger' : ($user['role'] === 'teacher' ? 'success' : 'info'); ?>">
                                                    <?php echo ucfirst($user['role']); ?>
                                                </span>
                                                <span class="text-muted"><?php echo htmlspecialchars($user['email']); ?></span>
                                            </p>
                                            <div class="mt-2">
                                                <a href="manage_users.php?edit=<?php echo $user['user_id']; ?>" class="btn btn-sm btn-outline-primary">Edit Role</a>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <p class="text-muted">No users registered yet.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include_once '../includes/footer.php'; ?>