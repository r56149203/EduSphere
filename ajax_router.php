<?php
require_once 'includes/config.php';

header('Content-Type: application/json');

// Check if it's an AJAX request
if (empty($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest') {
    http_response_code(403);
    echo json_encode(['error' => 'Direct access not allowed']);
    exit();
}

// Get the action parameter
$action = isset($_GET['action']) ? $_GET['action'] : '';

try {
    $database = new Database();
    $db = $database->getConnection();
    
    switch ($action) {
        case 'get_subjects':
            if (!isset($_GET['class_id']) || !is_numeric($_GET['class_id'])) {
                echo json_encode([]);
                exit();
            }
            
            $class_id = intval($_GET['class_id']);
            $query = "SELECT subject_id, subject_name FROM subjects WHERE class_id = :class_id ORDER BY subject_name";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':class_id', $class_id, PDO::PARAM_INT);
            $stmt->execute();
            
            $subjects = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode($subjects);
            break;
            
        case 'get_chapters':
            if (!isset($_GET['subject_id']) || !is_numeric($_GET['subject_id'])) {
                echo json_encode([]);
                exit();
            }
            
            $subject_id = intval($_GET['subject_id']);
            $query = "SELECT chapter_id, chapter_name FROM chapters WHERE subject_id = :subject_id ORDER BY chapter_name";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':subject_id', $subject_id, PDO::PARAM_INT);
            $stmt->execute();
            
            $chapters = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode($chapters);
            break;
            
        default:
            echo json_encode(['error' => 'Invalid action']);
            break;
    }
} catch(PDOException $e) {
    error_log("AJAX router error: " . $e->getMessage());
    echo json_encode(['error' => 'Server error']);
}
?>