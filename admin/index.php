<?php
require_once '../includes/config.php';

// Redirect to admin dashboard
header("Location: " . BASE_URL . "/admin/dashboard.php");
exit();
?>