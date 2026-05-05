<?php
/**
 * Employee Skills - HR
 */
require_once '../config/database.php';

// Check if user is logged in and is HR
if (!isLoggedIn() || !hasRole('hr')) {
    redirect('../auth/login.php?role=hr', 'Please login as HR', 'warning');
}

// Get search parameter
$search = isset($_GET['search']) ? sanitize($conn, $_GET['search']) : '';

// Build query
$query = "SELECT u.id, u.full_name, u.email, u.experience_years, COUNT(cs.id) as skill_count FROM users u LEFT JOIN candidate_skills cs ON u.id = cs.user_id WHERE u.role = 'candidate' AND u.is_active = 1";

if ($search) {
    $query .= " AND (u.full_name LIKE '%$search%' OR u.email LIKE '%$search%')";
}

$query .= " GROUP BY u.id ORDER BY skill_count DESC";

$employees = $conn->query($query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Skills - SkillTrack Pro</title>
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
                <a href="employee_skills.php" class="sidebar-link active">
                    <i class="fas fa-brain"></i>
                    Employee Skills
                </a>
            </li>
        </ul>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
        <div class="dashboard-header">
            <h1>Employee Skills</h1>
            <p>View employee skills and expertise</p>
        </div>

        <!-- Search -->
        <div class="glass-card" style="padding: 20px; margin-bottom: 24px;">
            <form method="GET" action="" style="display: flex; gap: 16px;">
                <div style="flex: 1;">
                    <div style="position: relative;">
                        <i class="fas fa-search" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: var(--text-muted);"></i>
                        <input type="text" name="search" class="glass-input" style="padding-left: 40px;" placeholder="Search employees..." value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search"></i> Search
                </button>
                <a href="employee_skills.php" class="btn btn-outline">
                    <i class="fas fa-times"></i> Clear
                </a>
            </form>
        </div>

        <!-- Employees Grid -->
        <div class="dashboard-grid" style="grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));">
            <?php while ($employee = $employees->fetch_assoc()): 
                // Get employee skills
                $user_id = $employee['id'];
                $skills_result = $conn->query("SELECT s.skill_name, cs.proficiency_level FROM candidate_skills cs JOIN skills s ON cs.skill_id = s.id WHERE cs.user_id = $user_id ORDER BY cs.years_experience DESC LIMIT 5");
                
                // Get assessment score
                $assessment_result = $conn->query("SELECT AVG(percentage) as avg_score FROM results WHERE user_id = $user_id");
                $assessment_data = $assessment_result->fetch_assoc();
                $avg_score = round($assessment_data['avg_score'] ?? 0);
            ?>
            <div class="glass-card" style="padding: 24px;">
                <div style="display: flex; align-items: center; gap: 16px; margin-bottom: 16px;">
                    <div style="width: 60px; height: 60px; border-radius: 50%; background: var(--gradient-primary); display: flex; align-items: center; justify-content: center; font-size: 24px; font-weight: 600;">
                        <?php echo strtoupper(substr($employee['full_name'], 0, 1)); ?>
                    </div>
                    <div>
                        <h4 style="margin: 0;"><?php echo htmlspecialchars($employee['full_name']); ?></h4>
                        <p style="margin: 4px 0 0; font-size: 13px; color: var(--text-muted);"><?php echo htmlspecialchars($employee['email']); ?></p>
                    </div>
                </div>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 16px;">
                    <div style="padding: 12px; background: rgba(255,255,255,0.03); border-radius: var(--radius-md); text-align: center;">
                        <div style="font-size: 20px; font-weight: 700;"><?php echo $employee['skill_count']; ?></div>
                        <div style="font-size: 11px; color: var(--text-muted);">Skills</div>
                    </div>
                    <div style="padding: 12px; background: rgba(255,255,255,0.03); border-radius: var(--radius-md); text-align: center;">
                        <div style="font-size: 20px; font-weight: 700;"><?php echo $avg_score; ?>%</div>
                        <div style="font-size: 11px; color: var(--text-muted);">Avg Score</div>
                    </div>
                </div>
                
                <div style="margin-bottom: 16px;">
                    <div style="font-size: 12px; color: var(--text-muted); margin-bottom: 8px;">Top Skills</div>
                    <div class="skills-container">
                        <?php while ($skill = $skills_result->fetch_assoc()): ?>
                            <span class="skill-tag" style="font-size: 11px; padding: 4px 10px;">
                                <?php echo htmlspecialchars($skill['skill_name']); ?>
                            </span>
                        <?php endwhile; ?>
                        <?php if ($skills_result->num_rows === 0): ?>
                            <span style="font-size: 12px; color: var(--text-muted);">No skills added</span>
                        <?php endif; ?>
                    </div>
                </div>
                
                <a href="assign_employee.php?user_id=<?php echo $employee['id']; ?>" class="btn btn-sm btn-primary" style="width: 100%;">
                    <i class="fas fa-user-plus"></i> Assign to Project
                </a>
            </div>
            <?php endwhile; ?>
        </div>
    </main>

    <script src="../assets/js/main.js"></script>
</body>
</html>
