-- Employee Skill Tracker & Project Assignment System
-- Database Schema
-- Created: 2026-03-09

-- Create Database
CREATE DATABASE IF NOT EXISTS skill_tracker CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE skill_tracker;

-- 1. Users Table (Admin, HR, Candidates)
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'hr', 'candidate') NOT NULL DEFAULT 'candidate',
    phone VARCHAR(20),
    address TEXT,
    profile_image VARCHAR(255),
    resume_path VARCHAR(255),
    experience_years INT DEFAULT 0,
    current_position VARCHAR(100),
    department VARCHAR(100),
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. Skills Master Table
CREATE TABLE skills (
    id INT AUTO_INCREMENT PRIMARY KEY,
    skill_name VARCHAR(100) NOT NULL UNIQUE,
    category VARCHAR(50),
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. Candidate Skills Junction Table
CREATE TABLE candidate_skills (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    skill_id INT NOT NULL,
    proficiency_level ENUM('beginner', 'intermediate', 'advanced', 'expert') DEFAULT 'beginner',
    years_experience DECIMAL(4,1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (skill_id) REFERENCES skills(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_skill (user_id, skill_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4. Assessments Table
CREATE TABLE assessments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    skill_id INT,
    duration_minutes INT NOT NULL DEFAULT 30,
    passing_score INT NOT NULL DEFAULT 60,
    total_questions INT NOT NULL DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (skill_id) REFERENCES skills(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 5. Questions Table
CREATE TABLE questions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    assessment_id INT NOT NULL,
    question_text TEXT NOT NULL,
    option_a VARCHAR(255) NOT NULL,
    option_b VARCHAR(255) NOT NULL,
    option_c VARCHAR(255) NOT NULL,
    option_d VARCHAR(255) NOT NULL,
    correct_answer ENUM('a', 'b', 'c', 'd') NOT NULL,
    marks INT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (assessment_id) REFERENCES assessments(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 6. Assessment Results Table
CREATE TABLE results (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    assessment_id INT NOT NULL,
    score INT NOT NULL,
    total_marks INT NOT NULL,
    percentage DECIMAL(5,2) NOT NULL,
    status ENUM('passed', 'failed') NOT NULL,
    time_taken_minutes INT,
    answers JSON,
    completed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (assessment_id) REFERENCES assessments(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_assessment (user_id, assessment_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 7. Projects Table
CREATE TABLE projects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    project_name VARCHAR(200) NOT NULL,
    description TEXT,
    total_positions INT NOT NULL DEFAULT 1,
    filled_positions INT NOT NULL DEFAULT 0,
    status ENUM('open', 'in_progress', 'completed', 'closed') DEFAULT 'open',
    start_date DATE,
    end_date DATE,
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 8. Project Required Skills Junction Table
CREATE TABLE project_skills (
    id INT AUTO_INCREMENT PRIMARY KEY,
    project_id INT NOT NULL,
    skill_id INT NOT NULL,
    is_mandatory TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
    FOREIGN KEY (skill_id) REFERENCES skills(id) ON DELETE CASCADE,
    UNIQUE KEY unique_project_skill (project_id, skill_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 9. Project Assignments Table
CREATE TABLE project_assignments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    project_id INT NOT NULL,
    user_id INT NOT NULL,
    skill_match_percentage DECIMAL(5,2) DEFAULT 0,
    assessment_score INT DEFAULT 0,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    assigned_by INT,
    assigned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    notes TEXT,
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (assigned_by) REFERENCES users(id) ON DELETE SET NULL,
    UNIQUE KEY unique_project_user (project_id, user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 10. Activity Log Table
CREATE TABLE activity_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    action VARCHAR(100) NOT NULL,
    details TEXT,
    ip_address VARCHAR(45),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert Default Admin User (password: admin123)
INSERT INTO users (full_name, email, password, role, phone, is_active) VALUES 
('System Administrator', 'admin@company.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', '1234567890', 1);

-- Insert Default HR User (password: hr123)
INSERT INTO users (full_name, email, password, role, phone, is_active) VALUES 
('HR Manager', 'hr@company.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'hr', '1234567891', 1);

-- Insert Sample Skills
INSERT INTO skills (skill_name, category, description) VALUES
('HTML', 'Frontend', 'HyperText Markup Language for web structure'),
('CSS', 'Frontend', 'Cascading Style Sheets for web styling'),
('JavaScript', 'Frontend', 'Programming language for web interactivity'),
('PHP', 'Backend', 'Server-side scripting language'),
('MySQL', 'Database', 'Relational database management system'),
('React', 'Frontend', 'JavaScript library for building user interfaces'),
('Node.js', 'Backend', 'JavaScript runtime for server-side development'),
('Python', 'Backend', 'General-purpose programming language'),
('Java', 'Backend', 'Object-oriented programming language'),
('C#', 'Backend', 'Modern object-oriented programming language'),
('Angular', 'Frontend', 'Platform for building mobile and desktop web applications'),
('Vue.js', 'Frontend', 'Progressive JavaScript framework'),
('Laravel', 'Backend', 'PHP web application framework'),
('Docker', 'DevOps', 'Platform for developing and running applications'),
('AWS', 'Cloud', 'Amazon Web Services cloud platform'),
('Git', 'DevOps', 'Version control system'),
('Linux', 'System', 'Open-source operating system'),
('MongoDB', 'Database', 'NoSQL document database'),
('Redis', 'Database', 'In-memory data structure store'),
('GraphQL', 'API', 'Query language for APIs');

-- Insert Sample Assessment
INSERT INTO assessments (title, description, skill_id, duration_minutes, passing_score, total_questions, is_active, created_by) VALUES
('Web Development Fundamentals', 'Basic assessment for HTML, CSS, and JavaScript', NULL, 30, 60, 5, 1, 1),
('PHP & MySQL Assessment', 'Backend development assessment', NULL, 45, 70, 5, 1, 1);

-- Insert Sample Questions for Assessment 1
INSERT INTO questions (assessment_id, question_text, option_a, option_b, option_c, option_d, correct_answer, marks) VALUES
(1, 'What does HTML stand for?', 'Hyper Text Markup Language', 'High Tech Modern Language', 'Hyper Transfer Markup Language', 'Home Tool Markup Language', 'a', 1),
(1, 'Which CSS property is used to change text color?', 'text-style', 'font-color', 'color', 'text-color', 'c', 1),
(1, 'What is the correct syntax for referring to an external script called "script.js"?', '<script href="script.js">', '<script name="script.js">', '<script src="script.js">', '<script file="script.js">', 'c', 1),
(1, 'Which HTML tag is used to define an internal style sheet?', '<css>', '<script>', '<style>', '<link>', 'c', 1),
(1, 'What is the correct JavaScript syntax to change the content of an HTML element with id="demo"?', 'document.getElement("demo").innerHTML = "Hello"', '#demo.innerHTML = "Hello"', 'document.getElementById("demo").innerHTML = "Hello"', 'document.getElementByName("demo").innerHTML = "Hello"', 'c', 1);

-- Insert Sample Questions for Assessment 2
INSERT INTO questions (assessment_id, question_text, option_a, option_b, option_c, option_d, correct_answer, marks) VALUES
(2, 'What does PHP stand for?', 'Personal Home Page', 'PHP: Hypertext Preprocessor', 'Private Home Page', 'Personal Hypertext Processor', 'b', 1),
(2, 'Which MySQL statement is used to extract data from a database?', 'GET', 'OPEN', 'EXTRACT', 'SELECT', 'd', 1),
(2, 'In PHP, which function is used to connect to a MySQL database?', 'mysql_open()', 'mysql_connect()', 'mysqli_connect()', 'db_connect()', 'c', 1),
(2, 'Which SQL keyword is used to sort the result-set?', 'SORT BY', 'ORDER BY', 'ARRANGE BY', 'GROUP BY', 'b', 1),
(2, 'What is the default port number for MySQL?', '3306', '8080', '3000', '5432', 'a', 1);

-- Insert Sample Projects
INSERT INTO projects (project_name, description, total_positions, filled_positions, status, start_date, end_date, created_by) VALUES
('AI Website Development', 'Developing an AI-powered company website with modern features', 3, 0, 'open', '2026-04-01', '2026-06-30', 2),
('E-Commerce Platform', 'Building a full-stack e-commerce solution', 4, 0, 'open', '2026-05-01', '2026-08-31', 2),
('Mobile App Backend', 'API development for mobile application', 2, 0, 'open', '2026-04-15', '2026-07-15', 2);

-- Insert Project Required Skills
INSERT INTO project_skills (project_id, skill_id, is_mandatory) VALUES
(1, 1, 1), -- HTML
(1, 2, 1), -- CSS
(1, 3, 1), -- JavaScript
(1, 4, 1), -- PHP
(2, 1, 1), -- HTML
(2, 2, 1), -- CSS
(2, 3, 1), -- JavaScript
(2, 6, 0), -- React
(2, 4, 1), -- PHP
(2, 5, 1), -- MySQL
(3, 4, 1), -- PHP
(3, 5, 1), -- MySQL
(3, 7, 0), -- Node.js
(3, 18, 0); -- MongoDB

-- Create indexes for better performance
CREATE INDEX idx_users_role ON users(role);
CREATE INDEX idx_users_email ON users(email);
CREATE INDEX idx_candidate_skills_user ON candidate_skills(user_id);
CREATE INDEX idx_candidate_skills_skill ON candidate_skills(skill_id);
CREATE INDEX idx_results_user ON results(user_id);
CREATE INDEX idx_results_assessment ON results(assessment_id);
CREATE INDEX idx_project_assignments_project ON project_assignments(project_id);
CREATE INDEX idx_project_assignments_user ON project_assignments(user_id);
CREATE INDEX idx_questions_assessment ON questions(assessment_id);

-- Create view for skill match analysis
CREATE VIEW skill_match_view AS
SELECT 
    p.id AS project_id,
    p.project_name,
    u.id AS user_id,
    u.full_name,
    COUNT(DISTINCT ps.skill_id) AS total_required_skills,
    COUNT(DISTINCT cs.skill_id) AS matching_skills,
    ROUND((COUNT(DISTINCT cs.skill_id) / COUNT(DISTINCT ps.skill_id)) * 100, 2) AS match_percentage
FROM projects p
CROSS JOIN users u
LEFT JOIN project_skills ps ON p.id = ps.project_id
LEFT JOIN candidate_skills cs ON ps.skill_id = cs.skill_id AND cs.user_id = u.id
WHERE u.role = 'candidate' AND u.is_active = 1
GROUP BY p.id, p.project_name, u.id, u.full_name;
