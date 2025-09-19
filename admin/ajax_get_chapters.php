<?php
require_once '../includes/config.php';

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