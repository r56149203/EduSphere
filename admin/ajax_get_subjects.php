<?php
require_once '../includes/config.php';

header('Content-Type: application/json');

if (!isset($_GET['class_id']) || !is_numeric($_GET['class_id'])) {
    echo json_encode([]);
    exit();
}

$class_id = intval($_GET['class_id']);

try {
    $database = new Database();
    $db = $database->getConnection();
    
    $query = "SELECT subject_id, subject_name FROM subjects WHERE class_id = :class_id ORDER BY subject_name";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':class_id', $class_id, PDO::PARAM_INT);
    $stmt->execute();
    
    $subjects = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($subjects);
} catch(PDOException $e) {
    error_log("AJAX subjects error: " . $e->getMessage());
    echo json_encode([]);
}
?>