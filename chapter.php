<?php
require_once 'includes/config.php';

// Validate and sanitize parameters
if (!isset($_GET['class_id']) || !is_numeric($_GET['class_id']) || 
    !isset($_GET['subject_id']) || !is_numeric($_GET['subject_id']) ||
    !isset($_GET['chapter_id']) || !is_numeric($_GET['chapter_id'])) {
    header("Location: error.php?code=400");
    exit();
}

$class_id = intval($_GET['class_id']);
$subject_id = intval($_GET['subject_id']);
$chapter_id = intval($_GET['chapter_id']);

try {
    $database = new Database();
    $db = $database->getConnection();
    
    // Get class, subject, and chapter names
    $query = "SELECT c.class_name, s.subject_name, ch.chapter_name 
              FROM classes c, subjects s, chapters ch 
              WHERE c.class_id = :class_id 
              AND s.subject_id = :subject_id 
              AND ch.chapter_id = :chapter_id
              AND s.class_id = c.class_id
              AND ch.subject_id = s.subject_id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':class_id', $class_id, PDO::PARAM_INT);
    $stmt->bindParam(':subject_id', $subject_id, PDO::PARAM_INT);
    $stmt->bindParam(':chapter_id', $chapter_id, PDO::PARAM_INT);
    $stmt->execute();
    
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$result) {
        header("Location: error.php?code=404");
        exit();
    }
    
    // Get resources for this chapter
    $query = "SELECT * FROM resources 
              WHERE class_id = :class_id 
              AND subject_id = :subject_id 
              AND chapter_id = :chapter_id 
              ORDER BY type, title";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':class_id', $class_id, PDO::PARAM_INT);
    $stmt->bindParam(':subject_id', $subject_id, PDO::PARAM_INT);
    $stmt->bindParam(':chapter_id', $chapter_id, PDO::PARAM_INT);
    $stmt->execute();
    
    $resources = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Group resources by type for better organization
    $grouped_resources = [];
    foreach ($resources as $resource) {
        $grouped_resources[$resource['type']][] = $resource;
    }
} catch(PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    header("Location: error.php?code=500");
    exit();
}

$page_title = htmlspecialchars($result['chapter_name']) . " - " . htmlspecialchars($result['subject_name']);
include_once 'includes/header.php';
?>

<div class="container mt-5">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php">Home</a></li>
            <li class="breadcrumb-item"><a href="class.php?class_id=<?php echo $class_id; ?>"><?php echo htmlspecialchars($result['class_name']); ?></a></li>
            <li class="breadcrumb-item"><a href="subject.php?class_id=<?php echo $class_id; ?>&subject_id=<?php echo $subject_id; ?>"><?php echo htmlspecialchars($result['subject_name']); ?></a></li>
            <li class="breadcrumb-item active"><?php echo htmlspecialchars($result['chapter_name']); ?></li>
        </ol>
    </nav>
    
    <h2 class="mb-4"><?php echo htmlspecialchars($result['chapter_name']); ?></h2>
    <p class="text-muted">
        Class: <?php echo htmlspecialchars($result['class_name']); ?> | 
        Subject: <?php echo htmlspecialchars($result['subject_name']); ?>
    </p>
    
    <div class="resources-container">
        <?php if (count($resources) > 0): ?>
            <?php foreach ($grouped_resources as $type => $items): ?>
                <div class="resource-section mb-5">
                    <h3 class="mb-3 border-bottom pb-2"><?php echo ucfirst($type) . 's'; ?></h3>
                    
                    <div class="row">
                        <?php foreach ($items as $resource): ?>
                            <div class="col-md-6 mb-4">
                                <div class="card h-100 shadow-sm">
                                    <div class="card-body">
                                        <h5 class="card-title"><?php echo htmlspecialchars($resource['title']); ?></h5>
                                        <?php if (!empty($resource['description'])): ?>
                                            <p class="card-text"><?php echo htmlspecialchars($resource['description']); ?></p>
                                        <?php endif; ?>
                                        
                                        <?php if ($resource['type'] === 'video'): ?>
                                            <div class="embed-responsive embed-responsive-16by9 mt-3">
                                                <iframe class="embed-responsive-item" src="<?php echo htmlspecialchars($resource['content_url']); ?>" allowfullscreen></iframe>
                                            </div>
                                        <?php elseif ($resource['type'] === 'pdf'): ?>
                                            <a href="pdf_viewer.php?file=<?php echo urlencode($resource['file_path']); ?>" class="btn btn-primary mt-3" target="_blank">
                                                View PDF
                                            </a>
                                        <?php elseif ($resource['type'] === 'mindmap'): ?>
                                            <div id="mindmap-<?php echo $resource['resource_id']; ?>" class="mt-3" style="height: 300px;"></div>
                                            <script>
                                                // jsMind initialization for mindmap
                                                document.addEventListener('DOMContentLoaded', function() {
                                                    var mind = <?php echo $resource['content_data'] ?: '{}'; ?>;
                                                    var options = {
                                                        container: 'mindmap-<?php echo $resource['resource_id']; ?>',
                                                        theme: 'primary',
                                                        editable: false
                                                    };
                                                    var jm = new jsMind(options);
                                                    jm.show(mind);
                                                });
                                            </script>
                                        <?php elseif ($resource['type'] === 'quiz'): ?>
                                            <a href="<?php echo htmlspecialchars($resource['content_url']); ?>" class="btn btn-primary mt-3" target="_blank">
                                                Take Quiz
                                            </a>
                                        <?php elseif ($resource['type'] === 'link'): ?>
                                            <a href="<?php echo htmlspecialchars($resource['content_url']); ?>" class="btn btn-primary mt-3" target="_blank">
                                                Visit Link
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="alert alert-info">No resources available for this chapter yet.</div>
        <?php endif; ?>
    </div>
</div>

<?php include_once 'includes/footer.php'; ?>