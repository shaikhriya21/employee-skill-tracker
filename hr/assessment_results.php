<?php
/**
 * Assessment Results - HR
 */
require_once '../config/database.php';

// Check if user is logged in and is HR
if (!isLoggedIn() || !hasRole('hr')) {
    redirect('../auth/login.php?role=hr', 'Please login as HR', 'warning');
}

// Get all assessment results
$results = $conn->query("SELECT r.*, a.title as assessment_title, u.full_name, u.email FROM results r JOIN assessments a ON r.assessment_id = a.id JOIN users u ON r.user_id = u.id ORDER BY r.completed_at DESC LIMIT 50");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assessment Results - SkillTrack Pro</title>
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
                <a href="assessment_results.php" class="sidebar-link active">
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
        </ul>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
        <div class="dashboard-header">
            <h1>Assessment Results</h1>
            <p>View all employee assessment results</p>
        </div>

        <div class="table-container">
            <div class="table-header">
                <h3>All Results</h3>
                <span style="color: var(--text-muted); font-size: 14px;"><?php echo $results->num_rows; ?> results</span>
            </div>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Employee</th>
                        <th>Assessment</th>
                        <th>Score</th>
                        <th>Percentage</th>
                        <th>Status</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($result = $results->fetch_assoc()): ?>
                    <tr>
                        <td>
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <div style="width: 35px; height: 35px; border-radius: 50%; background: var(--gradient-primary); display: flex; align-items: center; justify-content: center; font-size: 14px; font-weight: 600;">
                                    <?php echo strtoupper(substr($result['full_name'], 0, 1)); ?>
                                </div>
                                <div>
                                    <div style="font-weight: 500;"><?php echo htmlspecialchars($result['full_name']); ?></div>
                                    <div style="font-size: 12px; color: var(--text-muted);"><?php echo htmlspecialchars($result['email']); ?></div>
                                </div>
                            </div>
                        </td>
                        <td><?php echo htmlspecialchars($result['assessment_title']); ?></td>
                        <td><?php echo $result['score']; ?>/<?php echo $result['total_marks']; ?></td>
                        <td>
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <div class="progress-bar" style="width: 60px; height: 6px;">
                                    <div class="progress-fill <?php echo $result['status'] === 'passed' ? 'success' : 'danger'; ?>" style="width: <?php echo $result['percentage']; ?>%"></div>
                                </div>
                                <span><?php echo $result['percentage']; ?>%</span>
                            </div>
                        </td>
                        <td>
                            <span class="badge badge-<?php echo $result['status'] === 'passed' ? 'success' : 'danger'; ?>">
                                <?php echo ucfirst($result['status']); ?>
                            </span>
                        </td>
                        <td><?php echo date('M d, Y', strtotime($result['completed_at'])); ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </main>

    <script src="../assets/js/main.js"></script>
</body>
</html>
