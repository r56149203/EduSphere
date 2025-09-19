<?php
require_once '../includes/config.php';
requireAdmin();

// Check if resource ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: manage_content.php");
    exit();
}

$resource_id = intval($_GET['id']);

try {
    $database = new Database();
    $db = $database->getConnection();
    
    // First get resource details for logging and file deletion
    $query = "SELECT * FROM resources WHERE resource_id = :resource_id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':resource_id', $resource_id, PDO::PARAM_INT);
    $stmt->execute();
    $resource = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$resource) {
        header("Location: manage_content.php");
        exit();
    }
    
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
        logActivity($_SESSION['user_id'], 'delete_resource', "Deleted resource: " . $resource['title']);
        $_SESSION['success'] = "Resource deleted successfully!";
    } else {
        $_SESSION['error'] = "Failed to delete resource.";
    }
    
} catch(PDOException $e) {
    error_log("Delete resource error: " . $e->getMessage());
    $_SESSION['error'] = "A system error occurred. Please try again.";
}

// Redirect back to manage content page
header("Location: manage_content.php");
exit();
?>