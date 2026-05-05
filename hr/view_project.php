<?php
/**
 * View Project Details - HR
 */
require_once '../config/database.php';

// Check if user is logged in and is HR
if (!isLoggedIn() || !hasRole('hr')) {
    redirect('../auth/login.php?role=hr', 'Please login as HR', 'warning');
}

$project_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if (!$project_id) {
    redirect('view_projects.php', 'Project not found', 'danger');
}

// Get project details
$stmt = $conn->prepare("SELECT p.*, u.full_name as created_by_name FROM projects p JOIN users u ON p.created_by = u.id WHERE p.id = ?");
$stmt->bind_param("i", $project_id);
$stmt->execute();
$project = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$project) {
    redirect('view_projects.php', 'Project not found', 'danger');
}

// Get required skills
$skills_result = $conn->query("SELECT s.id, s.skill_name, s.category FROM project_skills ps JOIN skills s ON ps.skill_id = s.id WHERE ps.project_id = $project_id");
$required_skills = [];
$required_skill_ids = [];
while ($skill = $skills_result->fetch_assoc()) {
    $required_skills[] = $skill;
    $required_skill_ids[] = $skill['id'];
}

// Get assigned employees
$assignments = $conn->query("SELECT pa.*, u.full_name, u.email, u.experience_years FROM project_assignments pa JOIN users u ON pa.user_id = u.id WHERE pa.project_id = $project_id ORDER BY pa.assigned_at DESC");

// Calculate skill match for each candidate
$candidates = [];
if (!empty($required_skill_ids)) {
    $skill_ids_str = implode(',', $required_skill_ids);
    
    $candidates_query = "SELECT u.id, u.full_name, u.email, u.experience_years, 
                         COUNT(DISTINCT cs.skill_id) as matching_skills,
                         GROUP_CONCAT(DISTINCT s.skill_name) as matched_skill_names
                         FROM users u 
                         LEFT JOIN candidate_skills cs ON u.id = cs.user_id AND cs.skill_id IN ($skill_ids_str)
                         LEFT JOIN skills s ON cs.skill_id = s.id
                         WHERE u.role = 'candidate' AND u.is_active = 1
                         AND u.id NOT IN (SELECT user_id FROM project_assignments WHERE project_id = $project_id)
                         GROUP BY u.id
                         HAVING matching_skills > 0
                         ORDER BY matching_skills DESC
                         LIMIT 10";
    
    $candidates_result = $conn->query($candidates_query);
    while ($candidate = $candidates_result->fetch_assoc()) {
        $candidate['match_percentage'] = (count($required_skill_ids) > 0) ? round(($candidate['matching_skills'] / count($required_skill_ids)) * 100) : 0;
        
        // Get assessment score
        $user_id = $candidate['id'];
        $assessment_result = $conn->query("SELECT AVG(percentage) as avg_score FROM results WHERE user_id = $user_id");
        $assessment_data = $assessment_result->fetch_assoc();
        $candidate['assessment_score'] = round($assessment_data['avg_score'] ?? 0);
        
        $candidates[] = $candidate;
    }
}

$progress = $project['total_positions'] > 0 ? ($project['filled_positions'] / $project['total_positions']) * 100 : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($project['project_name']); ?> - SkillTrack Pro</title>
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
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <h1><?php echo htmlspecialchars($project['project_name']); ?></h1>
                    <p>Project details and team assignments</p>
                </div>
                <a href="view_projects.php" class="btn btn-outline">
                    <i class="fas fa-arrow-left"></i> Back
                </a>
            </div>
        </div>

        <!-- Project Overview -->
        <div class="dashboard-grid" style="grid-template-columns: 2fr 1fr;">
            <div class="glass-card" style="padding: 24px;">
                <h3 style="margin-bottom: 16px;">Project Details</h3>
                <p style="color: var(--text-secondary); line-height: 1.6; margin-bottom: 20px;">
                    <?php echo nl2br(htmlspecialchars($project['description'])); ?>
                </p>
                
                <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-top: 24px;">
                    <div>
                        <div style="font-size: 12px; color: var(--text-muted); text-transform: uppercase; margin-bottom: 4px;">Status</div>
                        <span class="badge badge-<?php echo $project['status'] === 'open' ? 'success' : ($project['status'] === 'completed' ? 'primary' : 'warning'); ?>">
                            <?php echo ucfirst($project['status']); ?>
                        </span>
                    </div>
                    <div>
                        <div style="font-size: 12px; color: var(--text-muted); text-transform: uppercase; margin-bottom: 4px;">Start Date</div>
                        <div style="font-weight: 500;"><?php echo $project['start_date'] ? date('M d, Y', strtotime($project['start_date'])) : 'Not set'; ?></div>
                    </div>
                    <div>
                        <div style="font-size: 12px; color: var(--text-muted); text-transform: uppercase; margin-bottom: 4px;">End Date</div>
                        <div style="font-weight: 500;"><?php echo $project['end_date'] ? date('M d, Y', strtotime($project['end_date'])) : 'Not set'; ?></div>
                    </div>
                </div>
                
                <div style="margin-top: 24px;">
                    <div style="font-size: 12px; color: var(--text-muted); text-transform: uppercase; margin-bottom: 8px;">Progress</div>
                    <div style="display: flex; align-items: center; gap: 16px;">
                        <div class="progress-bar" style="flex: 1;">
                            <div class="progress-fill <?php echo $progress >= 100 ? 'success' : 'primary'; ?>" style="width: <?php echo $progress; ?>%"></div>
                        </div>
                        <span style="font-weight: 600;"><?php echo $project['filled_positions']; ?>/<?php echo $project['total_positions']; ?></span>
                    </div>
                </div>
            </div>
            
            <div class="glass-card" style="padding: 24px;">
                <h3 style="margin-bottom: 16px;">Required Skills</h3>
                <div class="skills-container">
                    <?php foreach ($required_skills as $skill): ?>
                        <span class="skill-tag">
                            <?php echo htmlspecialchars($skill['skill_name']); ?>
                            <span class="skill-level intermediate"><?php echo htmlspecialchars($skill['category'] ?: 'General'); ?></span>
                        </span>
                    <?php endforeach; ?>
                </div>
                
                <div style="margin-top: 24px; padding-top: 20px; border-top: 1px solid var(--border-glass);">
                    <div style="font-size: 12px; color: var(--text-muted); margin-bottom: 8px;">Created By</div>
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <div style="width: 35px; height: 35px; border-radius: 50%; background: var(--gradient-primary); display: flex; align-items: center; justify-content: center; font-size: 14px; font-weight: 600;">
                            <?php echo strtoupper(substr($project['created_by_name'], 0, 1)); ?>
                        </div>
                        <span><?php echo htmlspecialchars($project['created_by_name']); ?></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Assigned Team -->
        <div class="table-container" style="margin-top: 24px;">
            <div class="table-header">
                <h3>Assigned Team Members</h3>
                <span style="color: var(--text-muted); font-size: 14px;"><?php echo $assignments->num_rows; ?> members</span>
            </div>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Employee</th>
                        <th>Experience</th>
                        <th>Skill Match</th>
                        <th>Assessment Score</th>
                        <th>Status</th>
                        <th>Assigned Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($assignment = $assignments->fetch_assoc()): ?>
                    <tr>
                        <td>
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <div style="width: 35px; height: 35px; border-radius: 50%; background: var(--gradient-primary); display: flex; align-items: center; justify-content: center; font-size: 14px; font-weight: 600;">
                                    <?php echo strtoupper(substr($assignment['full_name'], 0, 1)); ?>
                                </div>
                                <div>
                                    <div style="font-weight: 500;"><?php echo htmlspecialchars($assignment['full_name']); ?></div>
                                    <div style="font-size: 12px; color: var(--text-muted);"><?php echo htmlspecialchars($assignment['email']); ?></div>
                                </div>
                            </div>
                        </td>
                        <td><?php echo $assignment['experience_years']; ?> years</td>
                        <td>
                            <span class="badge badge-<?php echo $assignment['skill_match_percentage'] >= 70 ? 'success' : ($assignment['skill_match_percentage'] >= 50 ? 'warning' : 'danger'); ?>">
                                <?php echo round($assignment['skill_match_percentage']); ?>%
                            </span>
                        </td>
                        <td>
                            <span class="badge badge-<?php echo $assignment['assessment_score'] >= 70 ? 'success' : ($assignment['assessment_score'] >= 50 ? 'warning' : 'danger'); ?>">
                                <?php echo $assignment['assessment_score']; ?>%
                            </span>
                        </td>
                        <td>
                            <span class="badge badge-<?php echo $assignment['status'] === 'approved' ? 'success' : ($assignment['status'] === 'rejected' ? 'danger' : 'warning'); ?>">
                                <?php echo ucfirst($assignment['status']); ?>
                            </span>
                        </td>
                        <td><?php echo date('M d, Y', strtotime($assignment['assigned_at'])); ?></td>
                    </tr>
                    <?php endwhile; ?>
                    <?php if ($assignments->num_rows === 0): ?>
                    <tr>
                        <td colspan="6" style="text-align: center; color: var(--text-muted);">No team members assigned yet</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Recommended Candidates -->
        <?php if ($progress < 100 && !empty($candidates)): ?>
        <div class="table-container" style="margin-top: 24px;">
            <div class="table-header">
                <h3>Recommended Candidates</h3>
                <span style="color: var(--text-muted); font-size: 14px;">Based on skill match</span>
            </div>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Candidate</th>
                        <th>Experience</th>
                        <th>Matching Skills</th>
                        <th>Skill Match</th>
                        <th>Assessment Score</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($candidates as $candidate): ?>
                    <tr>
                        <td>
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <div style="width: 35px; height: 35px; border-radius: 50%; background: var(--gradient-primary); display: flex; align-items: center; justify-content: center; font-size: 14px; font-weight: 600;">
                                    <?php echo strtoupper(substr($candidate['full_name'], 0, 1)); ?>
                                </div>
                                <div>
                                    <div style="font-weight: 500;"><?php echo htmlspecialchars($candidate['full_name']); ?></div>
                                    <div style="font-size: 12px; color: var(--text-muted);"><?php echo htmlspecialchars($candidate['email']); ?></div>
                                </div>
                            </div>
                        </td>
                        <td><?php echo $candidate['experience_years']; ?> years</td>
                        <td>
                            <span class="badge badge-info"><?php echo $candidate['matching_skills']; ?>/<?php echo count($required_skill_ids); ?></span>
                            <div style="font-size: 11px; color: var(--text-muted); margin-top: 4px;">
                                <?php echo htmlspecialchars(substr($candidate['matched_skill_names'], 0, 30)) . (strlen($candidate['matched_skill_names']) > 30 ? '...' : ''); ?>
                            </div>
                        </td>
                        <td>
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <div class="progress-bar" style="width: 60px; height: 6px;">
                                    <div class="progress-fill <?php echo $candidate['match_percentage'] >= 70 ? 'success' : ($candidate['match_percentage'] >= 50 ? 'warning' : 'danger'); ?>" style="width: <?php echo $candidate['match_percentage']; ?>%"></div>
                                </div>
                                <span class="badge badge-<?php echo $candidate['match_percentage'] >= 70 ? 'success' : ($candidate['match_percentage'] >= 50 ? 'warning' : 'danger'); ?>">
                                    <?php echo $candidate['match_percentage']; ?>%
                                </span>
                            </div>
                        </td>
                        <td>
                            <span class="badge badge-<?php echo $candidate['assessment_score'] >= 70 ? 'success' : ($candidate['assessment_score'] >= 50 ? 'warning' : 'danger'); ?>">
                                <?php echo $candidate['assessment_score']; ?>%
                            </span>
                        </td>
                        <td>
                            <a href="assign_employee.php?project_id=<?php echo $project_id; ?>&user_id=<?php echo $candidate['id']; ?>" class="btn btn-sm btn-success">
                                <i class="fas fa-user-plus"></i> Assign
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </main>

    <script src="../assets/js/main.js"></script>
</body>
</html>
