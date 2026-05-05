<?php
/**
 * Manage Assessments - Admin
 */
require_once '../config/database.php';

// Check if user is logged in and is admin
if (!isLoggedIn() || !hasRole('admin')) {
    redirect('../auth/login.php?role=admin', 'Please login as admin', 'warning');
}

$message = '';
$error = '';

// Handle Add Assessment
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_assessment'])) {
    $title = sanitize($conn, $_POST['title']);
    $description = sanitize($conn, $_POST['description']);
    $skill_id = intval($_POST['skill_id']);
    $duration = intval($_POST['duration']);
    $passing_score = intval($_POST['passing_score']);
    
    if (empty($title)) {
        $error = 'Assessment title is required';
    } else {
        $stmt = $conn->prepare("INSERT INTO assessments (title, description, skill_id, duration_minutes, passing_score, created_by) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssiiii", $title, $description, $skill_id, $duration, $passing_score, $_SESSION['user_id']);
        
        if ($stmt->execute()) {
            $message = 'Assessment created successfully';
            logActivity($conn, $_SESSION['user_id'], 'Assessment Created', "Assessment: $title");
        } else {
            $error = 'Failed to create assessment';
        }
        $stmt->close();
    }
}

// Handle Toggle Status
if (isset($_GET['toggle']) && is_numeric($_GET['toggle'])) {
    $assessment_id = intval($_GET['toggle']);
    
    $stmt = $conn->prepare("UPDATE assessments SET is_active = NOT is_active WHERE id = ?");
    $stmt->bind_param("i", $assessment_id);
    
    if ($stmt->execute()) {
        $message = 'Assessment status updated';
    } else {
        $error = 'Failed to update status';
    }
    $stmt->close();
}

// Handle Delete Assessment
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $assessment_id = intval($_GET['delete']);
    
    $stmt = $conn->prepare("DELETE FROM assessments WHERE id = ?");
    $stmt->bind_param("i", $assessment_id);
    
    if ($stmt->execute()) {
        $message = 'Assessment deleted successfully';
        logActivity($conn, $_SESSION['user_id'], 'Assessment Deleted', "Assessment ID: $assessment_id");
    } else {
        $error = 'Failed to delete assessment';
    }
    $stmt->close();
}

// Get all assessments with question count
$assessments = $conn->query("SELECT a.*, s.skill_name, u.full_name as created_by_name, COUNT(q.id) as question_count FROM assessments a LEFT JOIN skills s ON a.skill_id = s.id LEFT JOIN users u ON a.created_by = u.id LEFT JOIN questions q ON a.id = q.assessment_id GROUP BY a.id ORDER BY a.created_at DESC");

// Get skills for dropdown
$skills = $conn->query("SELECT id, skill_name FROM skills ORDER BY skill_name");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Assessments - SkillTrack Pro</title>
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
                <a href="manage_assessments.php" class="sidebar-link active">
                    <i class="fas fa-clipboard-check"></i>
                    Assessments
                </a>
            </li>
            <li class="sidebar-item">
                <a href="manage_projects.php" class="sidebar-link">
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
            <h1>Manage Assessments</h1>
            <p>Create and manage skill assessments</p>
        </div>

        <!-- Add Assessment Form -->
        <div class="glass-card" style="padding: 24px; margin-bottom: 24px;">
            <h3 style="margin-bottom: 20px;"><i class="fas fa-plus"></i> Create New Assessment</h3>
            
            <form method="POST" action="">
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px;">
                    <div class="form-group">
                        <label class="form-label">Assessment Title *</label>
                        <input type="text" name="title" class="glass-input" placeholder="e.g., Web Development Fundamentals" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Related Skill</label>
                        <select name="skill_id" class="form-select">
                            <option value="">General Assessment</option>
                            <?php while ($skill = $skills->fetch_assoc()): ?>
                                <option value="<?php echo $skill['id']; ?>"><?php echo htmlspecialchars($skill['skill_name']); ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Duration (minutes)</label>
                        <input type="number" name="duration" class="glass-input" value="30" min="5" max="180">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Passing Score (%)</label>
                        <input type="number" name="passing_score" class="glass-input" value="60" min="0" max="100">
                    </div>
                </div>
                
                <div class="form-group" style="margin-top: 20px;">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="glass-input" rows="3" placeholder="Brief description of the assessment"></textarea>
                </div>
                
                <button type="submit" name="add_assessment" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Create Assessment
                </button>
            </form>
        </div>

        <!-- Assessments List -->
        <div class="table-container">
            <div class="table-header">
                <h3>All Assessments</h3>
                <span style="color: var(--text-muted); font-size: 14px;"><?php echo $assessments->num_rows; ?> assessments</span>
            </div>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Skill</th>
                        <th>Questions</th>
                        <th>Duration</th>
                        <th>Passing</th>
                        <th>Status</th>
                        <th>Created By</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($assessment = $assessments->fetch_assoc()): ?>
                    <tr>
                        <td>
                            <strong><?php echo htmlspecialchars($assessment['title']); ?></strong>
                            <div style="font-size: 12px; color: var(--text-muted); margin-top: 4px;">
                                <?php echo htmlspecialchars(substr($assessment['description'], 0, 50)) . (strlen($assessment['description']) > 50 ? '...' : ''); ?>
                            </div>
                        </td>
                        <td><?php echo htmlspecialchars($assessment['skill_name'] ?: 'General'); ?></td>
                        <td>
                            <span class="badge badge-info"><?php echo $assessment['question_count']; ?> questions</span>
                        </td>
                        <td><?php echo $assessment['duration_minutes']; ?> min</td>
                        <td><?php echo $assessment['passing_score']; ?>%</td>
                        <td>
                            <span class="badge badge-<?php echo $assessment['is_active'] ? 'success' : 'danger'; ?>">
                                <?php echo $assessment['is_active'] ? 'Active' : 'Inactive'; ?>
                            </span>
                        </td>
                        <td><?php echo htmlspecialchars($assessment['created_by_name']); ?></td>
                        <td>
                            <div style="display: flex; gap: 8px;">
                                <a href="manage_questions.php?assessment_id=<?php echo $assessment['id']; ?>" class="btn btn-sm btn-primary" title="Manage Questions">
                                    <i class="fas fa-question-circle"></i>
                                </a>
                                <a href="?toggle=<?php echo $assessment['id']; ?>" class="btn btn-sm btn-<?php echo $assessment['is_active'] ? 'warning' : 'success'; ?>" title="Toggle Status">
                                    <i class="fas fa-<?php echo $assessment['is_active'] ? 'ban' : 'check'; ?>"></i>
                                </a>
                                <a href="?delete=<?php echo $assessment['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this assessment?')" title="Delete">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </main>

    <script src="../assets/js/main.js"></script>
</body>
</html>
