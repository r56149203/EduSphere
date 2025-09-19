<?php
require_once 'includes/config.php';

// Validate and sanitize class_id
if (!isset($_GET['class_id']) || !is_numeric($_GET['class_id'])) {
    header("Location: error.php?code=400");
    exit();
}

$class_id = intval($_GET['class_id']);

try {
    $database = new Database();
    $db = $database->getConnection();
    
    // Get class name
    $query = "SELECT class_name FROM classes WHERE class_id = :class_id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':class_id', $class_id, PDO::PARAM_INT);
    $stmt->execute();
    
    $class = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$class) {
        header("Location: error.php?code=404");
        exit();
    }
    
    // Get subjects for this class
    $query = "SELECT * FROM subjects WHERE class_id = :class_id ORDER BY subject_name";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':class_id', $class_id, PDO::PARAM_INT);
    $stmt->execute();
    
    $subjects = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    header("Location: error.php?code=500");
    exit();
}

$page_title = "Subjects - " . htmlspecialchars($class['class_name']);
include_once 'includes/header.php';
?>

<div class="container mt-5">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php">Home</a></li>
            <li class="breadcrumb-item active"><?php echo htmlspecialchars($class['class_name']); ?></li>
        </ol>
    </nav>
    
    <h2 class="mb-4">Subjects for <?php echo htmlspecialchars($class['class_name']); ?></h2>
    
    <div class="row">
        <?php if (count($subjects) > 0): ?>
            <?php foreach ($subjects as $subject): ?>
                <div class="col-md-4 mb-4">
                    <div class="card h-100 shadow-sm">
                        <div class="card-body text-center">
                            <h5 class="card-title"><?php echo htmlspecialchars($subject['subject_name']); ?></h5>
                            <a href="subject.php?class_id=<?php echo $class_id; ?>&subject_id=<?php echo $subject['subject_id']; ?>" class="btn btn-primary mt-3">View Chapters</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12">
                <div class="alert alert-info">No subjects available for this class.</div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include_once 'includes/footer.php'; ?>