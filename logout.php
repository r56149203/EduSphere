<?php
require_once 'includes/config.php';

// Destroy all session data
$_SESSION = [];
session_destroy();

// Redirect to login page
header("Location: " . BASE_URL . "/login.php");
exit();
?>