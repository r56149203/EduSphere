<?php
require_once 'includes/config.php';

// Validate and sanitize parameters
if (!isset($_GET['class_id']) || !is_numeric($_GET['class_id']) || 
    !isset($_GET['subject_id']) || !is_numeric($_GET['subject_id'])) {
    header("Location: error.php?code=400");
    exit();
}

$class_id = intval($_GET['class_id']);
$subject_id = intval($_GET['subject_id']);

try {
    $database = new Database();
    $db = $database->getConnection();
    
    // Get class and subject names
    $query = "SELECT c.class_name, s.subject_name 
              FROM classes c, subjects s 
              WHERE c.class_id = :class_id 
              AND s.subject_id = :subject_id 
              AND s.class_id = c.class_id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':class_id', $class_id, PDO::PARAM_INT);
    $stmt->bindParam(':subject_id', $subject_id, PDO::PARAM_INT);
    $stmt->execute();
    
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$result) {
        header("Location: error.php?code=404");
        exit();
    }
    
    // Get chapters for this subject
    $query = "SELECT * FROM chapters WHERE subject_id = :subject_id ORDER BY chapter_name";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':subject_id', $subject_id, PDO::PARAM_INT);
    $stmt->execute();
    
    $chapters = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    header("Location: error.php?code=500");
    exit();
}

$page_title = "Chapters - " . htmlspecialchars($result['subject_name']);
include_once 'includes/header.php';
?>

<div class="container mt-5">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php">Home</a></li>
            <li class="breadcrumb-item"><a href="class.php?class_id=<?php echo $class_id; ?>"><?php echo htmlspecialchars($result['class_name']); ?></a></li>
            <li class="breadcrumb-item active"><?php echo htmlspecialchars($result['subject_name']); ?></li>
        </ol>
    </nav>
    
    <h2 class="mb-4">Chapters for <?php echo htmlspecialchars($result['subject_name']); ?></h2>
    <p class="text-muted">Class: <?php echo htmlspecialchars($result['class_name']); ?></p>
    
    <div class="list-group">
        <?php if (count($chapters) > 0): ?>
            <?php foreach ($chapters as $chapter): ?>
                <a href="chapter.php?class_id=<?php echo $class_id; ?>&subject_id=<?php echo $subject_id; ?>&chapter_id=<?php echo $chapter['chapter_id']; ?>" class="list-group-item list-group-item-action">
                    <div class="d-flex w-100 justify-content-between">
                        <h5 class="mb-1"><?php echo htmlspecialchars($chapter['chapter_name']); ?></h5>
                        <small>Click to view resources</small>
                    </div>
                </a>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="alert alert-info">No chapters available for this subject.</div>
        <?php endif; ?>
    </div>
</div>

<?php include_once 'includes/footer.php'; ?>