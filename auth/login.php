<?php
/**
 * Login Page
 */
require_once '../config/database.php';

// Redirect if already logged in
if (isLoggedIn()) {
    switch ($_SESSION['role']) {
        case 'admin':
            redirect('admin/dashboard.php');
            break;
        case 'hr':
            redirect('hr/dashboard.php');
            break;
        case 'candidate':
            redirect('candidate/dashboard.php');
            break;
    }
}

$error = '';
$role = isset($_GET['role']) ? sanitize($conn, $_GET['role']) : 'candidate';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize($conn, $_POST['email']);
    $password = $_POST['password'];
    $role = sanitize($conn, $_POST['role']);
    
    if (empty($email) || empty($password)) {
        $error = 'Please enter both email and password';
    } else {
        $stmt = $conn->prepare("SELECT id, full_name, email, password, role, is_active FROM users WHERE email = ? AND role = ?");
        $stmt->bind_param("ss", $email, $role);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            
            if ($user['is_active'] == 0) {
                $error = 'Your account has been deactivated. Please contact admin.';
            } elseif (password_verify($password, $user['password'])) {
                // Set session variables
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['full_name'] = $user['full_name'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['role'] = $user['role'];
                
                // Log activity
                logActivity($conn, $user['id'], 'Login', 'User logged in successfully');
                
                // Redirect based on role
                switch ($user['role']) {
                    case 'admin':
                        redirect('admin/dashboard.php');
                        break;
                    case 'hr':
                        redirect('hr/dashboard.php');
                        break;
                    case 'candidate':
                        redirect('candidate/dashboard.php');
                        break;
                }
            } else {
                $error = 'Invalid password';
            }
        } else {
            $error = 'Invalid email or role';
        }
        
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - SkillTrack Pro</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="auth-page">
        <div class="auth-container">
            <div class="auth-left">
                <div class="auth-header">
                    <h2>Welcome Back</h2>
                    <p>Sign in to your account to continue</p>
                </div>
                
                <?php if ($error): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle"></i>
                        <?php echo $error; ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="" data-validate>
                    <div class="auth-role-tabs">
                        <a href="?role=admin" class="auth-role-tab <?php echo $role === 'admin' ? 'active' : ''; ?>">
                            <i class="fas fa-user-shield"></i> Admin
                        </a>
                        <a href="?role=hr" class="auth-role-tab <?php echo $role === 'hr' ? 'active' : ''; ?>">
                            <i class="fas fa-user-tie"></i> HR
                        </a>
                        <a href="?role=candidate" class="auth-role-tab <?php echo $role === 'candidate' ? 'active' : ''; ?>">
                            <i class="fas fa-user"></i> Employee
                        </a>
                    </div>
                    
                    <input type="hidden" name="role" value="<?php echo htmlspecialchars($role); ?>">
                    
                    <div class="form-group">
                        <label class="form-label">Email Address</label>
                        <div style="position: relative;">
                            <i class="fas fa-envelope" style="position: absolute; left: 16px; top: 50%; transform: translateY(-50%); color: var(--text-muted);"></i>
                            <input type="email" name="email" class="glass-input" style="padding-left: 45px;" placeholder="Enter your email" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Password</label>
                        <div style="position: relative;">
                            <i class="fas fa-lock" style="position: absolute; left: 16px; top: 50%; transform: translateY(-50%); color: var(--text-muted);"></i>
                            <input type="password" name="password" id="password" class="glass-input" style="padding-left: 45px; padding-right: 45px;" placeholder="Enter your password" required>
                            <i class="fas fa-eye" id="toggle-password" style="position: absolute; right: 16px; top: 50%; transform: translateY(-50%); color: var(--text-muted); cursor: pointer;"></i>
                        </div>
                    </div>
                    
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
                        <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; font-size: 14px; color: var(--text-secondary);">
                            <input type="checkbox" name="remember" style="accent-color: var(--primary-color);">
                            Remember me
                        </label>
                        <a href="#" style="font-size: 14px; color: var(--primary-light); text-decoration: none;">Forgot password?</a>
                    </div>
                    
                    <button type="submit" class="btn btn-primary" style="width: 100%;">
                        <i class="fas fa-sign-in-alt"></i>
                        Sign In
                    </button>
                </form>
                
                <div class="auth-footer">
                    Don't have an account? <a href="register.php">Create one</a>
                </div>
            </div>
            
            <div class="auth-right">
                <div class="auth-right-content">
                    <i class="fas fa-layer-group" style="font-size: 80px; margin-bottom: 30px; opacity: 0.9;"></i>
                    <h2>Employee Skill Tracker</h2>
                    <p>Empowering teams with intelligent skill tracking and project assignment</p>
                    
                    <div style="margin-top: 40px; display: flex; gap: 30px; justify-content: center;">
                        <div style="text-align: center;">
                            <div style="font-size: 28px; font-weight: 700;">10K+</div>
                            <div style="font-size: 14px; opacity: 0.8;">Users</div>
                        </div>
                        <div style="text-align: center;">
                            <div style="font-size: 28px; font-weight: 700;">500+</div>
                            <div style="font-size: 14px; opacity: 0.8;">Projects</div>
                        </div>
                        <div style="text-align: center;">
                            <div style="font-size: 28px; font-weight: 700;">98%</div>
                            <div style="font-size: 14px; opacity: 0.8;">Accuracy</div>
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
    </script>
</body>
</html>
