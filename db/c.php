<?php
try {
    $pdo = new PDO('mysql:host=localhost;dbname=edusphere', 'root', '');
    echo "Database connection successful!";
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
?>