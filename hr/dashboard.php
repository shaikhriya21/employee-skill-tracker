<?php
/**
 * HR Dashboard
 */
require_once '../config/database.php';

// Check if user is logged in and is HR
if (!isLoggedIn() || !hasRole('hr')) {
    redirect('../auth/login.php?role=hr', 'Please login as HR', 'warning');
}

// Get statistics
$stats = [
    'total_employees' => 0,
    'total_projects' => 0,
    'open_projects' => 0,
    'total_assignments' => 0,
    'pending_assignments' => 0
];

// Count employees
$result = $conn->query("SELECT COUNT(*) as count FROM users WHERE role = 'candidate' AND is_active = 1");
if ($result) $stats['total_employees'] = $result->fetch_assoc()['count'];

// Count projects
$result = $conn->query("SELECT COUNT(*) as count FROM projects WHERE created_by = " . $_SESSION['user_id']);
if ($result) $stats['total_projects'] = $result->fetch_assoc()['count'];

// Count open projects
$result = $conn->query("SELECT COUNT(*) as count FROM projects WHERE status = 'open'");
if ($result) $stats['open_projects'] = $result->fetch_assoc()['count'];

// Count assignments
$result = $conn->query("SELECT COUNT(*) as count FROM project_assignments");
if ($result) $stats['total_assignments'] = $result->fetch_assoc()['count'];

// Count pending assignments
$result = $conn->query("SELECT COUNT(*) as count FROM project_assignments WHERE status = 'pending'");
if ($result) $stats['pending_assignments'] = $result->fetch_assoc()['count'];

// Get recent projects
$recent_projects = $conn->query("SELECT p.*, COUNT(pa.id) as assigned FROM projects p LEFT JOIN project_assignments pa ON p.id = pa.project_id WHERE p.created_by = " . $_SESSION['user_id'] . " GROUP BY p.id ORDER BY p.created_at DESC LIMIT 5");

// Get pending assignments
$pending_assignments = $conn->query("SELECT pa.*, p.project_name, u.full_name, u.email FROM project_assignments pa JOIN projects p ON pa.project_id = p.id JOIN users u ON pa.user_id = u.id WHERE pa.status = 'pending' ORDER BY pa.assigned_at DESC LIMIT 5");

// Get top skilled employees
$top_employees = $conn->query("SELECT u.id, u.full_name, COUNT(cs.id) as skill_count FROM users u JOIN candidate_skills cs ON u.id = cs.user_id WHERE u.role = 'candidate' AND u.is_active = 1 GROUP BY u.id ORDER BY skill_count DESC LIMIT 5");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HR Dashboard - SkillTrack Pro</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
                <a href="create_project.php" class="sidebar-link">
                    <i class="fas fa-plus-circle"></i>
                    Create Project
                </a>
            </li>
            <li class="sidebar-item">
                <a href="view_projects.php" class="sidebar-link">
                    <i class="fas fa-project-diagram"></i>
                    View Projects
                </a>
            </li>
            <li class="sidebar-item">
                <a href="assign_employee.php" class="sidebar-link">
                    <i class="fas fa-user-plus"></i>
                    Assign Employees
                </a>
            </li>
            <li class="sidebar-item">
                <a href="assessment_results.php" class="sidebar-link">
                    <i class="fas fa-clipboard-check"></i>
                    Assessment Results
                </a>
            </li>
            <li class="sidebar-item">
                <a href="employee_skills.php" class="sidebar-link">
                    <i class="fas fa-brain"></i>
                    Employee Skills
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
            <h1>HR Dashboard</h1>
            <p>Welcome back, <?php echo htmlspecialchars($_SESSION['full_name']); ?>! Manage your projects and team.</p>
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
                    <i class="fas fa-project-diagram"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo $stats['total_projects']; ?></h3>
                    <p>My Projects</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon warning">
                    <i class="fas fa-door-open"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo $stats['open_projects']; ?></h3>
                    <p>Open Projects</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon danger">
                    <i class="fas fa-tasks"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo $stats['pending_assignments']; ?></h3>
                    <p>Pending Approvals</p>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="glass-card" style="padding: 24px; margin-bottom: 24px;">
            <h3 style="margin-bottom: 20px;">Quick Actions</h3>
            <div style="display: flex; gap: 16px; flex-wrap: wrap;">
                <a href="create_project.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Create New Project
                </a>
                <a href="assign_employee.php" class="btn btn-success">
                    <i class="fas fa-user-plus"></i> Assign Employee
                </a>
                <a href="view_projects.php" class="btn btn-secondary">
                    <i class="fas fa-eye"></i> View All Projects
                </a>
            </div>
        </div>

        <!-- Dashboard Content -->
        <div class="dashboard-grid">
            <!-- Recent Projects -->
            <div class="table-container">
                <div class="table-header">
                    <h3>My Recent Projects</h3>
                    <a href="view_projects.php" class="btn btn-sm btn-outline">View All</a>
                </div>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Project</th>
                            <th>Positions</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($project = $recent_projects->fetch_assoc()): 
                            $progress = $project['total_positions'] > 0 ? ($project['filled_positions'] / $project['total_positions']) * 100 : 0;
                        ?>
                        <tr>
                            <td><?php echo htmlspecialchars($project['project_name']); ?></td>
                            <td>
                                <div style="display: flex; align-items: center; gap: 10px;">
                                    <span><?php echo $project['filled_positions']; ?>/<?php echo $project['total_positions']; ?></span>
                                    <div class="progress-bar" style="width: 60px; height: 6px;">
                                        <div class="progress-fill <?php echo $progress >= 100 ? 'success' : 'primary'; ?>" style="width: <?php echo $progress; ?>%"></div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge badge-<?php echo $project['status'] === 'open' ? 'success' : ($project['status'] === 'completed' ? 'primary' : 'warning'); ?>">
                                    <?php echo ucfirst($project['status']); ?>
                                </span>
                            </td>
                            <td>
                                <a href="view_project.php?id=<?php echo $project['id']; ?>" class="btn btn-sm btn-primary">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Pending Approvals -->
            <div class="table-container">
                <div class="table-header">
                    <h3>Pending Approvals</h3>
                    <a href="assign_employee.php" class="btn btn-sm btn-outline">Manage</a>
                </div>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Employee</th>
                            <th>Project</th>
                            <th>Match %</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($assignment = $pending_assignments->fetch_assoc()): ?>
                        <tr>
                            <td>
                                <div style="display: flex; align-items: center; gap: 10px;">
                                    <div style="width: 30px; height: 30px; border-radius: 50%; background: var(--gradient-primary); display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 600;">
                                        <?php echo strtoupper(substr($assignment['full_name'], 0, 1)); ?>
                                    </div>
                                    <?php echo htmlspecialchars($assignment['full_name']); ?>
                                </div>
                            </td>
                            <td><?php echo htmlspecialchars($assignment['project_name']); ?></td>
                            <td>
                                <span class="badge badge-<?php echo $assignment['skill_match_percentage'] >= 70 ? 'success' : ($assignment['skill_match_percentage'] >= 50 ? 'warning' : 'danger'); ?>">
                                    <?php echo round($assignment['skill_match_percentage']); ?>%
                                </span>
                            </td>
                            <td>
                                <div style="display: flex; gap: 8px;">
                                    <a href="assign_employee.php?action=approve&id=<?php echo $assignment['id']; ?>" class="btn btn-sm btn-success">
                                        <i class="fas fa-check"></i>
                                    </a>
                                    <a href="assign_employee.php?action=reject&id=<?php echo $assignment['id']; ?>" class="btn btn-sm btn-danger">
                                        <i class="fas fa-times"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                        <?php if ($pending_assignments->num_rows === 0): ?>
                        <tr>
                            <td colspan="4" style="text-align: center; color: var(--text-muted);">No pending approvals</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Top Employees by Skills -->
        <div class="dashboard-card" style="margin-top: 24px;">
            <h3><i class="fas fa-star"></i> Top Employees by Skills</h3>
            <div style="display: flex; gap: 20px; flex-wrap: wrap; margin-top: 20px;">
                <?php while ($employee = $top_employees->fetch_assoc()): ?>
                <div class="employee-card" style="flex: 1; min-width: 200px;">
                    <div class="employee-avatar">
                        <?php echo strtoupper(substr($employee['full_name'], 0, 1)); ?>
                    </div>
                    <div class="employee-info">
                        <div class="employee-name"><?php echo htmlspecialchars($employee['full_name']); ?></div>
                        <div class="employee-role"><?php echo $employee['skill_count']; ?> skills</div>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
        </div>
    </main>

    <script src="../assets/js/main.js"></script>
</body>
</html>
