-- Create database
CREATE DATABASE IF NOT EXISTS edusphere CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE edusphere;

-- Table: users
CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    role ENUM('student', 'teacher', 'admin') DEFAULT 'student',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Table: classes
CREATE TABLE classes (
    class_id INT AUTO_INCREMENT PRIMARY KEY,
    class_name VARCHAR(50) NOT NULL
);

-- Table: subjects
CREATE TABLE subjects (
    subject_id INT AUTO_INCREMENT PRIMARY KEY,
    subject_name VARCHAR(100) NOT NULL,
    class_id INT NOT NULL,
    FOREIGN KEY (class_id) REFERENCES classes(class_id) ON DELETE CASCADE
);

-- Table: chapters
CREATE TABLE chapters (
    chapter_id INT AUTO_INCREMENT PRIMARY KEY,
    chapter_name VARCHAR(255) NOT NULL,
    subject_id INT NOT NULL,
    FOREIGN KEY (subject_id) REFERENCES subjects(subject_id) ON DELETE CASCADE
);

-- Table: resources
CREATE TABLE resources (
    resource_id INT AUTO_INCREMENT PRIMARY KEY,
    type ENUM('video', 'pdf', 'mindmap', 'quiz', 'link') NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    class_id INT NOT NULL,
    subject_id INT NOT NULL,
    chapter_id INT NOT NULL,
    content_url VARCHAR(500),
    file_path VARCHAR(500),
    content_data JSON,
    uploaded_by INT NOT NULL,
    upload_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (class_id) REFERENCES classes(class_id),
    FOREIGN KEY (subject_id) REFERENCES subjects(subject_id),
    FOREIGN KEY (chapter_id) REFERENCES chapters(chapter_id),
    FOREIGN KEY (uploaded_by) REFERENCES users(user_id)
);

-- Insert sample data
INSERT INTO classes (class_name) VALUES 
('Grade 9'), 
('Grade 10'), 
('Grade 11'), 
('Grade 12');

INSERT INTO subjects (subject_name, class_id) VALUES 
('Mathematics', 1),
('Science', 1),
('Mathematics', 2),
('Physics', 2),
('Chemistry', 2);

INSERT INTO chapters (chapter_name, subject_id) VALUES 
('Algebra', 1),
('Geometry', 1),
('Force and Motion', 2),
('Calculus', 3),
('Electromagnetism', 4);

-- Create admin user (password: admin123)
INSERT INTO users (email, password_hash, full_name, role) VALUES 
('admin@edusphere.com', '$2y$10$r3B7oG5UQ2fJkK6Y8vL7E.FfJcZ9WxY1A2B3C4D5E6F7G8H9I0J1K', 'Admin User', 'admin');