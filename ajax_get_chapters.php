<?php
// Allow access from both admin and normal paths
$possible_paths = ['../includes/config.php', 'includes/config.php'];
$config_loaded = false;

foreach ($possible_paths as $path) {
    if (file_exists($path)) {
        require_once $path;
        $config_loaded = true;
        break;
    }
}

if (!$config_loaded) {
    header('HTTP/1.1 500 Internal Server Error');
    echo json_encode(['error' => 'Configuration file not found']);
    exit();
}

header('Content-Type: application/json');

if (!isset($_GET['subject_id']) || !is_numeric($_GET['subject_id'])) {
    echo json_encode([]);
    exit();
}

$subject_id = intval($_GET['subject_id']);

try {
    $database = new Database();
    $db = $database->getConnection();
    
    $query = "SELECT chapter_id, chapter_name FROM chapters WHERE subject_id = :subject_id ORDER BY chapter_name";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':subject_id', $subject_id, PDO::PARAM_INT);
    $stmt->execute();
    
    $chapters = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($chapters);
} catch(PDOException $e) {
    error_log("AJAX chapters error: " . $e->getMessage());
    echo json_encode([]);
}
?>