// Custom JavaScript for EduSphere
document.addEventListener('DOMContentLoaded', function() {
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // Initialize popovers
    var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
    var popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl);
    });
    
    // Auto-hide alerts after 5 seconds
    var alerts = document.querySelectorAll('.alert:not(.alert-permanent)');
    alerts.forEach(function(alert) {
        setTimeout(function() {
            var bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        }, 5000);
    });
    
    // Form validation
    var forms = document.querySelectorAll('.needs-validation');
    forms.forEach(function(form) {
        form.addEventListener('submit', function(event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        }, false);
    });
    
    // Dynamic resource type fields
    var resourceTypeSelect = document.getElementById('resourceType');
    if (resourceTypeSelect) {
        resourceTypeSelect.addEventListener('change', function() {
            var type = this.value;
            var dynamicFields = document.querySelectorAll('.dynamic-field');
            
            // Hide all fields first
            dynamicFields.forEach(function(field) {
                field.style.display = 'none';
            });
            
            // Show fields for selected type
            var selectedFields = document.querySelectorAll('.field-' + type);
            selectedFields.forEach(function(field) {
                field.style.display = 'block';
            });
        });
    }
    
    // AJAX form submissions
    var ajaxForms = document.querySelectorAll('.ajax-form');
    ajaxForms.forEach(function(form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            var formData = new FormData(this);
            var submitBtn = this.querySelector('button[type="submit"]');
            var originalText = submitBtn.innerHTML;
            
            // Show loading state
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Loading...';
            submitBtn.disabled = true;
            
            fetch(this.action, {
                method: this.method,
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Show success message
                    showAlert('success', data.message);
                    
                    // Reset form if needed
                    if (data.resetForm) {
                        this.reset();
                    }
                    
                    // Redirect if needed
                    if (data.redirect) {
                        setTimeout(function() {
                            window.location.href = data.redirect;
                        }, 1000);
                    }
                } else {
                    // Show error message
                    showAlert('danger', data.message);
                }
            })
            .catch(error => {
                showAlert('danger', 'An error occurred. Please try again.');
            })
            .finally(() => {
                // Restore button state
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            });
        });
    });
    
    // Function to show alerts
    function showAlert(type, message) {
        var alertDiv = document.createElement('div');
        alertDiv.className = 'alert alert-' + type + ' alert-dismissible fade show';
        alertDiv.innerHTML = message + '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
        
        var container = document.querySelector('.container') || document.querySelector('.container-fluid');
        container.insertBefore(alertDiv, container.firstChild);
        
        // Auto-remove after 5 seconds
        setTimeout(function() {
            var bsAlert = new bootstrap.Alert(alertDiv);
            bsAlert.close();
        }, 5000);
    }
    
    // PDF viewer enhancements
    if (document.querySelector('.pdf-viewer')) {
        // Add PDF viewer controls if needed
    }
    
    // Mindmap viewer enhancements
    if (document.querySelector('.mindmap-container')) {
        // Initialize mindmap viewer if needed
    }
});

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
    
    // Try both possible paths for the AJAX endpoint
    const urls = [
        '../ajax_get_subjects.php?class_id=' + classId,
        'ajax_get_subjects.php?class_id=' + classId
    ];
    
    // Try each URL until one works
    const tryFetch = (index) => {
        if (index >= urls.length) {
            console.error('All AJAX URLs failed');
            return;
        }
        
        fetch(urls[index])
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                subjectSelect.innerHTML = '<option value="">Select Subject</option>';
                data.forEach(subject => {
                    subjectSelect.innerHTML += `<option value="${subject.subject_id}">${subject.subject_name}</option>`;
                });
                subjectSelect.disabled = false;
            })
            .catch(error => {
                console.error('Error loading subjects from', urls[index], error);
                tryFetch(index + 1); // Try next URL
            });
    };
    
    tryFetch(0);
}

// Load chapters based on subject
function loadChapters() {
    const subjectId = subjectSelect.value;
    
    if (!subjectId) {
        chapterSelect.innerHTML = '<option value="">Select Chapter</option>';
        chapterSelect.disabled = true;
        return;
    }
    
    // Try both possible paths for the AJAX endpoint
    const urls = [
        '../ajax_get_chapters.php?subject_id=' + subjectId,
        'ajax_get_chapters.php?subject_id=' + subjectId
    ];
    
    // Try each URL until one works
    const tryFetch = (index) => {
        if (index >= urls.length) {
            console.error('All AJAX URLs failed');
            return;
        }
        
        fetch(urls[index])
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                chapterSelect.innerHTML = '<option value="">Select Chapter</option>';
                data.forEach(chapter => {
                    chapterSelect.innerHTML += `<option value="${chapter.chapter_id}">${chapter.chapter_name}</option>`;
                });
                chapterSelect.disabled = false;
            })
            .catch(error => {
                console.error('Error loading chapters from', urls[index], error);
                tryFetch(index + 1); // Try next URL
            });
    };
    
    tryFetch(0);
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
    
    fetch('../ajax_router.php?action=get_subjects&class_id=' + classId)
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
    
    fetch('../ajax_router.php?action=get_chapters&subject_id=' + subjectId)
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