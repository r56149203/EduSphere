<?php
/**
 * Utility functions for EduSphere
 */

/**
 * Generate a random token for CSRF protection
 */
function generateCsrfToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Validate CSRF token
 */
function validateCsrfToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Format file size in human readable format
 */
function formatFileSize($bytes) {
    if ($bytes >= 1073741824) {
        return number_format($bytes / 1073741824, 2) . ' GB';
    } elseif ($bytes >= 1048576) {
        return number_format($bytes / 1048576, 2) . ' MB';
    } elseif ($bytes >= 1024) {
        return number_format($bytes / 1024, 2) . ' KB';
    } elseif ($bytes > 1) {
        return $bytes . ' bytes';
    } elseif ($bytes == 1) {
        return '1 byte';
    } else {
        return '0 bytes';
    }
}

/**
 * Get file extension from filename
 */
function getFileExtension($filename) {
    return strtolower(pathinfo($filename, PATHINFO_EXTENSION));
}

/**
 * Check if a file is a valid PDF
 */
function isValidPdf($filePath) {
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $filePath);
    finfo_close($finfo);
    
    return $mimeType === 'application/pdf';
}

/**
 * Upload a file with security checks
 */
function uploadFile($file, $targetDir, $allowedExtensions = ['pdf']) {
    $errors = [];
    
    // Check for upload errors
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $errors[] = "File upload failed with error code: " . $file['error'];
        return [false, $errors];
    }
    
    // Check file size (max 10MB)
    if ($file['size'] > 10 * 1024 * 1024) {
        $errors[] = "File is too large. Maximum size is 10MB.";
    }
    
    // Get file extension
    $fileExtension = getFileExtension($file['name']);
    
    // Validate file extension
    if (!in_array($fileExtension, $allowedExtensions)) {
        $errors[] = "Invalid file type. Only " . implode(', ', $allowedExtensions) . " files are allowed.";
    }
    
    // Generate unique filename
    $uniqueName = uniqid() . '_' . time() . '.' . $fileExtension;
    $targetPath = $targetDir . $uniqueName;
    
    // Move uploaded file
    if (empty($errors) {
        if (move_uploaded_file($file['tmp_name'], $targetPath)) {
            // Additional validation for PDF files
            if ($fileExtension === 'pdf' && !isValidPdf($targetPath)) {
                unlink($targetPath);
                $errors[] = "Uploaded file is not a valid PDF.";
                return [false, $errors];
            }
            
            return [$uniqueName, $errors];
        } else {
            $errors[] = "Failed to move uploaded file.";
        }
    }
    
    return [false, $errors];
}

/**
 * Log activity to a file
 */
function logActivity($userId, $action, $details = '') {
    $logFile = __DIR__ . '/../logs/activity.log';
    $logDir = dirname($logFile);
    
    // Create logs directory if it doesn't exist
    if (!file_exists($logDir)) {
        mkdir($logDir, 0755, true);
    }
    
    $timestamp = date('Y-m-d H:i:s');
    $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
    
    $logMessage = "[$timestamp] [UserID: $userId] [IP: $ipAddress] [Action: $action]";
    if (!empty($details)) {
        $logMessage .= " [Details: $details]";
    }
    $logMessage .= " [UserAgent: $userAgent]\n";
    
    file_put_contents($logFile, $logMessage, FILE_APPEND | LOCK_EX);
}

/**
 * Get pagination parameters
 */
function getPaginationParams($currentPage, $itemsPerPage = 10) {
    $currentPage = max(1, (int)$currentPage);
    $offset = ($currentPage - 1) * $itemsPerPage;
    
    return [
        'current_page' => $currentPage,
        'items_per_page' => $itemsPerPage,
        'offset' => $offset
    ];
}

/**
 * Generate pagination HTML
 */
function generatePagination($totalItems, $currentPage, $itemsPerPage = 10, $urlPattern = '?page=%d') {
    $totalPages = ceil($totalItems / $itemsPerPage);
    
    if ($totalPages <= 1) {
        return '';
    }
    
    $html = '<nav aria-label="Page navigation"><ul class="pagination justify-content-center">';
    
    // Previous button
    if ($currentPage > 1) {
        $html .= '<li class="page-item"><a class="page-link" href="' . sprintf($urlPattern, $currentPage - 1) . '">Previous</a></li>';
    } else {
        $html .= '<li class="page-item disabled"><span class="page-link">Previous</span></li>';
    }
    
    // Page numbers
    $startPage = max(1, $currentPage - 2);
    $endPage = min($totalPages, $startPage + 4);
    
    if ($endPage - $startPage < 4) {
        $startPage = max(1, $endPage - 4);
    }
    
    for ($i = $startPage; $i <= $endPage; $i++) {
        if ($i == $currentPage) {
            $html .= '<li class="page-item active"><span class="page-link">' . $i . '</span></li>';
        } else {
            $html .= '<li class="page-item"><a class="page-link" href="' . sprintf($urlPattern, $i) . '">' . $i . '</a></li>';
        }
    }
    
    // Next button
    if ($currentPage < $totalPages) {
        $html .= '<li class="page-item"><a class="page-link" href="' . sprintf($urlPattern, $currentPage + 1) . '">Next</a></li>';
    } else {
        $html .= '<li class="page-item disabled"><span class="page-link">Next</span></li>';
    }
    
    $html .= '</ul></nav>';
    
    return $html;
}
?>