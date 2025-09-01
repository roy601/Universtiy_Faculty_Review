<?php
session_start();
include "../DBconnect.php";

// Ensure only admins can access
if (!isset($_SESSION['username']) || !isset($_SESSION['id']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit();
}

// Handle user actions
if (isset($_GET['action']) && isset($_GET['id'])) {
    $user_id = intval($_GET['id']);
    $action = $_GET['action'];
    
    if ($action === 'delete') {
        // Prevent admin from deleting themselves
        if ($user_id != $_SESSION['id']) {
            $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            
            // Log the action
            $admin_id = $_SESSION['id'];
            $action_text = "Deleted user #$user_id";
            $log_stmt = $conn->prepare("INSERT INTO admin_logs (admin_id, action, target_type, target_id) VALUES (?, ?, 'user', ?)");
            $log_stmt->bind_param("isi", $admin_id, $action_text, $user_id);
            $log_stmt->execute();
            
            $_SESSION['message'] = "User deleted successfully";
        } else {
            $_SESSION['error'] = "You cannot delete your own account";
        }
    } elseif ($action === 'toggle_admin') {
        // Prevent admin from changing their own role
        if ($user_id != $_SESSION['id']) {
            // Get current role
            $stmt = $conn->prepare("SELECT role FROM users WHERE id = ?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();
            
            $new_role = $user['role'] === 'admin' ? 'user' : 'admin';
            
            $stmt = $conn->prepare("UPDATE users SET role = ? WHERE id = ?");
            $stmt->bind_param("si", $new_role, $user_id);
            $stmt->execute();
            
            // Log the action
            $admin_id = $_SESSION['id'];
            $action_text = "Changed user #$user_id role to $new_role";
            $log_stmt = $conn->prepare("INSERT INTO admin_logs (admin_id, action, target_type, target_id) VALUES (?, ?, 'user', ?)");
            $log_stmt->bind_param("isi", $admin_id, $action_text, $user_id);
            $log_stmt->execute();
            
            $_SESSION['message'] = "User role updated successfully";
        } else {
            $_SESSION['error'] = "You cannot change your own role";
        }
    }
    
    header("Location: users.php");
    exit();
}

// Get all users
$users_result = $conn->query("SELECT * FROM users ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management - Faculty Hub</title>
    <link rel="stylesheet" href="../dashboardstyle.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="animated-bg">
        <div class="gradient-orb orb-1"></div>
        <div class="gradient-orb orb-2"></div>
        <div class="gradient-orb orb-3"></div>
    </div>
    
    <nav class="navbar">
        <div class="nav-container">
            <div class="nav-brand">
                <div class="brand-icon">
                    <i class="fas fa-graduation-cap"></i>
                </div>
                <div class="brand-text">FacultyHub Admin</div>
            </div>
            <div class="nav-menu">
                <a href="../dashboard_admin.php" class="nav-btn">
                    <i class="fas fa-home"></i>
                    <span>Dashboard</span>
                </a>
                <a href="faculty_management.php" class="nav-btn">
                    <i class="fas fa-users"></i>
                    <span>Faculty</span>
                </a>
                <a href="reviews.php" class="nav-btn">
                    <i class="fas fa-star"></i>
                    <span>Reviews</span>
                </a>
                <a href="users.php" class="nav-btn active">
                    <i class="fas fa-user-cog"></i>
                    <span>Users</span>
                </a>
                <a href="reports.php" class="nav-btn">
                    <i class="fas fa-chart-bar"></i>
                    <span>Activity</span>
                </a>
                <a href="../logout.php" class="nav-btn">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Logout</span>
                </a>
            </div>
        </div>
    </nav>

    <div class="dashboard-container">
        <div class="dashboard-header">
            <div class="welcome-section">
                <h1>User Management</h1>
                <p>Manage system users and their permissions</p>
            </div>
        </div>
        
        <?php if (isset($_SESSION['message'])): ?>
            <div class="alert alert-success">
                <?php echo $_SESSION['message']; unset($_SESSION['message']); ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger">
                <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>
        
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Username</th>
                        <th>Role</th>
                        <th>Joined</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($user = $users_result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $user['id']; ?></td>
                        <td><?php echo htmlspecialchars($user['name']); ?></td>
                        <td><?php echo htmlspecialchars($user['username']); ?></td>
                        <td>
                            <span class="badge bg-<?php echo $user['role'] === 'admin' ? 'primary' : 'secondary'; ?>">
                                <?php echo ucfirst($user['role']); ?>
                            </span>
                        </td>
                        <td><?php echo date('M j, Y', strtotime($user['created_at'])); ?></td>
                        <td>
                            <div class="action-buttons">
                                <a href="edit_user.php?id=<?php echo $user['id']; ?>" class="btn btn-sm btn-primary">Edit</a>
                                <?php if ($user['id'] != $_SESSION['id']): ?>
                                    <a href="users.php?action=toggle_admin&id=<?php echo $user['id']; ?>" class="btn btn-sm btn-<?php echo $user['role'] === 'admin' ? 'warning' : 'success'; ?>">
                                        <?php echo $user['role'] === 'admin' ? 'Remove Admin' : 'Make Admin'; ?>
                                    </a>
                                    <a href="users.php?action=delete&id=<?php echo $user['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this user?')">Delete</a>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
<?php $conn->close(); ?>