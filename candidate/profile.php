<?php
/**
 * Candidate Profile
 */
require_once '../config/database.php';

// Check if user is logged in and is candidate
if (!isLoggedIn() || !hasRole('candidate')) {
    redirect('../auth/login.php?role=candidate', 'Please login as employee', 'warning');
}

$user_id = $_SESSION['user_id'];
$message = '';
$error = '';

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = sanitize($conn, $_POST['full_name']);
    $phone = sanitize($conn, $_POST['phone']);
    $address = sanitize($conn, $_POST['address']);
    $experience_years = intval($_POST['experience_years']);
    $current_position = sanitize($conn, $_POST['current_position']);
    $department = sanitize($conn, $_POST['department']);
    
    $stmt = $conn->prepare("UPDATE users SET full_name = ?, phone = ?, address = ?, experience_years = ?, current_position = ?, department = ? WHERE id = ?");
    $stmt->bind_param("ssssssi", $full_name, $phone, $address, $experience_years, $current_position, $department, $user_id);
    
    if ($stmt->execute()) {
        $_SESSION['full_name'] = $full_name;
        $message = 'Profile updated successfully';
        logActivity($conn, $user_id, 'Profile Updated', 'User updated their profile');
    } else {
        $error = 'Failed to update profile';
    }
    $stmt->close();
}

// Get user details
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - SkillTrack Pro</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar">
        <a href="../index.php" class="navbar-brand">
            <i class="fas fa-layer-group"></i>
            Employee Skill Tracker
        </a>
        <ul class="navbar-nav">
            <li><a href="../auth/logout.php" class="nav-link"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </nav>

    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="sidebar-title">Main Menu</div>
        <ul class="sidebar-menu">
            <li class="sidebar-item">
                <a href="dashboard.php" class="sidebar-link">
                    <i class="fas fa-tachometer-alt"></i>
                    Dashboard
                </a>
            </li>
            <li class="sidebar-item">
                <a href="profile.php" class="sidebar-link active">
                    <i class="fas fa-user"></i>
                    My Profile
                </a>
            </li>
            <li class="sidebar-item">
                <a href="skills.php" class="sidebar-link">
                    <i class="fas fa-brain"></i>
                    My Skills
                </a>
            </li>
            <li class="sidebar-item">
                <a href="take_assessment.php" class="sidebar-link">
                    <i class="fas fa-clipboard-check"></i>
                    Take Assessment
                </a>
            </li>
            <li class="sidebar-item">
                <a href="my_projects.php" class="sidebar-link">
                    <i class="fas fa-project-diagram"></i>
                    My Projects
                </a>
            </li>
            <li class="sidebar-item">
                <a href="results.php" class="sidebar-link">
                    <i class="fas fa-chart-bar"></i>
                    My Results
                </a>
            </li>
        </ul>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
        <?php if ($message): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> <?php echo $message; ?>
            </div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
            </div>
        <?php endif; ?>
        
        <div class="dashboard-header">
            <h1>My Profile</h1>
            <p>Manage your personal information</p>
        </div>

        <div class="dashboard-grid" style="grid-template-columns: 1fr 2fr;">
            <!-- Profile Card -->
            <div class="glass-card" style="padding: 30px; text-align: center;">
                <div style="width: 120px; height: 120px; border-radius: 50%; background: var(--gradient-primary); display: flex; align-items: center; justify-content: center; font-size: 48px; font-weight: 700; margin: 0 auto 20px;">
                    <?php echo strtoupper(substr($user['full_name'], 0, 1)); ?>
                </div>
                <h3 style="margin-bottom: 8px;"><?php echo htmlspecialchars($user['full_name']); ?></h3>
                <p style="color: var(--text-muted); margin-bottom: 20px;"><?php echo htmlspecialchars($user['email']); ?></p>
                
                <div style="padding-top: 20px; border-top: 1px solid var(--border-glass);">
                    <div style="display: flex; justify-content: space-between; margin-bottom: 12px;">
                        <span style="color: var(--text-muted);">Role</span>
                        <span class="badge badge-primary">Employee</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; margin-bottom: 12px;">
                        <span style="color: var(--text-muted);">Status</span>
                        <span class="badge badge-<?php echo $user['is_active'] ? 'success' : 'danger'; ?>"><?php echo $user['is_active'] ? 'Active' : 'Inactive'; ?></span>
                    </div>
                    <div style="display: flex; justify-content: space-between;">
                        <span style="color: var(--text-muted);">Member Since</span>
                        <span><?php echo date('M d, Y', strtotime($user['created_at'])); ?></span>
                    </div>
                </div>
            </div>
            
            <!-- Edit Profile Form -->
            <div class="glass-card" style="padding: 30px;">
                <h3 style="margin-bottom: 24px;"><i class="fas fa-user-edit"></i> Edit Profile</h3>
                
                <form method="POST" action="" data-validate>
                    <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px;">
                        <div class="form-group">
                            <label class="form-label">Full Name *</label>
                            <div style="position: relative;">
                                <i class="fas fa-user" style="position: absolute; left: 16px; top: 50%; transform: translateY(-50%); color: var(--text-muted);"></i>
                                <input type="text" name="full_name" class="glass-input" style="padding-left: 45px;" value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Email</label>
                            <div style="position: relative;">
                                <i class="fas fa-envelope" style="position: absolute; left: 16px; top: 50%; transform: translateY(-50%); color: var(--text-muted);"></i>
                                <input type="email" class="glass-input" style="padding-left: 45px;" value="<?php echo htmlspecialchars($user['email']); ?>" disabled>
                            </div>
                            <small style="color: var(--text-muted);">Email cannot be changed</small>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Phone</label>
                            <div style="position: relative;">
                                <i class="fas fa-phone" style="position: absolute; left: 16px; top: 50%; transform: translateY(-50%); color: var(--text-muted);"></i>
                                <input type="tel" name="phone" class="glass-input" style="padding-left: 45px;" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>" placeholder="Your phone number">
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Experience (Years)</label>
                            <div style="position: relative;">
                                <i class="fas fa-briefcase" style="position: absolute; left: 16px; top: 50%; transform: translateY(-50%); color: var(--text-muted);"></i>
                                <input type="number" name="experience_years" class="glass-input" style="padding-left: 45px;" value="<?php echo $user['experience_years']; ?>" min="0" max="50">
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Current Position</label>
                            <div style="position: relative;">
                                <i class="fas fa-id-badge" style="position: absolute; left: 16px; top: 50%; transform: translateY(-50%); color: var(--text-muted);"></i>
                                <input type="text" name="current_position" class="glass-input" style="padding-left: 45px;" value="<?php echo htmlspecialchars($user['current_position'] ?? ''); ?>" placeholder="e.g., Senior Developer">
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Department</label>
                            <div style="position: relative;">
                                <i class="fas fa-building" style="position: absolute; left: 16px; top: 50%; transform: translateY(-50%); color: var(--text-muted);"></i>
                                <input type="text" name="department" class="glass-input" style="padding-left: 45px;" value="<?php echo htmlspecialchars($user['department'] ?? ''); ?>" placeholder="e.g., Engineering">
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group" style="margin-top: 20px;">
                        <label class="form-label">Address</label>
                        <textarea name="address" class="glass-input" rows="3" placeholder="Your address"><?php echo htmlspecialchars($user['address'] ?? ''); ?></textarea>
                    </div>
                    
                    <div style="display: flex; gap: 16px; margin-top: 24px;">
                        <button type="submit" class="btn btn-primary btn-lg" style="flex: 1;">
                            <i class="fas fa-save"></i> Save Changes
                        </button>
                        <a href="dashboard.php" class="btn btn-outline btn-lg">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <script src="../assets/js/main.js"></script>
</body>
</html>
