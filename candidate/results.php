<?php
/**
 * My Results - Candidate
 */
require_once '../config/database.php';

// Check if user is logged in and is candidate
if (!isLoggedIn() || !hasRole('candidate')) {
    redirect('../auth/login.php?role=candidate', 'Please login as employee', 'warning');
}

$user_id = $_SESSION['user_id'];

// Get all assessment results
$results = $conn->query("SELECT r.*, a.title, a.duration_minutes, a.total_questions, a.passing_score FROM results r JOIN assessments a ON r.assessment_id = a.id WHERE r.user_id = $user_id ORDER BY r.completed_at DESC");

// Calculate statistics
$total_assessments = $results->num_rows;
$passed = 0;
$failed = 0;
$total_percentage = 0;

$results->data_seek(0);
while ($row = $results->fetch_assoc()) {
    if ($row['status'] === 'passed') {
        $passed++;
    } else {
        $failed++;
    }
    $total_percentage += $row['percentage'];
}

$average_percentage = $total_assessments > 0 ? round($total_percentage / $total_assessments, 2) : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Results - SkillTrack Pro</title>
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
                <a href="profile.php" class="sidebar-link">
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
                <a href="results.php" class="sidebar-link active">
                    <i class="fas fa-chart-bar"></i>
                    My Results
                </a>
            </li>
        </ul>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
        <div class="dashboard-header">
            <h1>My Results</h1>
            <p>View all your assessment results</p>
        </div>

        <!-- Statistics -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon primary">
                    <i class="fas fa-clipboard-check"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo $total_assessments; ?></h3>
                    <p>Total Assessments</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon success">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo $passed; ?></h3>
                    <p>Passed</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon danger">
                    <i class="fas fa-times-circle"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo $failed; ?></h3>
                    <p>Failed</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon warning">
                    <i class="fas fa-percentage"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo $average_percentage; ?>%</h3>
                    <p>Average Score</p>
                </div>
            </div>
        </div>

        <!-- Results Table -->
        <div class="table-container">
            <div class="table-header">
                <h3>Assessment Results</h3>
                <a href="take_assessment.php" class="btn btn-sm btn-primary">
                    <i class="fas fa-plus"></i> Take New Assessment
                </a>
            </div>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Assessment</th>
                        <th>Score</th>
                        <th>Percentage</th>
                        <th>Status</th>
                        <th>Completed</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $results->data_seek(0);
                    while ($result = $results->fetch_assoc()): 
                    ?>
                    <tr>
                        <td>
                            <strong><?php echo htmlspecialchars($result['title']); ?></strong>
                            <div style="font-size: 12px; color: var(--text-muted); margin-top: 4px;">
                                <?php echo $result['total_questions']; ?> questions | Pass: <?php echo $result['passing_score']; ?>%
                            </div>
                        </td>
                        <td><?php echo $result['score']; ?>/<?php echo $result['total_marks']; ?></td>
                        <td>
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <div class="progress-bar" style="width: 100px; height: 8px;">
                                    <div class="progress-fill <?php echo $result['status'] === 'passed' ? 'success' : 'danger'; ?>" style="width: <?php echo $result['percentage']; ?>%"></div>
                                </div>
                                <span style="font-weight: 600;"><?php echo $result['percentage']; ?>%</span>
                            </div>
                        </td>
                        <td>
                            <span class="badge badge-<?php echo $result['status'] === 'passed' ? 'success' : 'danger'; ?>">
                                <i class="fas fa-<?php echo $result['status'] === 'passed' ? 'check' : 'times'; ?>"></i>
                                <?php echo ucfirst($result['status']); ?>
                            </span>
                        </td>
                        <td><?php echo date('M d, Y \a\t h:i A', strtotime($result['completed_at'])); ?></td>
                    </tr>
                    <?php endwhile; ?>
                    <?php if ($results->num_rows === 0): ?>
                    <tr>
                        <td colspan="5" style="text-align: center; color: var(--text-muted); padding: 40px;">
                            <i class="fas fa-clipboard-list" style="font-size: 32px; margin-bottom: 10px; display: block;"></i>
                            No results yet. <a href="take_assessment.php">Take your first assessment</a>
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>

    <script src="../assets/js/main.js"></script>
</body>
</html>
