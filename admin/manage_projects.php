<?php
/**
 * Manage Projects - Admin
 */
require_once '../config/database.php';

// Check if user is logged in and is admin
if (!isLoggedIn() || !hasRole('admin')) {
    redirect('../auth/login.php?role=admin', 'Please login as admin', 'warning');
}

$message = '';
$error = '';

// Get all projects with details
$projects = $conn->query("SELECT p.*, u.full_name as created_by_name, COUNT(DISTINCT pa.id) as assigned_count FROM projects p JOIN users u ON p.created_by = u.id LEFT JOIN project_assignments pa ON p.id = pa.project_id GROUP BY p.id ORDER BY p.created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Projects - SkillTrack Pro</title>
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
                <a href="manage_projects.php" class="sidebar-link active">
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
            <h1>Manage Projects</h1>
            <p>View all projects and their assignments</p>
        </div>

        <!-- Projects Grid -->
        <div class="project-grid">
            <?php while ($project = $projects->fetch_assoc()): 
                // Get project skills
                $project_id = $project['id'];
                $skills_result = $conn->query("SELECT s.skill_name FROM project_skills ps JOIN skills s ON ps.skill_id = s.id WHERE ps.project_id = $project_id LIMIT 5");
                $skills = [];
                while ($skill = $skills_result->fetch_assoc()) {
                    $skills[] = $skill['skill_name'];
                }
                
                $progress = $project['total_positions'] > 0 ? ($project['filled_positions'] / $project['total_positions']) * 100 : 0;
            ?>
            <div class="project-card">
                <div class="project-header">
                    <div>
                        <h4 class="project-title"><?php echo htmlspecialchars($project['project_name']); ?></h4>
                        <span class="badge badge-<?php echo $project['status'] === 'open' ? 'success' : ($project['status'] === 'completed' ? 'primary' : 'warning'); ?>">
                            <?php echo ucfirst($project['status']); ?>
                        </span>
                    </div>
                    <div class="match-circle" style="width: 50px; height: 50px;">
                        <span class="match-percentage" style="font-size: 14px;"><?php echo round($progress); ?>%</span>
                    </div>
                </div>
                
                <p class="project-description"><?php echo htmlspecialchars(substr($project['description'], 0, 100)) . (strlen($project['description']) > 100 ? '...' : ''); ?></p>
                
                <div class="project-meta">
                    <span><i class="fas fa-users"></i> <?php echo $project['filled_positions']; ?>/<?php echo $project['total_positions']; ?> filled</span>
                    <span><i class="fas fa-user"></i> <?php echo htmlspecialchars($project['created_by_name']); ?></span>
                </div>
                
                <div class="project-skills">
                    <?php foreach ($skills as $skill): ?>
                        <span class="project-skill"><?php echo htmlspecialchars($skill); ?></span>
                    <?php endforeach; ?>
                </div>
                
                <div class="progress-container">
                    <div class="progress-bar">
                        <div class="progress-fill <?php echo $progress >= 100 ? 'success' : 'primary'; ?>" style="width: <?php echo $progress; ?>%"></div>
                    </div>
                </div>
                
                <div class="project-actions">
                    <span style="font-size: 12px; color: var(--text-muted);">
                        <i class="fas fa-calendar"></i> <?php echo date('M d, Y', strtotime($project['start_date'])); ?>
                    </span>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
    </main>

    <script src="../assets/js/main.js"></script>
</body>
</html>
