<?php
require_once '../includes/config.php';
requireAdmin();

$page_title = "Manage Content - EduSphere";
include_once '../includes/header.php';

try {
    $database = new Database();
    $db = $database->getConnection();
    
    // Get all resources with pagination
    $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
    $items_per_page = 10;
    $offset = ($page - 1) * $items_per_page;
    
    // Get total count
    $query = "SELECT COUNT(*) as total FROM resources";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $total_items = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    $total_pages = ceil($total_items / $items_per_page);
    
    // Get resources
    $query = "SELECT r.*, c.class_name, s.subject_name, ch.chapter_name, u.full_name 
              FROM resources r 
              JOIN classes c ON r.class_id = c.class_id 
              JOIN subjects s ON r.subject_id = s.subject_id 
              JOIN chapters ch ON r.chapter_id = ch.chapter_id 
              JOIN users u ON r.uploaded_by = u.user_id 
              ORDER BY r.upload_date DESC 
              LIMIT :limit OFFSET :offset";
    $stmt = $db->prepare($query);
    $stmt->bindValue(':limit', $items_per_page, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $resources = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Handle resource deletion
    if (isset($_GET['delete'])) {
        $resource_id = intval($_GET['delete']);
        
        // First get resource details for logging
        $query = "SELECT * FROM resources WHERE resource_id = :resource_id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':resource_id', $resource_id, PDO::PARAM_INT);
        $stmt->execute();
        $resource = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($resource) {
            // Delete file if it's a PDF
            if ($resource['type'] === 'pdf' && !empty($resource['file_path'])) {
                $file_path = UPLOAD_PATH . $resource['file_path'];
                if (file_exists($file_path)) {
                    unlink($file_path);
                }
            }
            
            // Delete from database
            $query = "DELETE FROM resources WHERE resource_id = :resource_id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':resource_id', $resource_id, PDO::PARAM_INT);
            
            if ($stmt->execute()) {
                $success = "Resource deleted successfully!";
                logActivity($_SESSION['user_id'], 'delete_resource', "Deleted resource: " . $resource['title']);
                
                // Refresh page to update list
                header("Location: manage_content.php?page=" . $page);
                exit();
            } else {
                $error = "Failed to delete resource.";
            }
        } else {
            $error = "Resource not found.";
        }
    }
    
} catch(PDOException $e) {
    error_log("Manage content error: " . $e->getMessage());
    $error = "Failed to load content.";
}
?>

<div class="container-fluid mt-4">
    <h1 class="h2 mb-4">Manage Content</h1>
    
    <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <?php if (!empty($success)): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
    <?php endif; ?>
    
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">All Resources</h5>
            <a href="add_resource.php" class="btn btn-primary btn-sm">Add New Resource</a>
        </div>
        <div class="card-body">
            <?php if (count($resources) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Type</th>
                                <th>Class</th>
                                <th>Subject</th>
                                <th>Chapter</th>
                                <th>Uploaded By</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($resources as $resource): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($resource['title']); ?></td>
                                    <td><span class="badge bg-secondary"><?php echo ucfirst($resource['type']); ?></span></td>
                                    <td><?php echo htmlspecialchars($resource['class_name']); ?></td>
                                    <td><?php echo htmlspecialchars($resource['subject_name']); ?></td>
                                    <td><?php echo htmlspecialchars($resource['chapter_name']); ?></td>
                                    <td><?php echo htmlspecialchars($resource['full_name']); ?></td>
                                    <td><?php echo date('M j, Y', strtotime($resource['upload_date'])); ?></td>
                                    <td>
                                        <a href="../chapter.php?class_id=<?php echo $resource['class_id']; ?>&subject_id=<?php echo $resource['subject_id']; ?>&chapter_id=<?php echo $resource['chapter_id']; ?>" class="btn btn-sm btn-outline-primary" target="_blank">View</a>
                                        <a href="edit_resource.php?id=<?php echo $resource['resource_id']; ?>" class="btn btn-sm btn-outline-secondary">Edit</a>
                                        <a href="manage_content.php?delete=<?php echo $resource['resource_id']; ?>&page=<?php echo $page; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure you want to delete this resource?')">Delete</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                    <nav aria-label="Resource pagination">
                        <ul class="pagination justify-content-center">
                            <?php if ($page > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="manage_content.php?page=<?php echo $page - 1; ?>">Previous</a>
                                </li>
                            <?php else: ?>
                                <li class="page-item disabled">
                                    <span class="page-link">Previous</span>
                                </li>
                            <?php endif; ?>
                            
                            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                    <a class="page-link" href="manage_content.php?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                </li>
                            <?php endfor; ?>
                            
                            <?php if ($page < $total_pages): ?>
                                <li class="page-item">
                                    <a class="page-link" href="manage_content.php?page=<?php echo $page + 1; ?>">Next</a>
                                </li>
                            <?php else: ?>
                                <li class="page-item disabled">
                                    <span class="page-link">Next</span>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                <?php endif; ?>
                
            <?php else: ?>
                <p class="text-muted">No resources found. <a href="add_resource.php">Add your first resource</a></p>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include_once '../includes/footer.php'; ?>