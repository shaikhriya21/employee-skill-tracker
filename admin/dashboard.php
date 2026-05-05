<?php
/**
 * Admin Dashboard
 */
require_once '../config/database.php';

// Check if user is logged in and is admin
if (!isLoggedIn() || !hasRole('admin')) {
    header("Location: ../auth/login.php?role=admin");
    exit();
}

// Get statistics
$stats = [
    'total_employees' => 0,
    'total_hr' => 0,
    'total_skills' => 0,
    'total_projects' => 0,
    'total_assessments' => 0,
    'total_assignments' => 0
];

$result = $conn->query("SELECT COUNT(*) as count FROM users WHERE role = 'candidate'");
if ($result) $stats['total_employees'] = $result->fetch_assoc()['count'];

$result = $conn->query("SELECT COUNT(*) as count FROM users WHERE role = 'hr'");
if ($result) $stats['total_hr'] = $result->fetch_assoc()['count'];

$result = $conn->query("SELECT COUNT(*) as count FROM skills");
if ($result) $stats['total_skills'] = $result->fetch_assoc()['count'];

$result = $conn->query("SELECT COUNT(*) as count FROM projects");
if ($result) $stats['total_projects'] = $result->fetch_assoc()['count'];

$result = $conn->query("SELECT COUNT(*) as count FROM assessments");
if ($result) $stats['total_assessments'] = $result->fetch_assoc()['count'];

$result = $conn->query("SELECT COUNT(*) as count FROM project_assignments");
if ($result) $stats['total_assignments'] = $result->fetch_assoc()['count'];

$recent_users = $conn->query("SELECT id, full_name, email, role, created_at FROM users ORDER BY created_at DESC LIMIT 5");
$recent_projects = $conn->query("SELECT p.*, u.full_name as created_by_name FROM projects p JOIN users u ON p.created_by = u.id ORDER BY p.created_at DESC LIMIT 5");

// Get skill distribution data
$skill_dist = $conn->query("SELECT s.skill_name, COUNT(cs.id) as count FROM skills s LEFT JOIN candidate_skills cs ON s.id = cs.skill_id GROUP BY s.id ORDER BY count DESC LIMIT 8");

// Get assessment results data
$passed_count = $conn->query("SELECT COUNT(*) as count FROM results WHERE status = 'passed'")->fetch_assoc()['count'];
$failed_count = $conn->query("SELECT COUNT(*) as count FROM results WHERE status = 'failed'")->fetch_assoc()['count'];
$total_results = $passed_count + $failed_count;
$pass_percentage = $total_results > 0 ? round(($passed_count / $total_results) * 100) : 0;
$fail_percentage = $total_results > 0 ? round(($failed_count / $total_results) * 100) : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - SkillTrack Pro</title>
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
                <a href="dashboard.php" class="sidebar-link active">
                    <i class="fas fa-tachometer-alt"></i>
                    Dashboard
                </a>
            </li>
            <li class="sidebar-item">
                <a href="manage_users.php" class="sidebar-link">
                    <i class="fas fa-users"></i>
                    Manage Users
                </a>
            </li>
            <li class="sidebar-item">
                <a href="manage_skills.php" class="sidebar-link">
                    <i class="fas fa-brain"></i>
                    Manage Skills
                </a>
            </li>
            <li class="sidebar-item">
                <a href="manage_assessments.php" class="sidebar-link">
                    <i class="fas fa-clipboard-check"></i>
                    Assessments
                </a>
            </li>
            <li class="sidebar-item">
                <a href="manage_projects.php" class="sidebar-link">
                    <i class="fas fa-project-diagram"></i>
                    Projects
                </a>
            </li>
            <li class="sidebar-item">
                <a href="reports.php" class="sidebar-link">
                    <i class="fas fa-chart-bar"></i>
                    Reports
                </a>
            </li>
        </ul>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
        <?php echo showMessage(); ?>
        
        <div class="dashboard-header">
            <h1>Admin Dashboard</h1>
            <p>Welcome back, <?php echo htmlspecialchars($_SESSION['full_name']); ?>! Here's what's happening today.</p>
        </div>

        <!-- Stats Grid -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon primary">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo $stats['total_employees']; ?></h3>
                    <p>Total Employees</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon success">
                    <i class="fas fa-user-tie"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo $stats['total_hr']; ?></h3>
                    <p>HR Managers</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon warning">
                    <i class="fas fa-brain"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo $stats['total_skills']; ?></h3>
                    <p>Total Skills</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon danger">
                    <i class="fas fa-project-diagram"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo $stats['total_projects']; ?></h3>
                    <p>Total Projects</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon primary">
                    <i class="fas fa-clipboard-check"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo $stats['total_assessments']; ?></h3>
                    <p>Assessments</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon success">
                    <i class="fas fa-tasks"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo $stats['total_assignments']; ?></h3>
                    <p>Assignments</p>
                </div>
            </div>
        </div>

        <!-- Analytics Row - New Design -->
        <div class="dashboard-grid" style="grid-template-columns: 2fr 1fr;">
            <!-- Skill Distribution - New Design -->
            <div class="dashboard-card">
                <h3><i class="fas fa-chart-bar"></i> Skill Distribution</h3>
                <div style="margin-top: 20px;">
                    <?php 
                    $max_count = 0;
                    $skill_data = [];
                    while ($row = $skill_dist->fetch_assoc()) {
                        $skill_data[] = $row;
                        if ($row['count'] > $max_count) $max_count = $row['count'];
                    }
                    foreach ($skill_data as $skill): 
                        $percentage = $max_count > 0 ? ($skill['count'] / $max_count) * 100 : 0;
                    ?>
                    <div style="margin-bottom: 16px;">
                        <div style="display: flex; justify-content: space-between; margin-bottom: 6px;">
                            <span style="font-size: 14px;"><?php echo htmlspecialchars($skill['skill_name']); ?></span>
                            <span style="font-size: 14px; color: var(--text-muted);"><?php echo $skill['count']; ?> employees</span>
                        </div>
                        <div class="progress-bar" style="height: 8px;">
                            <div class="progress-fill primary" style="width: <?php echo $percentage; ?>%;"></div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <!-- Assessment Results - New Design -->
            <div class="dashboard-card">
                <h3><i class="fas fa-pie-chart"></i> Assessment Results</h3>
                <div style="margin-top: 20px; text-align: center;">
                    <!-- Donut Chart CSS -->
                    <div style="position: relative; width: 150px; height: 150px; margin: 0 auto 20px;">
                        <svg viewBox="0 0 36 36" style="width: 100%; height: 100%; transform: rotate(-90deg);">
                            <!-- Background circle -->
                            <path d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" 
                                  fill="none" stroke="rgba(255,255,255,0.1)" stroke-width="3" />
                            <!-- Passed segment -->
                            <?php if ($pass_percentage > 0): ?>
                            <path d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" 
                                  fill="none" stroke="#10b981" stroke-width="3" 
                                  stroke-dasharray="<?php echo $pass_percentage; ?>, 100" />
                            <?php endif; ?>
                            <!-- Failed segment -->
                            <?php if ($fail_percentage > 0): ?>
                            <path d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" 
                                  fill="none" stroke="#ef4444" stroke-width="3" 
                                  stroke-dasharray="<?php echo $fail_percentage; ?>, 100"
                                  stroke-dashoffset="-<?php echo $pass_percentage; ?>" />
                            <?php endif; ?>
                        </svg>
                        <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); text-align: center;">
                            <div style="font-size: 24px; font-weight: 700;"><?php echo $total_results; ?></div>
                            <div style="font-size: 11px; color: var(--text-muted);">Total</div>
                        </div>
                    </div>
                    
                    <!-- Legend -->
                    <div style="display: flex; justify-content: center; gap: 20px;">
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <div style="width: 12px; height: 12px; background: #10b981; border-radius: 50%;"></div>
                            <span style="font-size: 13px;">Passed (<?php echo $passed_count; ?>)</span>
                        </div>
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <div style="width: 12px; height: 12px; background: #ef4444; border-radius: 50%;"></div>
                            <span style="font-size: 13px;">Failed (<?php echo $failed_count; ?>)</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="dashboard-grid">
            <div class="table-container">
                <div class="table-header">
                    <h3>Recent Users</h3>
                    <a href="manage_users.php" class="btn btn-sm btn-outline">View All</a>
                </div>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Joined</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($user = $recent_users->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($user['full_name']); ?></td>
                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                            <td><span class="badge badge-<?php echo $user['role'] === 'admin' ? 'danger' : ($user['role'] === 'hr' ? 'warning' : 'primary'); ?>"><?php echo ucfirst($user['role']); ?></span></td>
                            <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
            
            <div class="table-container">
                <div class="table-header">
                    <h3>Recent Projects</h3>
                    <a href="manage_projects.php" class="btn btn-sm btn-outline">View All</a>
                </div>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Project</th>
                            <th>Positions</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($project = $recent_projects->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($project['project_name']); ?></td>
                            <td><?php echo $project['filled_positions']; ?>/<?php echo $project['total_positions']; ?></td>
                            <td><span class="badge badge-<?php echo $project['status'] === 'open' ? 'success' : ($project['status'] === 'completed' ? 'primary' : 'warning'); ?>"><?php echo ucfirst($project['status']); ?></span></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <script src="../assets/js/main.js"></script>
</body>
</html>
