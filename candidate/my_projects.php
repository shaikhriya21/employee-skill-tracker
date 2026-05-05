<?php
/**
 * My Projects - Candidate
 */
require_once '../config/database.php';

// Check if user is logged in and is candidate
if (!isLoggedIn() || !hasRole('candidate')) {
    redirect('../auth/login.php?role=candidate', 'Please login as employee', 'warning');
}

$user_id = $_SESSION['user_id'];

// Get project assignments
$assignments = $conn->query("SELECT pa.*, p.project_name, p.description, p.start_date, p.end_date, p.status as project_status FROM project_assignments pa JOIN projects p ON pa.project_id = p.id WHERE pa.user_id = $user_id ORDER BY pa.assigned_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Projects - SkillTrack Pro</title>
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
                <a href="my_projects.php" class="sidebar-link active">
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
        <div class="dashboard-header">
            <h1>My Projects</h1>
            <p>View your project assignments</p>
        </div>

        <div class="project-grid">
            <?php while ($assignment = $assignments->fetch_assoc()): ?>
            <div class="project-card">
                <div class="project-header">
                    <div>
                        <h4 class="project-title"><?php echo htmlspecialchars($assignment['project_name']); ?></h4>
                        <span class="badge badge-<?php echo $assignment['status'] === 'approved' ? 'success' : ($assignment['status'] === 'rejected' ? 'danger' : 'warning'); ?>">
                            <?php echo ucfirst($assignment['status']); ?>
                        </span>
                    </div>
                    <div class="match-circle" style="width: 60px; height: 60px;">
                        <span class="match-percentage" style="font-size: 16px;"><?php echo round($assignment['skill_match_percentage']); ?>%</span>
                        <span class="match-label">Match</span>
                    </div>
                </div>
                
                <p class="project-description"><?php echo htmlspecialchars(substr($assignment['description'], 0, 100)) . (strlen($assignment['description']) > 100 ? '...' : ''); ?></p>
                
                <div class="project-meta">
                    <span><i class="fas fa-calendar"></i> <?php echo $assignment['start_date'] ? date('M d, Y', strtotime($assignment['start_date'])) : 'Not set'; ?></span>
                    <span><i class="fas fa-flag"></i> <?php echo ucfirst($assignment['project_status']); ?></span>
                </div>
                
                <?php if ($assignment['notes']): ?>
                <div style="margin-top: 16px; padding: 12px; background: rgba(255,255,255,0.03); border-radius: var(--radius-md);">
                    <div style="font-size: 12px; color: var(--text-muted); margin-bottom: 4px;"><i class="fas fa-comment"></i> Notes</div>
                    <div style="font-size: 13px;"><?php echo htmlspecialchars($assignment['notes']); ?></div>
                </div>
                <?php endif; ?>
                
                <div style="margin-top: 16px; padding-top: 16px; border-top: 1px solid var(--border-glass); font-size: 12px; color: var(--text-muted);">
                    <i class="fas fa-clock"></i> Assigned on <?php echo date('M d, Y', strtotime($assignment['assigned_at'])); ?>
                </div>
            </div>
            <?php endwhile; ?>
            
            <?php if ($assignments->num_rows === 0): ?>
            <div class="glass-card" style="padding: 40px; text-align: center; grid-column: 1 / -1;">
                <i class="fas fa-folder-open" style="font-size: 48px; color: var(--text-muted); margin-bottom: 16px;"></i>
                <h3>No Projects Yet</h3>
                <p style="color: var(--text-muted);">You haven't been assigned to any projects yet.</p>
                <a href="skills.php" class="btn btn-primary" style="margin-top: 16px;">
                    <i class="fas fa-brain"></i> Add Skills
                </a>
            </div>
            <?php endif; ?>
        </div>
    </main>

    <script src="../assets/js/main.js"></script>
</body>
</html>
