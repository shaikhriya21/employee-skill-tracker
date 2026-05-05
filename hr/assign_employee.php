<?php
/**
 * Assign Employee to Project - HR
 */
require_once '../config/database.php';

// Check if user is logged in and is HR
if (!isLoggedIn() || !hasRole('hr')) {
    redirect('../auth/login.php?role=hr', 'Please login as HR', 'warning');
}

$message = '';
$error = '';

// Handle approval/rejection from dashboard
if (isset($_GET['action']) && isset($_GET['id'])) {
    $assignment_id = intval($_GET['id']);
    $action = $_GET['action'];
    
    if ($action === 'approve' || $action === 'reject') {
        $status = ($action === 'approve') ? 'approved' : 'rejected';
        $stmt = $conn->prepare("UPDATE project_assignments SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $status, $assignment_id);
        
        if ($stmt->execute()) {
            // If approved, update filled positions
            if ($action === 'approve') {
                $project_stmt = $conn->prepare("UPDATE projects p JOIN project_assignments pa ON p.id = pa.project_id SET p.filled_positions = p.filled_positions + 1 WHERE pa.id = ?");
                $project_stmt->bind_param("i", $assignment_id);
                $project_stmt->execute();
                $project_stmt->close();
            }
            
            $message = 'Assignment ' . $status . ' successfully';
            logActivity($conn, $_SESSION['user_id'], 'Assignment ' . ucfirst($status), "Assignment ID: $assignment_id");
        } else {
            $error = 'Failed to update assignment';
        }
        $stmt->close();
    }
}

// Handle new assignment
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['assign_employee'])) {
    $project_id = intval($_POST['project_id']);
    $user_id = intval($_POST['user_id']);
    $notes = sanitize($conn, $_POST['notes']);
    
    // Check if project has available positions
    $project_stmt = $conn->prepare("SELECT total_positions, filled_positions FROM projects WHERE id = ?");
    $project_stmt->bind_param("i", $project_id);
    $project_stmt->execute();
    $project = $project_stmt->get_result()->fetch_assoc();
    $project_stmt->close();
    
    if ($project['filled_positions'] >= $project['total_positions']) {
        $error = 'All positions for this project have been filled';
    } else {
        // Calculate skill match percentage
        $required_skills_query = $conn->query("SELECT skill_id FROM project_skills WHERE project_id = $project_id");
        $required_skills = [];
        while ($row = $required_skills_query->fetch_assoc()) {
            $required_skills[] = $row['skill_id'];
        }
        
        $match_percentage = 0;
        if (!empty($required_skills)) {
            $user_skills_query = $conn->query("SELECT COUNT(*) as match_count FROM candidate_skills WHERE user_id = $user_id AND skill_id IN (" . implode(',', $required_skills) . ")");
            $match_data = $user_skills_query->fetch_assoc();
            $match_percentage = round(($match_data['match_count'] / count($required_skills)) * 100);
        }
        
        // Get assessment score
        $assessment_result = $conn->query("SELECT AVG(percentage) as avg_score FROM results WHERE user_id = $user_id");
        $assessment_data = $assessment_result->fetch_assoc();
        $assessment_score = round($assessment_data['avg_score'] ?? 0);
        
        // Determine status based on criteria
        $status = ($match_percentage >= 50 && $assessment_score >= 60) ? 'approved' : 'pending';
        
        // Insert assignment
        $stmt = $conn->prepare("INSERT INTO project_assignments (project_id, user_id, skill_match_percentage, assessment_score, status, assigned_by, notes) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("iiiisss", $project_id, $user_id, $match_percentage, $assessment_score, $status, $_SESSION['user_id'], $notes);
        
        if ($stmt->execute()) {
            // If auto-approved, update filled positions
            if ($status === 'approved') {
                $conn->query("UPDATE projects SET filled_positions = filled_positions + 1 WHERE id = $project_id");
            }
            
            $message = 'Employee assigned successfully' . ($status === 'approved' ? ' (Auto-approved)' : ' (Pending approval)');
            logActivity($conn, $_SESSION['user_id'], 'Employee Assigned', "Project: $project_id, User: $user_id");
        } else {
            $error = 'Failed to assign employee';
        }
        $stmt->close();
    }
}

// Get all projects
$projects = $conn->query("SELECT id, project_name, total_positions, filled_positions FROM projects WHERE status = 'open' ORDER BY project_name");

// Get all active employees
$employees = $conn->query("SELECT id, full_name, email FROM users WHERE role = 'candidate' AND is_active = 1 ORDER BY full_name");

// Get pending assignments
$pending_assignments = $conn->query("SELECT pa.*, p.project_name, u.full_name, u.email FROM project_assignments pa JOIN projects p ON pa.project_id = p.id JOIN users u ON pa.user_id = u.id WHERE pa.status = 'pending' ORDER BY pa.assigned_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assign Employees - SkillTrack Pro</title>
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
                <a href="assign_employee.php" class="sidebar-link active">
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
            <h1>Assign Employees</h1>
            <p>Assign employees to projects based on their skills</p>
        </div>

        <div class="dashboard-grid" style="grid-template-columns: 1fr 1fr;">
            <!-- Assignment Form -->
            <div class="glass-card" style="padding: 24px;">
                <h3 style="margin-bottom: 20px;"><i class="fas fa-user-plus"></i> New Assignment</h3>
                
                <form method="POST" action="">
                    <div class="form-group">
                        <label class="form-label">Select Project *</label>
                        <select name="project_id" class="form-select" required>
                            <option value="">Choose a project...</option>
                            <?php while ($project = $projects->fetch_assoc()): 
                                $available = $project['total_positions'] - $project['filled_positions'];
                            ?>
                                <option value="<?php echo $project['id']; ?>">
                                    <?php echo htmlspecialchars($project['project_name']); ?> 
                                    (<?php echo $available; ?> position<?php echo $available !== 1 ? 's' : ''; ?> available)
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Select Employee *</label>
                        <select name="user_id" class="form-select" required>
                            <option value="">Choose an employee...</option>
                            <?php while ($employee = $employees->fetch_assoc()): ?>
                                <option value="<?php echo $employee['id']; ?>">
                                    <?php echo htmlspecialchars($employee['full_name']); ?> (<?php echo htmlspecialchars($employee['email']); ?>)
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Notes</label>
                        <textarea name="notes" class="glass-input" rows="3" placeholder="Any additional notes..."></textarea>
                    </div>
                    
                    <button type="submit" name="assign_employee" class="btn btn-primary" style="width: 100%;">
                        <i class="fas fa-user-plus"></i> Assign Employee
                    </button>
                </form>
                
                <div style="margin-top: 20px; padding: 16px; background: rgba(255,255,255,0.03); border-radius: var(--radius-md);">
                    <h4 style="font-size: 14px; margin-bottom: 10px;"><i class="fas fa-info-circle"></i> Auto-Approval Criteria</h4>
                    <ul style="font-size: 13px; color: var(--text-muted); padding-left: 20px; margin: 0;">
                        <li>Skill match ≥ 50%</li>
                        <li>Assessment score ≥ 60%</li>
                    </ul>
                </div>
            </div>
            
            <!-- Pending Approvals -->
            <div class="table-container">
                <div class="table-header">
                    <h3>Pending Approvals</h3>
                    <span class="badge badge-warning"><?php echo $pending_assignments->num_rows; ?></span>
                </div>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Employee</th>
                            <th>Project</th>
                            <th>Match</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($assignment = $pending_assignments->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($assignment['full_name']); ?></td>
                            <td><?php echo htmlspecialchars($assignment['project_name']); ?></td>
                            <td>
                                <span class="badge badge-<?php echo $assignment['skill_match_percentage'] >= 70 ? 'success' : ($assignment['skill_match_percentage'] >= 50 ? 'warning' : 'danger'); ?>">
                                    <?php echo round($assignment['skill_match_percentage']); ?>%
                                </span>
                            </td>
                            <td>
                                <div style="display: flex; gap: 8px;">
                                    <a href="?action=approve&id=<?php echo $assignment['id']; ?>" class="btn btn-sm btn-success" onclick="return confirm('Approve this assignment?')">
                                        <i class="fas fa-check"></i>
                                    </a>
                                    <a href="?action=reject&id=<?php echo $assignment['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Reject this assignment?')">
                                        <i class="fas fa-times"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                        <?php if ($pending_assignments->num_rows === 0): ?>
                        <tr>
                            <td colspan="4" style="text-align: center; color: var(--text-muted);">No pending approvals</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <script src="../assets/js/main.js"></script>
</body>
</html>
