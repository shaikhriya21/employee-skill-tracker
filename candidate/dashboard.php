<?php
/**
 * Candidate Dashboard
 */
require_once '../config/database.php';

// Check if user is logged in and is candidate
if (!isLoggedIn() || !hasRole('candidate')) {
    redirect('../auth/login.php?role=candidate', 'Please login as employee', 'warning');
}

$user_id = $_SESSION['user_id'];

// Get user details
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Get statistics
$stats = [
    'total_skills' => 0,
    'total_assessments' => 0,
    'passed_assessments' => 0,
    'project_assignments' => 0
];

// Count skills
$result = $conn->query("SELECT COUNT(*) as count FROM candidate_skills WHERE user_id = $user_id");
if ($result) $stats['total_skills'] = $result->fetch_assoc()['count'];

// Count assessments taken
$result = $conn->query("SELECT COUNT(*) as count FROM results WHERE user_id = $user_id");
if ($result) $stats['total_assessments'] = $result->fetch_assoc()['count'];

// Count passed assessments
$result = $conn->query("SELECT COUNT(*) as count FROM results WHERE user_id = $user_id AND status = 'passed'");
if ($result) $stats['passed_assessments'] = $result->fetch_assoc()['count'];

// Count project assignments
$result = $conn->query("SELECT COUNT(*) as count FROM project_assignments WHERE user_id = $user_id AND status = 'approved'");
if ($result) $stats['project_assignments'] = $result->fetch_assoc()['count'];

// Get user's skills
$skills = $conn->query("SELECT s.skill_name, cs.proficiency_level, cs.years_experience FROM candidate_skills cs JOIN skills s ON cs.skill_id = s.id WHERE cs.user_id = $user_id ORDER BY cs.years_experience DESC");

// Get available assessments
$available_assessments = $conn->query("SELECT a.*, s.skill_name FROM assessments a LEFT JOIN skills s ON a.skill_id = s.id WHERE a.is_active = 1 AND a.id NOT IN (SELECT assessment_id FROM results WHERE user_id = $user_id) ORDER BY a.created_at DESC LIMIT 5");

// Get assessment results
$assessment_results = $conn->query("SELECT r.*, a.title as assessment_title FROM results r JOIN assessments a ON r.assessment_id = a.id WHERE r.user_id = $user_id ORDER BY r.completed_at DESC LIMIT 5");

// Get project assignments
$assignments = $conn->query("SELECT pa.*, p.project_name, p.description FROM project_assignments pa JOIN projects p ON pa.project_id = p.id WHERE pa.user_id = $user_id ORDER BY pa.assigned_at DESC LIMIT 5");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Dashboard - SkillTrack Pro</title>
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
                <a href="dashboard.php" class="sidebar-link active">
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
                <a href="results.php" class="sidebar-link">
                    <i class="fas fa-chart-bar"></i>
                    My Results
                </a>
            </li>
        </ul>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
        <?php echo showMessage(); ?>
        
        <div class="dashboard-header">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <h1>Welcome, <?php echo htmlspecialchars($_SESSION['full_name']); ?>!</h1>
                    <p>Manage your skills and track your progress</p>
                </div>
                <div style="text-align: right;">
                    <div style="font-size: 14px; color: var(--text-muted);">Profile Completion</div>
                    <div style="display: flex; align-items: center; gap: 10px; margin-top: 5px;">
                        <div class="progress-bar" style="width: 150px; height: 8px;">
                            <div class="progress-fill primary" style="width: <?php echo $user['profile_image'] ? 80 : 60; ?>%"></div>
                        </div>
                        <span style="font-weight: 600;"><?php echo $user['profile_image'] ? 80 : 60; ?>%</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stats Grid -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon primary">
                    <i class="fas fa-brain"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo $stats['total_skills']; ?></h3>
                    <p>My Skills</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon success">
                    <i class="fas fa-clipboard-check"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo $stats['total_assessments']; ?></h3>
                    <p>Assessments Taken</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon warning">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo $stats['passed_assessments']; ?></h3>
                    <p>Passed</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon danger">
                    <i class="fas fa-project-diagram"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo $stats['project_assignments']; ?></h3>
                    <p>Project Assignments</p>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="glass-card" style="padding: 24px; margin-bottom: 24px;">
            <h3 style="margin-bottom: 20px;">Quick Actions</h3>
            <div style="display: flex; gap: 16px; flex-wrap: wrap;">
                <a href="skills.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Add Skills
                </a>
                <a href="take_assessment.php" class="btn btn-success">
                    <i class="fas fa-clipboard-check"></i> Take Assessment
                </a>
                <a href="profile.php" class="btn btn-secondary">
                    <i class="fas fa-user-edit"></i> Update Profile
                </a>
            </div>
        </div>

        <!-- Dashboard Content -->
        <div class="dashboard-grid">
            <!-- My Skills -->
            <div class="dashboard-card">
                <h3><i class="fas fa-brain"></i> My Skills</h3>
                <div class="skills-container" style="margin-top: 16px;">
                    <?php while ($skill = $skills->fetch_assoc()): ?>
                        <span class="skill-tag">
                            <?php echo htmlspecialchars($skill['skill_name']); ?>
                            <span class="skill-level <?php echo $skill['proficiency_level']; ?>"><?php echo ucfirst($skill['proficiency_level']); ?></span>
                        </span>
                    <?php endwhile; ?>
                    <?php if ($skills->num_rows === 0): ?>
                        <p style="color: var(--text-muted);">No skills added yet. <a href="skills.php">Add your first skill</a></p>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Available Assessments -->
            <div class="dashboard-card">
                <h3><i class="fas fa-clipboard-list"></i> Available Assessments</h3>
                <div style="margin-top: 16px;">
                    <?php while ($assessment = $available_assessments->fetch_assoc()): ?>
                        <div style="padding: 16px; background: rgba(255,255,255,0.03); border-radius: var(--radius-md); margin-bottom: 12px;">
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <div>
                                    <div style="font-weight: 500;"><?php echo htmlspecialchars($assessment['title']); ?></div>
                                    <div style="font-size: 12px; color: var(--text-muted); margin-top: 4px;">
                                        <i class="fas fa-clock"></i> <?php echo $assessment['duration_minutes']; ?> min | 
                                        <i class="fas fa-question-circle"></i> <?php echo $assessment['total_questions']; ?> questions
                                    </div>
                                </div>
                                <a href="take_assessment.php?id=<?php echo $assessment['id']; ?>" class="btn btn-sm btn-primary">Start</a>
                            </div>
                        </div>
                    <?php endwhile; ?>
                    <?php if ($available_assessments->num_rows === 0): ?>
                        <p style="color: var(--text-muted);">No assessments available at the moment.</p>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Recent Results -->
            <div class="dashboard-card">
                <h3><i class="fas fa-chart-line"></i> Recent Results</h3>
                <div style="margin-top: 16px;">
                    <?php while ($result = $assessment_results->fetch_assoc()): ?>
                        <div style="padding: 16px; background: rgba(255,255,255,0.03); border-radius: var(--radius-md); margin-bottom: 12px;">
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <div>
                                    <div style="font-weight: 500;"><?php echo htmlspecialchars($result['assessment_title']); ?></div>
                                    <div style="font-size: 12px; color: var(--text-muted); margin-top: 4px;">
                                        <?php echo date('M d, Y', strtotime($result['completed_at'])); ?>
                                    </div>
                                </div>
                                <div style="text-align: right;">
                                    <span class="badge badge-<?php echo $result['status'] === 'passed' ? 'success' : 'danger'; ?>">
                                        <?php echo ucfirst($result['status']); ?>
                                    </span>
                                    <div style="font-size: 18px; font-weight: 700; margin-top: 4px;">
                                        <?php echo $result['percentage']; ?>%
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                    <?php if ($assessment_results->num_rows === 0): ?>
                        <p style="color: var(--text-muted);">No assessment results yet. <a href="take_assessment.php">Take an assessment</a></p>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Project Assignments -->
            <div class="dashboard-card">
                <h3><i class="fas fa-tasks"></i> Project Assignments</h3>
                <div style="margin-top: 16px;">
                    <?php while ($assignment = $assignments->fetch_assoc()): ?>
                        <div style="padding: 16px; background: rgba(255,255,255,0.03); border-radius: var(--radius-md); margin-bottom: 12px;">
                            <div style="font-weight: 500;"><?php echo htmlspecialchars($assignment['project_name']); ?></div>
                            <div style="font-size: 12px; color: var(--text-muted); margin-top: 4px;">
                                <?php echo htmlspecialchars(substr($assignment['description'], 0, 60)) . (strlen($assignment['description']) > 60 ? '...' : ''); ?>
                            </div>
                            <div style="margin-top: 8px;">
                                <span class="badge badge-<?php echo $assignment['status'] === 'approved' ? 'success' : ($assignment['status'] === 'rejected' ? 'danger' : 'warning'); ?>">
                                    <?php echo ucfirst($assignment['status']); ?>
                                </span>
                            </div>
                        </div>
                    <?php endwhile; ?>
                    <?php if ($assignments->num_rows === 0): ?>
                        <p style="color: var(--text-muted);">No project assignments yet.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>

    <script src="../assets/js/main.js"></script>
</body>
</html>
