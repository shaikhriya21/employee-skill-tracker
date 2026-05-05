<?php
/**
 * Database Configuration File
 * Employee Skill Tracker & Project Assignment System
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Database credentials - Update these according to your XAMPP setup
define('DB_HOST', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '');  // Default XAMPP password is empty
define('DB_NAME', 'skill_tracker');
define('BASE_URL', '/employee-skill-tracker/');

// Create database connection
try {
    $conn = new mysqli(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME);
    
    // Check connection
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
    
    // Set charset to handle special characters
    $conn->set_charset("utf8mb4");
    
} catch (Exception $e) {
    die("Database connection error: " . $e->getMessage());
}

// Helper function to sanitize input
function sanitize($conn, $data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $conn->real_escape_string($data);
}

// Helper function to check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

// Helper function to check user role
function hasRole($role) {
    return isset($_SESSION['role']) && $_SESSION['role'] === $role;
}

// Helper function to redirect with message
function redirect($url, $message = null, $type = 'info') {
    if ($message) {
        $_SESSION['message'] = $message;
        $_SESSION['message_type'] = $type;
    }
    
    // Handle relative URLs
    if (strpos($url, 'http') !== 0 && strpos($url, '/') !== 0) {
        $url = BASE_URL . $url;
    }
    
    header("Location: " . $url);
    exit();
}

// Helper function to display session message
function showMessage() {
    if (isset($_SESSION['message'])) {
        $type = $_SESSION['message_type'] ?? 'info';
        $message = $_SESSION['message'];
        unset($_SESSION['message']);
        unset($_SESSION['message_type']);
        return '<div class="alert alert-' . $type . '"><i class="fas fa-' . ($type === 'success' ? 'check-circle' : ($type === 'danger' ? 'exclamation-circle' : ($type === 'warning' ? 'exclamation-triangle' : 'info-circle'))) . '"></i> ' . $message . '</div>';
    }
    return '';
}

// Helper function to log activity
function logActivity($conn, $user_id, $action, $details = '') {
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
    $stmt = $conn->prepare("INSERT INTO activity_logs (user_id, action, details, ip_address) VALUES (?, ?, ?, ?)");
    if ($stmt) {
        $stmt->bind_param("isss", $user_id, $action, $details, $ip);
        $stmt->execute();
        $stmt->close();
    }
}

// Helper function to generate URL
function url($path) {
    return BASE_URL . ltrim($path, '/');
}

// Helper function to check password strength
function checkPasswordStrength($password) {
    $strength = 0;
    $feedback = [];
    
    // Length check
    if (strlen($password) >= 8) {
        $strength += 25;
    } else {
        $feedback[] = 'At least 8 characters';
    }
    
    // Uppercase check
    if (preg_match('/[A-Z]/', $password)) {
        $strength += 25;
    } else {
        $feedback[] = 'One uppercase letter';
    }
    
    // Lowercase check
    if (preg_match('/[a-z]/', $password)) {
        $strength += 25;
    } else {
        $feedback[] = 'One lowercase letter';
    }
    
    // Number/Special char check
    if (preg_match('/[0-9!@#$%^&*(),.?":{}|<>]/', $password)) {
        $strength += 25;
    } else {
        $feedback[] = 'One number or special character';
    }
    
    // Determine strength label
    if ($strength >= 100) {
        $label = 'Strong';
        $class = 'success';
    } elseif ($strength >= 50) {
        $label = 'Medium';
        $class = 'warning';
    } else {
        $label = 'Weak';
        $class = 'danger';
    }
    
    return [
        'score' => $strength,
        'label' => $label,
        'class' => $class,
        'feedback' => $feedback
    ];
}
?>
