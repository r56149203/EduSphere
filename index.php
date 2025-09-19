<?php
require_once 'includes/config.php';

$page_title = "EduSphere - Educational Content Hub";
include_once 'includes/header.php';

try {
    $database = new Database();
    $db = $database->getConnection();
    
    $query = "SELECT * FROM classes ORDER BY class_name";
    $stmt = $db->prepare($query);
    $stmt->execute();
    
    $classes = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    header("Location: error.php?code=500");
    exit();
}
?>

<div class="container mt-5">
    <div class="jumbotron bg-light p-5 rounded">
        <h1 class="display-4">Welcome to EduSphere</h1>
        <p class="lead">Your structured educational content hub with organized learning materials for all classes and subjects.</p>
        <hr class="my-4">
        <p>Browse our collection of videos, PDFs, mind maps, quizzes, and more organized by class, subject, and chapter.</p>
    </div>

    <h2 class="mb-4">Select a Class</h2>
    <div class="row">
        <?php if (count($classes) > 0): ?>
            <?php foreach ($classes as $class): ?>
                <div class="col-md-3 mb-4">
                    <div class="card h-100 shadow-sm">
                        <div class="card-body text-center">
                            <h5 class="card-title"><?php echo htmlspecialchars($class['class_name']); ?></h5>
                            <a href="class.php?class_id=<?php echo $class['class_id']; ?>" class="btn btn-primary mt-3">View Subjects</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12">
                <div class="alert alert-info">No classes available at the moment.</div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include_once 'includes/footer.php'; ?>