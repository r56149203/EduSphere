<?php
require_once 'includes/config.php';
requireAdmin();

$error = '';
$success = '';

// Get classes for dropdown
try {
    $database = new Database();
    $db = $database->getConnection();
    
    $query = "SELECT * FROM classes ORDER BY class_name";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $classes = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    error_log("Resource form error: " . $e->getMessage());
    $error = "Failed to load form data.";
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $type = sanitizeInput($_POST['type']);
    $title = sanitizeInput($_POST['title']);
    $description = sanitizeInput($_POST['description']);
    $class_id = intval($_POST['class_id']);
    $subject_id = intval($_POST['subject_id']);
    $chapter_id = intval($_POST['chapter_id']);
    
    // Validate required fields
    if (empty($title) || empty($class_id) || empty($subject_id) || empty($chapter_id)) {
        $error = "Please fill all required fields.";
    } else {
        try {
            $content_url = '';
            $file_path = '';
            $content_data = '';
            
            // Handle different resource types
            if ($type === 'video' || $type === 'quiz' || $type === 'link') {
                $content_url = sanitizeInput($_POST['content_url']);
                if (empty($content_url)) {
                    $error = "URL is required for this resource type.";
                }
            } elseif ($type === 'pdf') {
                if (isset($_FILES['pdf_file']) && $_FILES['pdf_file']['error'] === UPLOAD_ERR_OK) {
                    list($file_name, $errors) = uploadFile($_FILES['pdf_file'], UPLOAD_PATH . 'pdfs/', ['pdf']);
                    
                    if ($file_name) {
                        $file_path = 'pdfs/' . $file_name;
                    } else {
                        $error = implode('<br>', $errors);
                    }
                } else {
                    $error = "Please select a PDF file to upload.";
                }
            } elseif ($type === 'mindmap') {
                if (isset($_POST['mindmap_data']) && !empty($_POST['mindmap_data'])) {
                    $content_data = $_POST['mindmap_data'];
                } else {
                    $error = "Mindmap data is required.";
                }
            }
            
            // If no errors, save to database
            if (empty($error)) {
                $query = "INSERT INTO resources (type, title, description, class_id, subject_id, chapter_id, content_url, file_path, content_data, uploaded_by) 
                          VALUES (:type, :title, :description, :class_id, :subject_id, :chapter_id, :content_url, :file_path, :content_data, :uploaded_by)";
                
                $stmt = $db->prepare($query);
                $stmt->bindParam(':type', $type);
                $stmt->bindParam(':title', $title);
                $stmt->bindParam(':description', $description);
                $stmt->bindParam(':class_id', $class_id);
                $stmt->bindParam(':subject_id', $subject_id);
                $stmt->bindParam(':chapter_id', $chapter_id);
                $stmt->bindParam(':content_url', $content_url);
                $stmt->bindParam(':file_path', $file_path);
                $stmt->bindParam(':content_data', $content_data);
                $stmt->bindParam(':uploaded_by', $_SESSION['user_id']);
                
                if ($stmt->execute()) {
                    $success = "Resource added successfully!";
                    logActivity($_SESSION['user_id'], 'add_resource', "Added $type: $title");
                    
                    // Clear form if needed
                    if (!isset($_POST['add_another'])) {
                        $title = $description = $content_url = '';
                    }
                } else {
                    $error = "Failed to add resource. Please try again.";
                }
            }
        } catch(PDOException $e) {
            error_log("Resource add error: " . $e->getMessage());
            $error = "A system error occurred. Please try again.";
        }
    }
}

$page_title = "Add Resource - EduSphere";
include_once 'includes/header.php';
?>

<div class="container mt-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="admin_dashboard.php">Dashboard</a></li>
            <li class="breadcrumb-item active">Add Resource</li>
        </ol>
    </nav>
    
    <h2 class="mb-4">Add New Resource</h2>
    
    <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <?php if (!empty($success)): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
    <?php endif; ?>
    
    <form method="POST" action="" enctype="multipart/form-data" id="resourceForm">
        <div class="row">
            <div class="col-md-6">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Resource Details</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="type" class="form-label">Resource Type <span class="text-danger">*</span></label>
                            <select class="form-select" id="type" name="type" required>
                                <option value="">Select Type</option>
                                <option value="video" <?php echo (isset($_POST['type']) && $_POST['type'] === 'video') ? 'selected' : ''; ?>>Video</option>
                                <option value="pdf" <?php echo (isset($_POST['type']) && $_POST['type'] === 'pdf') ? 'selected' : ''; ?>>PDF</option>
                                <option value="mindmap" <?php echo (isset($_POST['type']) && $_POST['type'] === 'mindmap') ? 'selected' : ''; ?>>Mind Map</option>
                                <option value="quiz" <?php echo (isset($_POST['type']) && $_POST['type'] === 'quiz') ? 'selected' : ''; ?>>Quiz</option>
                                <option value="link" <?php echo (isset($_POST['type']) && $_POST['type'] === 'link') ? 'selected' : ''; ?>>Link</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="title" class="form-label">Title <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="title" name="title" value="<?php echo isset($_POST['title']) ? htmlspecialchars($_POST['title']) : ''; ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="3"><?php echo isset($_POST['description']) ? htmlspecialchars($_POST['description']) : ''; ?></textarea>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Classification</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="class_id" class="form-label">Class <span class="text-danger">*</span></label>
                            <select class="form-select" id="class_id" name="class_id" required>
                                <option value="">Select Class</option>
                                <?php foreach ($classes as $class): ?>
                                    <option value="<?php echo $class['class_id']; ?>" <?php echo (isset($_POST['class_id']) && $_POST['class_id'] == $class['class_id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($class['class_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="subject_id" class="form-label">Subject <span class="text-danger">*</span></label>
                            <select class="form-select" id="subject_id" name="subject_id" required disabled>
                                <option value="">Select Subject</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="chapter_id" class="form-label">Chapter <span class="text-danger">*</span></label>
                            <select class="form-select" id="chapter_id" name="chapter_id" required disabled>
                                <option value="">Select Chapter</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Dynamic fields based on resource type -->
        <div class="card mb-4" id="dynamicFields">
            <div class="card-header">
                <h5 class="card-title mb-0">Resource Content</h5>
            </div>
            <div class="card-body">
                <div id="videoFields" class="resource-type-fields" style="display: none;">
                    <div class="mb-3">
                        <label for="content_url" class="form-label">Video URL <span class="text-danger">*</span></label>
                        <input type="url" class="form-control" id="content_url" name="content_url" placeholder="https://www.youtube.com/embed/..." value="<?php echo isset($_POST['content_url']) ? htmlspecialchars($_POST['content_url']) : ''; ?>">
                        <div class="form-text">Enter the embed URL for YouTube, Vimeo, etc.</div>
                    </div>
                </div>
                
                <div id="pdfFields" class="resource-type-fields" style="display: none;">
                    <div class="mb-3">
                        <label for="pdf_file" class="form-label">PDF File <span class="text-danger">*</span></label>
                        <input type="file" class="form-control" id="pdf_file" name="pdf_file" accept=".pdf">
                        <div class="form-text">Maximum file size: 10MB</div>
                    </div>
                </div>
                
                <div id="mindmapFields" class="resource-type-fields" style="display: none;">
                    <div class="mb-3">
                        <label for="mindmap_data" class="form-label">Mindmap JSON Data <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="mindmap_data" name="mindmap_data" rows="6" placeholder='{"meta":{"name":"Example"},"format":"node_tree","data":[]}'><?php echo isset($_POST['mindmap_data']) ? htmlspecialchars($_POST['mindmap_data']) : ''; ?></textarea>
                        <div class="form-text">Paste JSON data from a mindmap tool.</div>
                    </div>
                </div>
                
                <div id="quizFields" class="resource-type-fields" style="display: none;">
                    <div class="mb-3">
                        <label for="content_url" class="form-label">Quiz URL <span class="text-danger">*</span></label>
                        <input type="url" class="form-control" id="content_url" name="content_url" placeholder="https://forms.google.com/..." value="<?php echo isset($_POST['content_url']) ? htmlspecialchars($_POST['content_url']) : ''; ?>">
                        <div class="form-text">Enter the URL for Google Forms, Kahoot, etc.</div>
                    </div>
                </div>
                
                <div id="linkFields" class="resource-type-fields" style="display: none;">
                    <div class="mb-3">
                        <label for="content_url" class="form-label">Link URL <span class="text-danger">*</span></label>
                        <input type="url" class="form-control" id="content_url" name="content_url" placeholder="https://example.com/..." value="<?php echo isset($_POST['content_url']) ? htmlspecialchars($_POST['content_url']) : ''; ?>">
                    </div>
                </div>
            </div>
        </div>
        
        <div class="d-flex gap-2">
            <button type="submit" name="add_another" value="1" class="btn btn-primary">Save and Add Another</button>
            <button type="submit" class="btn btn-success">Save Resource</button>
            <a href="admin_dashboard.php" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const typeSelect = document.getElementById('type');
    const classSelect = document.getElementById('class_id');
    const subjectSelect = document.getElementById('subject_id');
    const chapterSelect = document.getElementById('chapter_id');
    const dynamicFields = document.getElementById('dynamicFields');
    const allTypeFields = document.querySelectorAll('.resource-type-fields');
    
    // Show/hide fields based on resource type
    function toggleResourceFields() {
        const type = typeSelect.value;
        
        // Hide all fields first
        allTypeFields.forEach(field => {
            field.style.display = 'none';
        });
        
        // Show fields for selected type
        if (type) {
            document.getElementById(type + 'Fields').style.display = 'block';
            dynamicFields.style.display = 'block';
        } else {
            dynamicFields.style.display = 'none';
        }
    }
    
    // Load subjects based on class
    function loadSubjects() {
        const classId = classSelect.value;
        
        if (!classId) {
            subjectSelect.innerHTML = '<option value="">Select Subject</option>';
            subjectSelect.disabled = true;
            chapterSelect.innerHTML = '<option value="">Select Chapter</option>';
            chapterSelect.disabled = true;
            return;
        }
        
        fetch('ajax_get_subjects.php?class_id=' + classId)
            .then(response => response.json())
            .then(data => {
                subjectSelect.innerHTML = '<option value="">Select Subject</option>';
                data.forEach(subject => {
                    subjectSelect.innerHTML += `<option value="${subject.subject_id}">${subject.subject_name}</option>`;
                });
                subjectSelect.disabled = false;
            })
            .catch(error => console.error('Error loading subjects:', error));
    }
    
    // Load chapters based on subject
    function loadChapters() {
        const subjectId = subjectSelect.value;
        
        if (!subjectId) {
            chapterSelect.innerHTML = '<option value="">Select Chapter</option>';
            chapterSelect.disabled = true;
            return;
        }
        
        fetch('ajax_get_chapters.php?subject_id=' + subjectId)
            .then(response => response.json())
            .then(data => {
                chapterSelect.innerHTML = '<option value="">Select Chapter</option>';
                data.forEach(chapter => {
                    chapterSelect.innerHTML += `<option value="${chapter.chapter_id}">${chapter.chapter_name}</option>`;
                });
                chapterSelect.disabled = false;
            })
            .catch(error => console.error('Error loading chapters:', error));
    }
    
    // Event listeners
    typeSelect.addEventListener('change', toggleResourceFields);
    classSelect.addEventListener('change', loadSubjects);
    subjectSelect.addEventListener('change', loadChapters);
    
    // Initialize on page load
    toggleResourceFields();
    if (classSelect.value) loadSubjects();
    if (subjectSelect.value) loadChapters();
});
</script>

<?php include_once 'includes/footer.php'; ?>