<?php
/**
 * Candidate Skills Management
 */
require_once '../config/database.php';

// Check if user is logged in and is candidate
if (!isLoggedIn() || !hasRole('candidate')) {
    redirect('../auth/login.php?role=candidate', 'Please login as employee', 'warning');
}

$user_id = $_SESSION['user_id'];
$message = '';
$error = '';

// Handle Add Skill
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_skill'])) {
    $skill_id = intval($_POST['skill_id']);
    $proficiency_level = sanitize($conn, $_POST['proficiency_level']);
    $years_experience = floatval($_POST['years_experience']);
    
    if ($skill_id <= 0) {
        $error = 'Please select a skill';
    } else {
        $stmt = $conn->prepare("INSERT INTO candidate_skills (user_id, skill_id, proficiency_level, years_experience) VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE proficiency_level = ?, years_experience = ?");
        $stmt->bind_param("iissds", $user_id, $skill_id, $proficiency_level, $years_experience, $proficiency_level, $years_experience);
        
        if ($stmt->execute()) {
            $message = 'Skill added successfully';
            logActivity($conn, $user_id, 'Skill Added', "Skill ID: $skill_id");
        } else {
            $error = 'Failed to add skill';
        }
        $stmt->close();
    }
}

// Handle Delete Skill
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $skill_id = intval($_GET['delete']);
    
    $stmt = $conn->prepare("DELETE FROM candidate_skills WHERE user_id = ? AND skill_id = ?");
    $stmt->bind_param("ii", $user_id, $skill_id);
    
    if ($stmt->execute()) {
        $message = 'Skill removed successfully';
        logActivity($conn, $user_id, 'Skill Removed', "Skill ID: $skill_id");
    } else {
        $error = 'Failed to remove skill';
    }
    $stmt->close();
}

// Get user's skills
$my_skills = $conn->query("SELECT s.id, s.skill_name, s.category, cs.proficiency_level, cs.years_experience FROM candidate_skills cs JOIN skills s ON cs.skill_id = s.id WHERE cs.user_id = $user_id ORDER BY cs.years_experience DESC");

// Get available skills (not already added)
$available_skills = $conn->query("SELECT * FROM skills WHERE id NOT IN (SELECT skill_id FROM candidate_skills WHERE user_id = $user_id) ORDER BY category, skill_name");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Skills - SkillTrack Pro</title>
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
                <a href="skills.php" class="sidebar-link active">
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
            <h1>My Skills</h1>
            <p>Manage your skills and expertise</p>
        </div>

        <div class="dashboard-grid" style="grid-template-columns: 1fr 2fr;">
            <!-- Add Skill Form -->
            <div class="glass-card" style="padding: 24px;">
                <h3 style="margin-bottom: 20px;"><i class="fas fa-plus"></i> Add New Skill</h3>
                
                <form method="POST" action="">
                    <div class="form-group">
                        <label class="form-label">Select Skill *</label>
                        <select name="skill_id" class="form-select" required>
                            <option value="">Choose a skill...</option>
                            <?php 
                            $current_category = '';
                            while ($skill = $available_skills->fetch_assoc()): 
                                if ($skill['category'] !== $current_category):
                                    if ($current_category) echo '</optgroup>';
                                    $current_category = $skill['category'];
                                    echo '<optgroup label="' . htmlspecialchars($current_category ?: 'Other') . '">';
                                endif;
                            ?>
                                <option value="<?php echo $skill['id']; ?>"><?php echo htmlspecialchars($skill['skill_name']); ?></option>
                            <?php endwhile; 
                            if ($current_category) echo '</optgroup>';
                            ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Proficiency Level</label>
                        <select name="proficiency_level" class="form-select">
                            <option value="beginner">Beginner</option>
                            <option value="intermediate">Intermediate</option>
                            <option value="advanced">Advanced</option>
                            <option value="expert">Expert</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Years of Experience</label>
                        <input type="number" name="years_experience" class="glass-input" value="0" min="0" max="50" step="1">
                    </div>
                    
                    <button type="submit" name="add_skill" class="btn btn-primary" style="width: 100%;">
                        <i class="fas fa-plus"></i> Add Skill
                    </button>
                </form>
                
                <div style="margin-top: 20px; padding: 16px; background: rgba(255,255,255,0.03); border-radius: var(--radius-md);">
                    <h4 style="font-size: 14px; margin-bottom: 10px;"><i class="fas fa-lightbulb"></i> Tip</h4>
                    <p style="font-size: 13px; color: var(--text-muted); margin: 0;">Adding more skills increases your chances of being matched to relevant projects.</p>
                </div>
            </div>
            
            <!-- My Skills List -->
            <div class="table-container">
                <div class="table-header">
                    <h3>My Skills</h3>
                    <span class="badge badge-primary"><?php echo $my_skills->num_rows; ?> skills</span>
                </div>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Skill</th>
                            <th>Category</th>
                            <th>Proficiency</th>
                            <th>Experience</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($skill = $my_skills->fetch_assoc()): ?>
                        <tr>
                            <td>
                                <strong><?php echo htmlspecialchars($skill['skill_name']); ?></strong>
                            </td>
                            <td>
                                <span class="badge badge-info"><?php echo htmlspecialchars($skill['category'] ?: 'General'); ?></span>
                            </td>
                            <td>
                                <span class="skill-level <?php echo $skill['proficiency_level']; ?>">
                                    <?php echo ucfirst($skill['proficiency_level']); ?>
                                </span>
                            </td>
                            <td><?php echo $skill['years_experience']; ?> year<?php echo $skill['years_experience'] != 1 ? 's' : ''; ?></td>
                            <td>
                                <a href="?delete=<?php echo $skill['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Remove this skill?')">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                        <?php if ($my_skills->num_rows === 0): ?>
                        <tr>
                            <td colspan="5" style="text-align: center; color: var(--text-muted);">
                                <i class="fas fa-inbox" style="font-size: 24px; margin-bottom: 10px; display: block;"></i>
                                No skills added yet. Add your first skill!
                            </td>
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
