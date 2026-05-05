<?php
/**
 * Reports - Admin
 */
require_once '../config/database.php';

// Check if user is logged in and is admin
if (!isLoggedIn() || !hasRole('admin')) {
    header("Location: ../auth/login.php?role=admin");
    exit();
}

// Get report data
$employee_skills = $conn->query("SELECT u.full_name, COUNT(cs.id) as skill_count FROM users u LEFT JOIN candidate_skills cs ON u.id = cs.user_id WHERE u.role = 'candidate' GROUP BY u.id ORDER BY skill_count DESC LIMIT 20");

$assessment_summary = $conn->query("SELECT a.title, COUNT(r.id) as total_attempts, AVG(r.percentage) as avg_score FROM assessments a LEFT JOIN results r ON a.id = r.assessment_id GROUP BY a.id ORDER BY total_attempts DESC");

$project_fill_rate = $conn->query("SELECT p.project_name, p.total_positions, p.filled_positions, (p.filled_positions/p.total_positions)*100 as fill_rate FROM projects p ORDER BY fill_rate DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports - SkillTrack Pro</title>
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
                <a href="reports.php" class="sidebar-link active">
                    <i class="fas fa-chart-bar"></i>
                    Reports
                </a>
            </li>
        </ul>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
        <div class="dashboard-header">
            <h1>Reports</h1>
            <p>System analytics and insights</p>
        </div>

        <div class="dashboard-grid">
            <!-- Employee Skills Report -->
            <div class="table-container">
                <div class="table-header">
                    <h3>Employee Skills Report</h3>
                </div>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Employee</th>
                            <th>Skills Count</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $employee_skills->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['full_name']); ?></td>
                            <td>
                                <span class="badge badge-<?php echo $row['skill_count'] >= 5 ? 'success' : ($row['skill_count'] >= 3 ? 'warning' : 'info'); ?>">
                                    <?php echo $row['skill_count']; ?> skills
                                </span>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Assessment Performance -->
            <div class="table-container">
                <div class="table-header">
                    <h3>Assessment Performance</h3>
                </div>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Assessment</th>
                            <th>Attempts</th>
                            <th>Avg Score</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $assessment_summary->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['title']); ?></td>
                            <td><?php echo $row['total_attempts']; ?></td>
                            <td>
                                <span class="badge badge-<?php echo $row['avg_score'] >= 70 ? 'success' : ($row['avg_score'] >= 50 ? 'warning' : 'danger'); ?>">
                                    <?php echo round($row['avg_score'], 2); ?>%
                                </span>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Project Fill Rate -->
            <div class="table-container">
                <div class="table-header">
                    <h3>Project Fill Rate</h3>
                </div>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Project</th>
                            <th>Positions</th>
                            <th>Fill Rate</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $project_fill_rate->fetch_assoc()): 
                            $progress = $row['total_positions'] > 0 ? ($row['filled_positions'] / $row['total_positions']) * 100 : 0;
                        ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['project_name']); ?></td>
                            <td><?php echo $row['filled_positions']; ?>/<?php echo $row['total_positions']; ?></td>
                            <td>
                                <div style="display: flex; align-items: center; gap: 10px;">
                                    <div class="progress-bar" style="width: 80px; height: 8px;">
                                        <div class="progress-fill <?php echo $progress >= 100 ? 'success' : 'primary'; ?>" style="width: <?php echo $progress; ?>%"></div>
                                    </div>
                                    <span><?php echo round($progress); ?>%</span>
                                </div>
                            </td>
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
