<?php
require_once 'includes/config.php';

// Validate and sanitize file parameter
if (!isset($_GET['file']) || empty($_GET['file'])) {
    header("Location: error.php?code=400");
    exit();
}

$file_path = UPLOAD_PATH . 'pdfs/' . basename($_GET['file']);

// Check if file exists and is a PDF
if (!file_exists($file_path) || pathinfo($file_path, PATHINFO_EXTENSION) !== 'pdf') {
    header("Location: error.php?code=404");
    exit();
}

$page_title = "PDF Viewer";
include_once 'includes/header.php';
?>

<div class="container-fluid mt-3">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php">Home</a></li>
            <li class="breadcrumb-item active">PDF Viewer</li>
        </ol>
    </nav>
    
    <div class="card">
        <div class="card-header">
            <h5>PDF Viewer</h5>
        </div>
        <div class="card-body p-0">
            <iframe src="<?php echo BASE_URL; ?>/assets/pdfjs/web/viewer.html?file=<?php echo urlencode($file_path); ?>" width="100%" height="600px" frameborder="0"></iframe>
        </div>
    </div>
</div>

<?php include_once 'includes/footer.php'; ?>