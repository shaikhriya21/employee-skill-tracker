<?php
/**
 * Take Assessment - Candidate
 */
require_once '../config/database.php';

// Check if user is logged in and is candidate
if (!isLoggedIn() || !hasRole('candidate')) {
    redirect('../auth/login.php?role=candidate', 'Please login as employee', 'warning');
}

$user_id = $_SESSION['user_id'];
$assessment_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// If no assessment ID, show list of available assessments
if (!$assessment_id) {
    // Get available assessments
    $available_assessments = $conn->query("SELECT a.*, s.skill_name FROM assessments a LEFT JOIN skills s ON a.skill_id = s.id WHERE a.is_active = 1 AND a.id NOT IN (SELECT assessment_id FROM results WHERE user_id = $user_id) ORDER BY a.created_at DESC");
    
    // Get completed assessments
    $completed_assessments = $conn->query("SELECT r.*, a.title, a.duration_minutes, a.total_questions FROM results r JOIN assessments a ON r.assessment_id = a.id WHERE r.user_id = $user_id ORDER BY r.completed_at DESC");
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Take Assessment - SkillTrack Pro</title>
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
                    <a href="skills.php" class="sidebar-link">
                        <i class="fas fa-brain"></i>
                        My Skills
                    </a>
                </li>
                <li class="sidebar-item">
                    <a href="take_assessment.php" class="sidebar-link active">
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
            <div class="dashboard-header">
                <h1>Take Assessment</h1>
                <p>Complete assessments to validate your skills</p>
            </div>

            <!-- Available Assessments -->
            <h3 style="margin-bottom: 20px;"><i class="fas fa-clipboard-list"></i> Available Assessments</h3>
            <div class="project-grid">
                <?php while ($assessment = $available_assessments->fetch_assoc()): ?>
                <div class="glass-card" style="padding: 24px;">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 16px;">
                        <h4 style="margin: 0;"><?php echo htmlspecialchars($assessment['title']); ?></h4>
                        <span class="badge badge-success">Available</span>
                    </div>
                    <p style="color: var(--text-muted); font-size: 14px; margin-bottom: 16px;">
                        <?php echo htmlspecialchars(substr($assessment['description'], 0, 100)) . (strlen($assessment['description']) > 100 ? '...' : ''); ?>
                    </p>
                    <div style="display: flex; gap: 16px; margin-bottom: 16px; font-size: 13px; color: var(--text-muted);">
                        <span><i class="fas fa-clock"></i> <?php echo $assessment['duration_minutes']; ?> min</span>
                        <span><i class="fas fa-question-circle"></i> <?php echo $assessment['total_questions']; ?> questions</span>
                        <span><i class="fas fa-percentage"></i> Pass: <?php echo $assessment['passing_score']; ?>%</span>
                    </div>
                    <a href="take_assessment.php?id=<?php echo $assessment['id']; ?>" class="btn btn-primary" style="width: 100%;">
                        <i class="fas fa-play"></i> Start Assessment
                    </a>
                </div>
                <?php endwhile; ?>
                <?php if ($available_assessments->num_rows === 0): ?>
                <div class="glass-card" style="padding: 40px; text-align: center; grid-column: 1 / -1;">
                    <i class="fas fa-check-circle" style="font-size: 48px; color: var(--success-color); margin-bottom: 16px;"></i>
                    <h3>All Caught Up!</h3>
                    <p style="color: var(--text-muted);">You've completed all available assessments.</p>
                </div>
                <?php endif; ?>
            </div>

            <!-- Completed Assessments -->
            <h3 style="margin: 40px 0 20px;"><i class="fas fa-history"></i> Completed Assessments</h3>
            <div class="table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Assessment</th>
                            <th>Score</th>
                            <th>Status</th>
                            <th>Completed</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($result = $completed_assessments->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($result['title']); ?></td>
                            <td>
                                <div style="display: flex; align-items: center; gap: 10px;">
                                    <div class="progress-bar" style="width: 100px; height: 8px;">
                                        <div class="progress-fill <?php echo $result['status'] === 'passed' ? 'success' : 'danger'; ?>" style="width: <?php echo $result['percentage']; ?>%"></div>
                                    </div>
                                    <span><?php echo $result['percentage']; ?>%</span>
                                </div>
                            </td>
                            <td>
                                <span class="badge badge-<?php echo $result['status'] === 'passed' ? 'success' : 'danger'; ?>">
                                    <?php echo ucfirst($result['status']); ?>
                                </span>
                            </td>
                            <td><?php echo date('M d, Y', strtotime($result['completed_at'])); ?></td>
                        </tr>
                        <?php endwhile; ?>
                        <?php if ($completed_assessments->num_rows === 0): ?>
                        <tr>
                            <td colspan="4" style="text-align: center; color: var(--text-muted);">No completed assessments yet</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </main>

        <script src="../assets/js/main.js"></script>
    </body>
    </html>
    <?php
    exit;
}

// Get assessment details
$stmt = $conn->prepare("SELECT * FROM assessments WHERE id = ? AND is_active = 1");
$stmt->bind_param("i", $assessment_id);
$stmt->execute();
$assessment = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$assessment) {
    redirect('take_assessment.php', 'Assessment not found or inactive', 'danger');
}

// Check if already taken
$stmt = $conn->prepare("SELECT id FROM results WHERE user_id = ? AND assessment_id = ?");
$stmt->bind_param("ii", $user_id, $assessment_id);
$stmt->execute();
if ($stmt->get_result()->num_rows > 0) {
    redirect('take_assessment.php', 'You have already taken this assessment', 'warning');
}
$stmt->close();

// Get questions
$questions = $conn->query("SELECT * FROM questions WHERE assessment_id = $assessment_id ORDER BY id ASC");
$total_questions = $questions->num_rows;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $answers = $_POST['answers'] ?? [];
    $score = 0;
    $total_marks = 0;
    
    $questions->data_seek(0);
    while ($question = $questions->fetch_assoc()) {
        $total_marks += $question['marks'];
        $qid = $question['id'];
        if (isset($answers[$qid]) && $answers[$qid] === $question['correct_answer']) {
            $score += $question['marks'];
        }
    }
    
    $percentage = $total_marks > 0 ? round(($score / $total_marks) * 100, 2) : 0;
    $status = $percentage >= $assessment['passing_score'] ? 'passed' : 'failed';
    $answers_json = json_encode($answers);
    
    // Save result
    $stmt = $conn->prepare("INSERT INTO results (user_id, assessment_id, score, total_marks, percentage, status, answers) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("iiiidss", $user_id, $assessment_id, $score, $total_marks, $percentage, $status, $answers_json);
    $stmt->execute();
    $stmt->close();
    
    logActivity($conn, $user_id, 'Assessment Completed', "Assessment ID: $assessment_id, Score: $percentage%");
    
    // Show results
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Assessment Results - SkillTrack Pro</title>
        <link rel="stylesheet" href="../assets/css/styles.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    </head>
    <body>
        <div class="auth-page">
            <div class="glass-card" style="padding: 40px; text-align: center; max-width: 500px;">
                <div style="width: 100px; height: 100px; border-radius: 50%; background: <?php echo $status === 'passed' ? 'var(--success-color)' : 'var(--danger-color)'; ?>; display: flex; align-items: center; justify-content: center; margin: 0 auto 24px; font-size: 48px;">
                    <i class="fas fa-<?php echo $status === 'passed' ? 'check' : 'times'; ?>"></i>
                </div>
                <h2 style="margin-bottom: 8px;">Assessment <?php echo $status === 'passed' ? 'Passed!' : 'Failed'; ?></h2>
                <p style="color: var(--text-muted); margin-bottom: 24px;"><?php echo htmlspecialchars($assessment['title']); ?></p>
                
                <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; margin-bottom: 24px;">
                    <div style="padding: 16px; background: rgba(255,255,255,0.03); border-radius: var(--radius-md);">
                        <div style="font-size: 24px; font-weight: 700;"><?php echo $score; ?>/<?php echo $total_marks; ?></div>
                        <div style="font-size: 12px; color: var(--text-muted);">Score</div>
                    </div>
                    <div style="padding: 16px; background: rgba(255,255,255,0.03); border-radius: var(--radius-md);">
                        <div style="font-size: 24px; font-weight: 700;"><?php echo $percentage; ?>%</div>
                        <div style="font-size: 12px; color: var(--text-muted);">Percentage</div>
                    </div>
                    <div style="padding: 16px; background: rgba(255,255,255,0.03); border-radius: var(--radius-md);">
                        <div style="font-size: 24px; font-weight: 700;"><?php echo $assessment['passing_score']; ?>%</div>
                        <div style="font-size: 12px; color: var(--text-muted);">Required</div>
                    </div>
                </div>
                
                <a href="take_assessment.php" class="btn btn-primary">
                    <i class="fas fa-arrow-left"></i> Back to Assessments
                </a>
            </div>
        </div>
    </body>
    </html>
    <?php
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($assessment['title']); ?> - SkillTrack Pro</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <!-- Timer -->
    <div class="assessment-timer" id="assessment-timer" data-duration="<?php echo $assessment['duration_minutes']; ?>">
        <i class="fas fa-clock"></i>
        <div>
            <div style="font-size: 11px; color: var(--text-muted);">Time Remaining</div>
            <div class="time-display" style="font-size: 20px; font-weight: 700; font-family: monospace;">--:--</div>
        </div>
    </div>

    <!-- Navigation -->
    <nav class="navbar">
        <a href="../index.php" class="navbar-brand">
            <i class="fas fa-layer-group"></i>
            SkillTrack Pro
        </a>
        <ul class="navbar-nav">
            <li><a href="../auth/logout.php" class="nav-link"><i class="fas fa-sign-out-alt"></i> Exit</a></li>
        </ul>
    </nav>

    <!-- Main Content -->
    <main class="main-content-full" style="padding-top: 100px;">
        <div class="dashboard-header">
            <h1><?php echo htmlspecialchars($assessment['title']); ?></h1>
            <p><?php echo $total_questions; ?> questions | <?php echo $assessment['duration_minutes']; ?> minutes | Pass: <?php echo $assessment['passing_score']; ?>%</p>
        </div>

        <form method="POST" action="" id="assessment-form">
            <?php 
            $q_num = 1;
            while ($question = $questions->fetch_assoc()): 
            ?>
            <div class="question-card">
                <div class="question-header">
                    <span>Question <?php echo $q_num++; ?> of <?php echo $total_questions; ?></span>
                    <span><?php echo $question['marks']; ?> mark<?php echo $question['marks'] > 1 ? 's' : ''; ?></span>
                </div>
                
                <div class="question-text">
                    <?php echo htmlspecialchars($question['question_text']); ?>
                </div>
                
                <div class="options-list">
                    <label class="option-item">
                        <input type="radio" name="answers[<?php echo $question['id']; ?>]" value="a" required>
                        <span><?php echo htmlspecialchars($question['option_a']); ?></span>
                    </label>
                    
                    <label class="option-item">
                        <input type="radio" name="answers[<?php echo $question['id']; ?>]" value="b" required>
                        <span><?php echo htmlspecialchars($question['option_b']); ?></span>
                    </label>
                    
                    <label class="option-item">
                        <input type="radio" name="answers[<?php echo $question['id']; ?>]" value="c" required>
                        <span><?php echo htmlspecialchars($question['option_c']); ?></span>
                    </label>
                    
                    <label class="option-item">
                        <input type="radio" name="answers[<?php echo $question['id']; ?>]" value="d" required>
                        <span><?php echo htmlspecialchars($question['option_d']); ?></span>
                    </label>
                </div>
            </div>
            <?php endwhile; ?>
            
            <div style="text-align: center; margin: 40px 0;">
                <button type="submit" class="btn btn-primary btn-lg">
                    <i class="fas fa-check-circle"></i> Submit Assessment
                </button>
            </div>
        </form>
    </main>

    <script src="../assets/js/main.js"></script>
    <script>
        // Prevent leaving page
        window.onbeforeunload = function() {
            return "Are you sure you want to leave? Your progress will be lost.";
        };
        
        // Remove warning on form submit
        document.getElementById('assessment-form').addEventListener('submit', function() {
            window.onbeforeunload = null;
        });
    </script>
</body>
</html>
