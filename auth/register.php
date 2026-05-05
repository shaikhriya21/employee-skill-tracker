<?php
/**
 * Registration Page
 */
require_once '../config/database.php';

// Redirect if already logged in
if (isLoggedIn()) {
    redirect('index.php');
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = sanitize($conn, $_POST['full_name']);
    $email = sanitize($conn, $_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $phone = sanitize($conn, $_POST['phone']);
    $role = sanitize($conn, $_POST['role']);
    
    // Validation
    if (empty($full_name) || empty($email) || empty($password) || empty($confirm_password)) {
        $error = 'Please fill in all required fields';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address';
    } elseif (strlen($password) < 8) {
        $error = 'Password must be at least 8 characters long';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match';
    } else {
        // Check if email already exists
        $check_stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $check_stmt->bind_param("s", $email);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if ($check_result->num_rows > 0) {
            $error = 'Email address already registered';
        } else {
            // Hash password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // Insert user
            $insert_stmt = $conn->prepare("INSERT INTO users (full_name, email, password, phone, role) VALUES (?, ?, ?, ?, ?)");
            $insert_stmt->bind_param("sssss", $full_name, $email, $hashed_password, $phone, $role);
            
            if ($insert_stmt->execute()) {
                $success = 'Registration successful! Please login to continue.';
                logActivity($conn, $insert_stmt->insert_id, 'Registration', 'New user registered');
            } else {
                $error = 'Registration failed. Please try again.';
            }
            
            $insert_stmt->close();
        }
        
        $check_stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - SkillTrack Pro</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="auth-page">
        <div class="auth-container">
            <div class="auth-left">
                <div class="auth-header">
                    <h2>Create Account</h2>
                    <p>Join Employee Skill Tracker to manage your skills and projects</p>
                </div>
                
                <?php if ($error): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle"></i>
                        <?php echo $error; ?>
                    </div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i>
                        <?php echo $success; ?>
                    </div>
                    <script>
                        setTimeout(() => {
                            window.location.href = 'login.php';
                        }, 2000);
                    </script>
                <?php endif; ?>
                
                <form method="POST" action="" data-validate>
                    <div class="form-group">
                        <label class="form-label">Full Name *</label>
                        <div style="position: relative;">
                            <i class="fas fa-user" style="position: absolute; left: 16px; top: 50%; transform: translateY(-50%); color: var(--text-muted);"></i>
                            <input type="text" name="full_name" class="glass-input" style="padding-left: 45px;" placeholder="Enter your full name" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Email Address *</label>
                        <div style="position: relative;">
                            <i class="fas fa-envelope" style="position: absolute; left: 16px; top: 50%; transform: translateY(-50%); color: var(--text-muted);"></i>
                            <input type="email" name="email" class="glass-input" style="padding-left: 45px;" placeholder="Enter your email" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Phone Number</label>
                        <div style="position: relative;">
                            <i class="fas fa-phone" style="position: absolute; left: 16px; top: 50%; transform: translateY(-50%); color: var(--text-muted);"></i>
                            <input type="tel" name="phone" class="glass-input" style="padding-left: 45px;" placeholder="Enter your phone number">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Role *</label>
                        <div style="position: relative;">
                            <i class="fas fa-user-tag" style="position: absolute; left: 16px; top: 50%; transform: translateY(-50%); color: var(--text-muted); z-index: 1;"></i>
                            <select name="role" class="form-select" style="padding-left: 45px;" required>
                                <option value="candidate">Employee</option>
                                <option value="hr">HR / Recruiter</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Password *</label>
                        <div style="position: relative;">
                            <i class="fas fa-lock" style="position: absolute; left: 16px; top: 50%; transform: translateY(-50%); color: var(--text-muted);"></i>
                            <input type="password" name="password" id="password" class="glass-input" style="padding-left: 45px; padding-right: 45px;" placeholder="Create a password (min 8 chars)" required data-password-strength="password-strength-indicator">
                            <i class="fas fa-eye" id="toggle-password" style="position: absolute; right: 16px; top: 50%; transform: translateY(-50%); color: var(--text-muted); cursor: pointer;"></i>
                        </div>
                        
                        <!-- Password Strength Indicator -->
                        <div id="password-strength-indicator" class="password-strength">
                            <div class="password-strength-bar">
                                <div class="password-strength-fill"></div>
                            </div>
                            <div class="password-strength-text">
                                <span class="password-strength-label">Weak</span>
                                <span>Password strength</span>
                            </div>
                            <div class="password-requirements">
                                <ul>
                                    <li>At least 8 characters</li>
                                    <li>One uppercase letter</li>
                                    <li>One lowercase letter</li>
                                    <li>One number</li>
                                    <li>One special character</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Confirm Password *</label>
                        <div style="position: relative;">
                            <i class="fas fa-lock" style="position: absolute; left: 16px; top: 50%; transform: translateY(-50%); color: var(--text-muted);"></i>
                            <input type="password" name="confirm_password" id="confirm_password" class="glass-input" style="padding-left: 45px; padding-right: 45px;" placeholder="Confirm your password" required>
                            <i class="fas fa-eye" id="toggle-confirm" style="position: absolute; right: 16px; top: 50%; transform: translateY(-50%); color: var(--text-muted); cursor: pointer;"></i>
                        </div>
                    </div>
                    
                    <div style="margin-bottom: 24px;">
                        <label style="display: flex; align-items: flex-start; gap: 8px; cursor: pointer; font-size: 14px; color: var(--text-secondary);">
                            <input type="checkbox" name="terms" required style="accent-color: var(--primary-color); margin-top: 3px;">
                            <span>I agree to the <a href="#" style="color: var(--primary-light);">Terms of Service</a> and <a href="#" style="color: var(--primary-light);">Privacy Policy</a></span>
                        </label>
                    </div>
                    
                    <button type="submit" class="btn btn-primary" style="width: 100%;">
                        <i class="fas fa-user-plus"></i>
                        Create Account
                    </button>
                </form>
                
                <div class="auth-footer">
                    Already have an account? <a href="login.php">Sign in</a>
                </div>
            </div>
            
            <div class="auth-right">
                <div class="auth-right-content">
                    <i class="fas fa-user-plus" style="font-size: 80px; margin-bottom: 30px; opacity: 0.9;"></i>
                    <h2>Join Our Community</h2>
                    <p>Create your account and start tracking your skills today</p>
                    
                    <div style="margin-top: 40px; text-align: left; max-width: 300px;">
                        <div style="display: flex; align-items: center; gap: 15px; margin-bottom: 20px;">
                            <i class="fas fa-check-circle" style="font-size: 20px;"></i>
                            <span>Track unlimited skills</span>
                        </div>
                        <div style="display: flex; align-items: center; gap: 15px; margin-bottom: 20px;">
                            <i class="fas fa-check-circle" style="font-size: 20px;"></i>
                            <span>Take skill assessments</span>
                        </div>
                        <div style="display: flex; align-items: center; gap: 15px; margin-bottom: 20px;">
                            <i class="fas fa-check-circle" style="font-size: 20px;"></i>
                            <span>Get matched to projects</span>
                        </div>
                        <div style="display: flex; align-items: center; gap: 15px;">
                            <i class="fas fa-check-circle" style="font-size: 20px;"></i>
                            <span>Build your portfolio</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="../assets/js/main.js"></script>
    <script>
        // Toggle password visibility
        togglePassword('password', 'toggle-password');
        togglePassword('confirm_password', 'toggle-confirm');
    </script>
</body>
</html>
