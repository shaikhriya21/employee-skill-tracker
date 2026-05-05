<?php
/**
 * Manage Users - Admin
 */
require_once '../config/database.php';

// Check if user is logged in and is admin
if (!isLoggedIn() || !hasRole('admin')) {
    header("Location: ../auth/login.php?role=admin");
    exit();
}

$message = '';
$error = '';

// Handle user activation/deactivation
if (isset($_GET['action']) && isset($_GET['id'])) {
    $user_id = intval($_GET['id']);
    $action = $_GET['action'];
    
    if ($action === 'activate' || $action === 'deactivate') {
        $is_active = ($action === 'activate') ? 1 : 0;
        $stmt = $conn->prepare("UPDATE users SET is_active = ? WHERE id = ?");
        $stmt->bind_param("ii", $is_active, $user_id);
        
        if ($stmt->execute()) {
            $message = 'User status updated successfully';
            logActivity($conn, $_SESSION['user_id'], 'User Status Update', "User ID: $user_id set to " . ($is_active ? 'active' : 'inactive'));
        } else {
            $error = 'Failed to update user status';
        }
        $stmt->close();
    } elseif ($action === 'delete') {
        // Prevent deleting self
        if ($user_id === $_SESSION['user_id']) {
            $error = 'You cannot delete your own account';
        } else {
            $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
            $stmt->bind_param("i", $user_id);
            
            if ($stmt->execute()) {
                $message = 'User deleted successfully';
                logActivity($conn, $_SESSION['user_id'], 'User Deleted', "User ID: $user_id");
            } else {
                $error = 'Failed to delete user';
            }
            $stmt->close();
        }
    }
}

// Get filter parameters
$role_filter = isset($_GET['role']) ? sanitize($conn, $_GET['role']) : '';
$search = isset($_GET['search']) ? sanitize($conn, $_GET['search']) : '';

// Build query
$query = "SELECT * FROM users WHERE 1=1";
$params = [];
$types = '';

if ($role_filter) {
    $query .= " AND role = ?";
    $params[] = $role_filter;
    $types .= 's';
}

if ($search) {
    $query .= " AND (full_name LIKE ? OR email LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $types .= 'ss';
}

$query .= " ORDER BY created_at DESC";

$stmt = $conn->prepare($query);
if ($types) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$users = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - SkillTrack Pro</title>
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
                <a href="manage_users.php" class="sidebar-link active">
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
            <h1>Manage Users</h1>
            <p>View and manage all system users</p>
        </div>

        <!-- Filters -->
        <div class="glass-card" style="padding: 20px; margin-bottom: 24px;">
            <form method="GET" action="" style="display: flex; gap: 16px; flex-wrap: wrap; align-items: flex-end;">
                <div style="flex: 1; min-width: 200px;">
                    <label class="form-label">Search</label>
                    <div style="position: relative;">
                        <i class="fas fa-search" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: var(--text-muted);"></i>
                        <input type="text" name="search" class="glass-input" style="padding-left: 40px;" placeholder="Search by name or email" value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                </div>
                
                <div style="min-width: 150px;">
                    <label class="form-label">Role</label>
                    <select name="role" class="form-select">
                        <option value="">All Roles</option>
                        <option value="admin" <?php echo $role_filter === 'admin' ? 'selected' : ''; ?>>Admin</option>
                        <option value="hr" <?php echo $role_filter === 'hr' ? 'selected' : ''; ?>>HR</option>
                        <option value="candidate" <?php echo $role_filter === 'candidate' ? 'selected' : ''; ?>>Employee</option>
                    </select>
                </div>
                
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-filter"></i> Filter
                </button>
                
                <a href="manage_users.php" class="btn btn-outline">
                    <i class="fas fa-times"></i> Clear
                </a>
            </form>
        </div>

        <!-- Users Table -->
        <div class="table-container">
            <div class="table-header">
                <h3>All Users</h3>
                <span style="color: var(--text-muted); font-size: 14px;"><?php echo $users->num_rows; ?> users found</span>
            </div>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Joined</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($user = $users->fetch_assoc()): ?>
                    <tr>
                        <td>#<?php echo $user['id']; ?></td>
                        <td>
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <div style="width: 35px; height: 35px; border-radius: 50%; background: var(--gradient-primary); display: flex; align-items: center; justify-content: center; font-size: 14px; font-weight: 600;">
                                    <?php echo strtoupper(substr($user['full_name'], 0, 1)); ?>
                                </div>
                                <?php echo htmlspecialchars($user['full_name']); ?>
                            </div>
                        </td>
                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                        <td><?php echo htmlspecialchars($user['phone'] ?: '-'); ?></td>
                        <td>
                            <span class="badge badge-<?php echo $user['role'] === 'admin' ? 'danger' : ($user['role'] === 'hr' ? 'warning' : 'primary'); ?>">
                                <?php echo ucfirst($user['role']); ?>
                            </span>
                        </td>
                        <td>
                            <span class="badge badge-<?php echo $user['is_active'] ? 'success' : 'danger'; ?>">
                                <?php echo $user['is_active'] ? 'Active' : 'Inactive'; ?>
                            </span>
                        </td>
                        <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                        <td>
                            <div style="display: flex; gap: 8px;">
                                <?php if ($user['is_active']): ?>
                                    <a href="?action=deactivate&id=<?php echo $user['id']; ?>" class="btn btn-sm btn-warning" onclick="return confirm('Deactivate this user?')">
                                        <i class="fas fa-ban"></i>
                                    </a>
                                <?php else: ?>
                                    <a href="?action=activate&id=<?php echo $user['id']; ?>" class="btn btn-sm btn-success" onclick="return confirm('Activate this user?')">
                                        <i class="fas fa-check"></i>
                                    </a>
                                <?php endif; ?>
                                
                                <?php if ($user['id'] !== $_SESSION['user_id']): ?>
                                    <a href="?action=delete&id=<?php echo $user['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this user?')">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                <?php endif; ?>
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
