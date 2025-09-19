<?php
require_once 'includes/config.php';

$error_codes = [
    400 => ['title' => 'Bad Request', 'message' => 'The request could not be understood by the server.'],
    403 => ['title' => 'Forbidden', 'message' => 'You don\'t have permission to access this resource.'],
    404 => ['title' => 'Page Not Found', 'message' => 'The page you are looking for does not exist.'],
    500 => ['title' => 'Internal Server Error', 'message' => 'The server encountered an unexpected condition.'],
];

$error_code = isset($_GET['code']) && isset($error_codes[$_GET['code']]) ? intval($_GET['code']) : 404;
$error = $error_codes[$error_code];

http_response_code($error_code);
$page_title = "Error $error_code - " . $error['title'];
include_once 'includes/header.php';
?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8 text-center">
            <h1 class="display-1 text-primary"><?php echo $error_code; ?></h1>
            <h2 class="mb-4"><?php echo $error['title']; ?></h2>
            <p class="lead mb-4"><?php echo $error['message']; ?></p>
            <a href="index.php" class="btn btn-primary btn-lg">Go Home</a>
        </div>
    </div>
</div>

<?php include_once 'includes/footer.php'; ?>