<?php
/**
 * Reports - HR
 */
require_once '../config/database.php';

// Check if user is logged in and is HR
if (!isLoggedIn() || !hasRole('hr')) {
    redirect('../auth/login.php?role=hr', 'Please login as HR', 'warning');
}

$user_id = $_SESSION['user_id'];

// Get report data
$my_projects = $conn->query("SELECT p.*, COUNT(pa.id) as assigned FROM projects p LEFT JOIN project_assignments pa ON p.id = pa.project_id WHERE p.created_by = $user_id GROUP BY p.id");

$employee_performance = $conn->query("SELECT u.full_name, AVG(r.percentage) as avg_score, COUNT(r.id) as assessments_taken FROM users u LEFT JOIN results r ON u.id = r.user_id WHERE u.role = 'candidate' GROUP BY u.id HAVING assessments_taken > 0 ORDER BY avg_score DESC LIMIT 20");

$skill_demand = $conn->query("SELECT s.skill_name, COUNT(ps.project_id) as project_count FROM skills s JOIN project_skills ps ON s.id = ps.skill_id JOIN projects p ON ps.project_id = p.id GROUP BY s.id ORDER BY project_count DESC LIMIT 15");
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
            <p>HR analytics and insights</p>
        </div>

        <!-- Report Actions -->
        <div class="glass-card" style="padding: 20px; margin-bottom: 24px;">
            <div style="display: flex; gap: 16px; flex-wrap: wrap;">
                <button onclick="window.print()" class="btn btn-primary">
                    <i class="fas fa-print"></i> Print Report
                </button>
            </div>
        </div>

        <div class="dashboard-grid">
            <!-- My Projects Summary -->
            <div class="table-container">
                <div class="table-header">
                    <h3>My Projects Summary</h3>
                </div>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Project</th>
                            <th>Status</th>
                            <th>Assigned</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $my_projects->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['project_name']); ?></td>
                            <td>
                                <span class="badge badge-<?php echo $row['status'] === 'open' ? 'success' : ($row['status'] === 'completed' ? 'primary' : 'warning'); ?>">
                                    <?php echo ucfirst($row['status']); ?>
                                </span>
                            </td>
                            <td><?php echo $row['assigned']; ?> employees</td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Employee Performance -->
            <div class="table-container">
                <div class="table-header">
                    <h3>Employee Performance</h3>
                </div>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Employee</th>
                            <th>Avg Score</th>
                            <th>Assessments</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $employee_performance->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['full_name']); ?></td>
                            <td>
                                <span class="badge badge-<?php echo $row['avg_score'] >= 70 ? 'success' : ($row['avg_score'] >= 50 ? 'warning' : 'danger'); ?>">
                                    <?php echo round($row['avg_score'], 2); ?>%
                                </span>
                            </td>
                            <td><?php echo $row['assessments_taken']; ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Skill Demand -->
            <div class="table-container">
                <div class="table-header">
                    <h3>Most In-Demand Skills</h3>
                </div>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Skill</th>
                            <th>Projects</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $skill_demand->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['skill_name']); ?></td>
                            <td>
                                <span class="badge badge-primary"><?php echo $row['project_count']; ?> projects</span>
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
