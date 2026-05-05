<?php
/**
 * View Projects - HR
 */
require_once '../config/database.php';

// Check if user is logged in and is HR
if (!isLoggedIn() || !hasRole('hr')) {
    redirect('../auth/login.php?role=hr', 'Please login as HR', 'warning');
}

// Get filter parameters
$status_filter = isset($_GET['status']) ? sanitize($conn, $_GET['status']) : '';
$search = isset($_GET['search']) ? sanitize($conn, $_GET['search']) : '';

// Build query
$query = "SELECT p.*, COUNT(pa.id) as assigned_count FROM projects p LEFT JOIN project_assignments pa ON p.id = pa.project_id WHERE p.created_by = " . $_SESSION['user_id'];

if ($status_filter) {
    $query .= " AND p.status = '$status_filter'";
}

if ($search) {
    $query .= " AND (p.project_name LIKE '%$search%' OR p.description LIKE '%$search%')";
}

$query .= " GROUP BY p.id ORDER BY p.created_at DESC";

$projects = $conn->query($query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Projects - SkillTrack Pro</title>
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
                <a href="view_projects.php" class="sidebar-link active">
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
        </ul>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
        <div class="dashboard-header">
            <h1>My Projects</h1>
            <p>View and manage all your projects</p>
        </div>

        <!-- Filters -->
        <div class="glass-card" style="padding: 20px; margin-bottom: 24px;">
            <form method="GET" action="" style="display: flex; gap: 16px; flex-wrap: wrap; align-items: flex-end;">
                <div style="flex: 1; min-width: 200px;">
                    <label class="form-label">Search</label>
                    <div style="position: relative;">
                        <i class="fas fa-search" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: var(--text-muted);"></i>
                        <input type="text" name="search" class="glass-input" style="padding-left: 40px;" placeholder="Search projects..." value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                </div>
                
                <div style="min-width: 150px;">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">All Status</option>
                        <option value="open" <?php echo $status_filter === 'open' ? 'selected' : ''; ?>>Open</option>
                        <option value="in_progress" <?php echo $status_filter === 'in_progress' ? 'selected' : ''; ?>>In Progress</option>
                        <option value="completed" <?php echo $status_filter === 'completed' ? 'selected' : ''; ?>>Completed</option>
                        <option value="closed" <?php echo $status_filter === 'closed' ? 'selected' : ''; ?>>Closed</option>
                    </select>
                </div>
                
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-filter"></i> Filter
                </button>
                
                <a href="view_projects.php" class="btn btn-outline">
                    <i class="fas fa-times"></i> Clear
                </a>
            </form>
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
                    <span><i class="fas fa-calendar"></i> <?php echo $project['start_date'] ? date('M d, Y', strtotime($project['start_date'])) : 'Not set'; ?></span>
                </div>
                
                <div class="project-skills">
                    <?php foreach (array_slice($skills, 0, 4) as $skill): ?>
                        <span class="project-skill"><?php echo htmlspecialchars($skill); ?></span>
                    <?php endforeach; ?>
                    <?php if (count($skills) > 4): ?>
                        <span class="project-skill">+<?php echo count($skills) - 4; ?></span>
                    <?php endif; ?>
                </div>
                
                <div class="progress-container">
                    <div class="progress-header">
                        <span>Progress</span>
                        <span><?php echo round($progress); ?>%</span>
                    </div>
                    <div class="progress-bar">
                        <div class="progress-fill <?php echo $progress >= 100 ? 'success' : 'primary'; ?>" style="width: <?php echo $progress; ?>%"></div>
                    </div>
                </div>
                
                <div class="project-actions">
                    <a href="view_project.php?id=<?php echo $project['id']; ?>" class="btn btn-sm btn-primary">
                        <i class="fas fa-eye"></i> View Details
                    </a>
                    <a href="assign_employee.php?project_id=<?php echo $project['id']; ?>" class="btn btn-sm btn-success <?php echo $progress >= 100 ? 'disabled' : ''; ?>" style="opacity: <?php echo $progress >= 100 ? '0.5' : '1'; ?>; pointer-events: <?php echo $progress >= 100 ? 'none' : 'auto'; ?>">
                        <i class="fas fa-user-plus"></i> Assign
                    </a>
                </div>
            </div>
            <?php endwhile; ?>
            
            <?php if ($projects->num_rows === 0): ?>
            <div class="glass-card" style="padding: 40px; text-align: center; grid-column: 1 / -1;">
                <i class="fas fa-folder-open" style="font-size: 48px; color: var(--text-muted); margin-bottom: 16px;"></i>
                <h3>No Projects Found</h3>
                <p style="color: var(--text-muted);">Create your first project to get started</p>
                <a href="create_project.php" class="btn btn-primary" style="margin-top: 16px;">
                    <i class="fas fa-plus"></i> Create Project
                </a>
            </div>
            <?php endif; ?>
        </div>
    </main>

    <script src="../assets/js/main.js"></script>
</body>
</html>
