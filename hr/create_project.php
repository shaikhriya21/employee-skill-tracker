<?php
/**
 * Create Project - HR
 */
require_once '../config/database.php';

// Check if user is logged in and is HR
if (!isLoggedIn() || !hasRole('hr')) {
    redirect('../auth/login.php?role=hr', 'Please login as HR', 'warning');
}

$message = '';
$error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $project_name = sanitize($conn, $_POST['project_name']);
    $description = sanitize($conn, $_POST['description']);
    $total_positions = intval($_POST['total_positions']);
    $start_date = sanitize($conn, $_POST['start_date']);
    $end_date = sanitize($conn, $_POST['end_date']);
    $required_skills = isset($_POST['required_skills']) ? $_POST['required_skills'] : [];
    
    if (empty($project_name)) {
        $error = 'Project name is required';
    } elseif ($total_positions < 1) {
        $error = 'Total positions must be at least 1';
    } elseif (empty($required_skills)) {
        $error = 'Please select at least one required skill';
    } else {
        // Insert project
        $stmt = $conn->prepare("INSERT INTO projects (project_name, description, total_positions, start_date, end_date, created_by) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssissi", $project_name, $description, $total_positions, $start_date, $end_date, $_SESSION['user_id']);
        
        if ($stmt->execute()) {
            $project_id = $stmt->insert_id;
            
            // Insert required skills
            $skill_stmt = $conn->prepare("INSERT INTO project_skills (project_id, skill_id, is_mandatory) VALUES (?, ?, 1)");
            foreach ($required_skills as $skill_id) {
                $skill_id = intval($skill_id);
                $skill_stmt->bind_param("ii", $project_id, $skill_id);
                $skill_stmt->execute();
            }
            $skill_stmt->close();
            
            $message = 'Project created successfully';
            logActivity($conn, $_SESSION['user_id'], 'Project Created', "Project: $project_name");
        } else {
            $error = 'Failed to create project';
        }
        $stmt->close();
    }
}

// Get all skills
$skills = $conn->query("SELECT id, skill_name, category FROM skills ORDER BY category, skill_name");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Project - SkillTrack Pro</title>
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
                <a href="create_project.php" class="sidebar-link active">
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
            <h1>Create New Project</h1>
            <p>Set up a new project and define required skills</p>
        </div>

        <div class="glass-card" style="padding: 30px; max-width: 900px;">
            <form method="POST" action="" data-validate>
                <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px;">
                    <div class="form-group" style="grid-column: 1 / -1;">
                        <label class="form-label">Project Name *</label>
                        <div style="position: relative;">
                            <i class="fas fa-project-diagram" style="position: absolute; left: 16px; top: 50%; transform: translateY(-50%); color: var(--text-muted);"></i>
                            <input type="text" name="project_name" class="glass-input" style="padding-left: 45px;" placeholder="e.g., AI Website Development" required>
                        </div>
                    </div>
                    
                    <div class="form-group" style="grid-column: 1 / -1;">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="glass-input" rows="4" placeholder="Describe the project objectives and requirements..."></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Total Positions *</label>
                        <div style="position: relative;">
                            <i class="fas fa-users" style="position: absolute; left: 16px; top: 50%; transform: translateY(-50%); color: var(--text-muted);"></i>
                            <input type="number" name="total_positions" class="glass-input" style="padding-left: 45px;" value="1" min="1" max="50" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Start Date</label>
                        <input type="date" name="start_date" class="glass-input">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">End Date</label>
                        <input type="date" name="end_date" class="glass-input">
                    </div>
                </div>
                
                <div class="form-group" style="margin-top: 24px;">
                    <label class="form-label">Required Skills *</label>
                    <p style="font-size: 13px; color: var(--text-muted); margin-bottom: 12px;">Select the skills required for this project</p>
                    
                    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 12px; max-height: 300px; overflow-y: auto; padding: 16px; background: rgba(255,255,255,0.03); border-radius: var(--radius-md); border: 1px solid var(--border-glass);">
                        <?php 
                        $current_category = '';
                        while ($skill = $skills->fetch_assoc()): 
                            if ($skill['category'] !== $current_category):
                                $current_category = $skill['category'];
                        ?>
                            <div style="grid-column: 1 / -1; font-size: 12px; text-transform: uppercase; color: var(--primary-light); margin-top: 10px; font-weight: 600;">
                                <?php echo htmlspecialchars($current_category ?: 'Other'); ?>
                            </div>
                        <?php endif; ?>
                            <label style="display: flex; align-items: center; gap: 10px; cursor: pointer; padding: 8px; border-radius: var(--radius-sm); transition: var(--transition-fast);" onmouseover="this.style.background='rgba(255,255,255,0.05)'" onmouseout="this.style.background='transparent'">
                                <input type="checkbox" name="required_skills[]" value="<?php echo $skill['id']; ?>" style="width: 18px; height: 18px; accent-color: var(--primary-color);">
                                <span style="font-size: 14px;"><?php echo htmlspecialchars($skill['skill_name']); ?></span>
                            </label>
                        <?php endwhile; ?>
                    </div>
                </div>
                
                <div style="display: flex; gap: 16px; margin-top: 30px;">
                    <button type="submit" class="btn btn-primary btn-lg" style="flex: 1;">
                        <i class="fas fa-plus"></i> Create Project
                    </button>
                    <a href="dashboard.php" class="btn btn-outline btn-lg">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </main>

    <script src="../assets/js/main.js"></script>
</body>
</html>
