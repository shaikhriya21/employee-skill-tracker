<?php
/**
 * Manage Skills - Admin
 */
require_once '../config/database.php';

// Check if user is logged in and is admin
if (!isLoggedIn() || !hasRole('admin')) {
    header("Location: ../auth/login.php?role=admin");
    exit();
}

$message = '';
$error = '';

// Handle Add Skill
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_skill'])) {
    $skill_name = sanitize($conn, $_POST['skill_name']);
    $category = sanitize($conn, $_POST['category']);
    $description = sanitize($conn, $_POST['description']);
    
    if (empty($skill_name)) {
        $error = 'Skill name is required';
    } else {
        $stmt = $conn->prepare("INSERT INTO skills (skill_name, category, description) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $skill_name, $category, $description);
        
        if ($stmt->execute()) {
            $message = 'Skill added successfully';
            logActivity($conn, $_SESSION['user_id'], 'Skill Added', "Skill: $skill_name");
        } else {
            $error = 'Skill already exists or failed to add';
        }
        $stmt->close();
    }
}

// Handle Edit Skill
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_skill'])) {
    $skill_id = intval($_POST['skill_id']);
    $skill_name = sanitize($conn, $_POST['skill_name']);
    $category = sanitize($conn, $_POST['category']);
    $description = sanitize($conn, $_POST['description']);
    
    $stmt = $conn->prepare("UPDATE skills SET skill_name = ?, category = ?, description = ? WHERE id = ?");
    $stmt->bind_param("sssi", $skill_name, $category, $description, $skill_id);
    
    if ($stmt->execute()) {
        $message = 'Skill updated successfully';
        logActivity($conn, $_SESSION['user_id'], 'Skill Updated', "Skill ID: $skill_id");
    } else {
        $error = 'Failed to update skill';
    }
    $stmt->close();
}

// Handle Delete Skill
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $skill_id = intval($_GET['delete']);
    
    $stmt = $conn->prepare("DELETE FROM skills WHERE id = ?");
    $stmt->bind_param("i", $skill_id);
    
    if ($stmt->execute()) {
        $message = 'Skill deleted successfully';
        logActivity($conn, $_SESSION['user_id'], 'Skill Deleted', "Skill ID: $skill_id");
    } else {
        $error = 'Failed to delete skill';
    }
    $stmt->close();
}

// Get all skills
$skills = $conn->query("SELECT s.*, COUNT(cs.id) as employee_count FROM skills s LEFT JOIN candidate_skills cs ON s.id = cs.skill_id GROUP BY s.id ORDER BY s.skill_name");

// Get skill for editing
$edit_skill = null;
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $stmt = $conn->prepare("SELECT * FROM skills WHERE id = ?");
    $stmt->bind_param("i", $_GET['edit']);
    $stmt->execute();
    $edit_skill = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Skills - SkillTrack Pro</title>
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
                <a href="manage_skills.php" class="sidebar-link active">
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
            <h1>Manage Skills</h1>
            <p>Add, edit, and manage system skills</p>
        </div>

        <div class="dashboard-grid" style="grid-template-columns: 1fr 2fr;">
            <!-- Add/Edit Skill Form -->
            <div class="glass-card" style="padding: 24px;">
                <h3 style="margin-bottom: 20px;">
                    <i class="fas fa-<?php echo $edit_skill ? 'edit' : 'plus'; ?>"></i>
                    <?php echo $edit_skill ? 'Edit Skill' : 'Add New Skill'; ?>
                </h3>
                
                <form method="POST" action="">
                    <?php if ($edit_skill): ?>
                        <input type="hidden" name="skill_id" value="<?php echo $edit_skill['id']; ?>">
                    <?php endif; ?>
                    
                    <div class="form-group">
                        <label class="form-label">Skill Name *</label>
                        <input type="text" name="skill_name" class="glass-input" placeholder="e.g., JavaScript" 
                               value="<?php echo $edit_skill ? htmlspecialchars($edit_skill['skill_name']) : ''; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Category</label>
                        <select name="category" class="form-select">
                            <option value="">Select Category</option>
                            <option value="Frontend" <?php echo $edit_skill && $edit_skill['category'] === 'Frontend' ? 'selected' : ''; ?>>Frontend</option>
                            <option value="Backend" <?php echo $edit_skill && $edit_skill['category'] === 'Backend' ? 'selected' : ''; ?>>Backend</option>
                            <option value="Database" <?php echo $edit_skill && $edit_skill['category'] === 'Database' ? 'selected' : ''; ?>>Database</option>
                            <option value="DevOps" <?php echo $edit_skill && $edit_skill['category'] === 'DevOps' ? 'selected' : ''; ?>>DevOps</option>
                            <option value="Cloud" <?php echo $edit_skill && $edit_skill['category'] === 'Cloud' ? 'selected' : ''; ?>>Cloud</option>
                            <option value="Mobile" <?php echo $edit_skill && $edit_skill['category'] === 'Mobile' ? 'selected' : ''; ?>>Mobile</option>
                            <option value="Other" <?php echo $edit_skill && $edit_skill['category'] === 'Other' ? 'selected' : ''; ?>>Other</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="glass-input" rows="4" placeholder="Brief description of the skill"><?php echo $edit_skill ? htmlspecialchars($edit_skill['description']) : ''; ?></textarea>
                    </div>
                    
                    <div style="display: flex; gap: 10px;">
                        <button type="submit" name="<?php echo $edit_skill ? 'edit_skill' : 'add_skill'; ?>" class="btn btn-primary" style="flex: 1;">
                            <i class="fas fa-<?php echo $edit_skill ? 'save' : 'plus'; ?>"></i>
                            <?php echo $edit_skill ? 'Update Skill' : 'Add Skill'; ?>
                        </button>
                        
                        <?php if ($edit_skill): ?>
                            <a href="manage_skills.php" class="btn btn-outline">
                                <i class="fas fa-times"></i> Cancel
                            </a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
            
            <!-- Skills List -->
            <div class="table-container">
                <div class="table-header">
                    <h3>All Skills</h3>
                    <span style="color: var(--text-muted); font-size: 14px;"><?php echo $skills->num_rows; ?> skills</span>
                </div>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Skill</th>
                            <th>Category</th>
                            <th>Description</th>
                            <th>Employees</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($skill = $skills->fetch_assoc()): ?>
                        <tr>
                            <td>
                                <strong><?php echo htmlspecialchars($skill['skill_name']); ?></strong>
                            </td>
                            <td>
                                <span class="badge badge-primary"><?php echo htmlspecialchars($skill['category'] ?: 'Uncategorized'); ?></span>
                            </td>
                            <td><?php echo htmlspecialchars(substr($skill['description'], 0, 50)) . (strlen($skill['description']) > 50 ? '...' : ''); ?></td>
                            <td>
                                <span class="badge badge-info"><?php echo $skill['employee_count']; ?> employees</span>
                            </td>
                            <td>
                                <div style="display: flex; gap: 8px;">
                                    <a href="?edit=<?php echo $skill['id']; ?>" class="btn btn-sm btn-primary">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="?delete=<?php echo $skill['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this skill?')">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <script src="../assets/js/main.js"></script>
</body>
</html>
