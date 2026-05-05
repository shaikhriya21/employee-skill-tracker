<?php
/**
 * Manage Questions - Admin
 */
require_once '../config/database.php';

// Check if user is logged in and is admin
if (!isLoggedIn() || !hasRole('admin')) {
    redirect('../auth/login.php?role=admin', 'Please login as admin', 'warning');
}

$assessment_id = isset($_GET['assessment_id']) ? intval($_GET['assessment_id']) : 0;

if (!$assessment_id) {
    redirect('manage_assessments.php', 'Assessment not found', 'danger');
}

// Get assessment details
$stmt = $conn->prepare("SELECT a.*, s.skill_name FROM assessments a LEFT JOIN skills s ON a.skill_id = s.id WHERE a.id = ?");
$stmt->bind_param("i", $assessment_id);
$stmt->execute();
$assessment = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$assessment) {
    redirect('manage_assessments.php', 'Assessment not found', 'danger');
}

$message = '';
$error = '';

// Handle Add Question
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_question'])) {
    $question_text = sanitize($conn, $_POST['question_text']);
    $option_a = sanitize($conn, $_POST['option_a']);
    $option_b = sanitize($conn, $_POST['option_b']);
    $option_c = sanitize($conn, $_POST['option_c']);
    $option_d = sanitize($conn, $_POST['option_d']);
    $correct_answer = sanitize($conn, $_POST['correct_answer']);
    $marks = intval($_POST['marks']);
    
    if (empty($question_text) || empty($option_a) || empty($option_b) || empty($option_c) || empty($option_d)) {
        $error = 'Please fill in all fields';
    } else {
        $stmt = $conn->prepare("INSERT INTO questions (assessment_id, question_text, option_a, option_b, option_c, option_d, correct_answer, marks) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("issssssi", $assessment_id, $question_text, $option_a, $option_b, $option_c, $option_d, $correct_answer, $marks);
        
        if ($stmt->execute()) {
            // Update total questions count
            $conn->query("UPDATE assessments SET total_questions = (SELECT COUNT(*) FROM questions WHERE assessment_id = $assessment_id) WHERE id = $assessment_id");
            
            $message = 'Question added successfully';
            logActivity($conn, $_SESSION['user_id'], 'Question Added', "Assessment ID: $assessment_id");
        } else {
            $error = 'Failed to add question';
        }
        $stmt->close();
    }
}

// Handle Delete Question
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $question_id = intval($_GET['delete']);
    
    $stmt = $conn->prepare("DELETE FROM questions WHERE id = ? AND assessment_id = ?");
    $stmt->bind_param("ii", $question_id, $assessment_id);
    
    if ($stmt->execute()) {
        // Update total questions count
        $conn->query("UPDATE assessments SET total_questions = (SELECT COUNT(*) FROM questions WHERE assessment_id = $assessment_id) WHERE id = $assessment_id");
        
        $message = 'Question deleted successfully';
        logActivity($conn, $_SESSION['user_id'], 'Question Deleted', "Question ID: $question_id");
    } else {
        $error = 'Failed to delete question';
    }
    $stmt->close();
}

// Get all questions
$stmt = $conn->prepare("SELECT * FROM questions WHERE assessment_id = ? ORDER BY id ASC");
$stmt->bind_param("i", $assessment_id);
$stmt->execute();
$questions = $stmt->get_result();
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Questions - SkillTrack Pro</title>
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
            <li><a href="#" class="nav-link"><i class="fas fa-bell"></i></a></li>
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
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <h1>Manage Questions</h1>
                    <p><?php echo htmlspecialchars($assessment['title']); ?> - <?php echo $questions->num_rows; ?> questions</p>
                </div>
                <a href="manage_assessments.php" class="btn btn-outline">
                    <i class="fas fa-arrow-left"></i> Back to Assessments
                </a>
            </div>
        </div>

        <!-- Add Question Form -->
        <div class="glass-card" style="padding: 24px; margin-bottom: 24px;">
            <h3 style="margin-bottom: 20px;"><i class="fas fa-plus"></i> Add New Question</h3>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label class="form-label">Question Text *</label>
                    <textarea name="question_text" class="glass-input" rows="3" placeholder="Enter your question here..." required></textarea>
                </div>
                
                <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px;">
                    <div class="form-group">
                        <label class="form-label">Option A *</label>
                        <input type="text" name="option_a" class="glass-input" placeholder="Option A" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Option B *</label>
                        <input type="text" name="option_b" class="glass-input" placeholder="Option B" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Option C *</label>
                        <input type="text" name="option_c" class="glass-input" placeholder="Option C" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Option D *</label>
                        <input type="text" name="option_d" class="glass-input" placeholder="Option D" required>
                    </div>
                </div>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-top: 20px;">
                    <div class="form-group">
                        <label class="form-label">Correct Answer *</label>
                        <select name="correct_answer" class="form-select" required>
                            <option value="a">Option A</option>
                            <option value="b">Option B</option>
                            <option value="c">Option C</option>
                            <option value="d">Option D</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Marks</label>
                        <input type="number" name="marks" class="glass-input" value="1" min="1" max="10">
                    </div>
                </div>
                
                <button type="submit" name="add_question" class="btn btn-primary" style="margin-top: 20px;">
                    <i class="fas fa-plus"></i> Add Question
                </button>
            </form>
        </div>

        <!-- Questions List -->
        <div class="dashboard-grid">
            <?php 
            $q_num = 1;
            while ($question = $questions->fetch_assoc()): 
            ?>
            <div class="question-card">
                <div class="question-header">
                    <span>Question <?php echo $q_num++; ?></span>
                    <span><?php echo $question['marks']; ?> mark<?php echo $question['marks'] > 1 ? 's' : ''; ?></span>
                </div>
                
                <div class="question-text">
                    <?php echo htmlspecialchars($question['question_text']); ?>
                </div>
                
                <div class="options-list">
                    <div class="option-item <?php echo $question['correct_answer'] === 'a' ? 'selected' : ''; ?>">
                        <input type="radio" disabled <?php echo $question['correct_answer'] === 'a' ? 'checked' : ''; ?>>
                        <span><?php echo htmlspecialchars($question['option_a']); ?></span>
                        <?php if ($question['correct_answer'] === 'a'): ?>
                            <i class="fas fa-check-circle" style="color: var(--success-color); margin-left: auto;"></i>
                        <?php endif; ?>
                    </div>
                    
                    <div class="option-item <?php echo $question['correct_answer'] === 'b' ? 'selected' : ''; ?>">
                        <input type="radio" disabled <?php echo $question['correct_answer'] === 'b' ? 'checked' : ''; ?>>
                        <span><?php echo htmlspecialchars($question['option_b']); ?></span>
                        <?php if ($question['correct_answer'] === 'b'): ?>
                            <i class="fas fa-check-circle" style="color: var(--success-color); margin-left: auto;"></i>
                        <?php endif; ?>
                    </div>
                    
                    <div class="option-item <?php echo $question['correct_answer'] === 'c' ? 'selected' : ''; ?>">
                        <input type="radio" disabled <?php echo $question['correct_answer'] === 'c' ? 'checked' : ''; ?>>
                        <span><?php echo htmlspecialchars($question['option_c']); ?></span>
                        <?php if ($question['correct_answer'] === 'c'): ?>
                            <i class="fas fa-check-circle" style="color: var(--success-color); margin-left: auto;"></i>
                        <?php endif; ?>
                    </div>
                    
                    <div class="option-item <?php echo $question['correct_answer'] === 'd' ? 'selected' : ''; ?>">
                        <input type="radio" disabled <?php echo $question['correct_answer'] === 'd' ? 'checked' : ''; ?>>
                        <span><?php echo htmlspecialchars($question['option_d']); ?></span>
                        <?php if ($question['correct_answer'] === 'd'): ?>
                            <i class="fas fa-check-circle" style="color: var(--success-color); margin-left: auto;"></i>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div style="margin-top: 20px; text-align: right;">
                    <a href="?assessment_id=<?php echo $assessment_id; ?>&delete=<?php echo $question['id']; ?>" 
                       class="btn btn-sm btn-danger" 
                       onclick="return confirm('Delete this question?')">
                        <i class="fas fa-trash"></i> Delete
                    </a>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
    </main>

    <script src="../assets/js/main.js"></script>
</body>
</html>