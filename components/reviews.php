<?php
session_start();
include "../DBconnect.php";

// Ensure only admins can access
if (!isset($_SESSION['username']) || !isset($_SESSION['id']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit();
}

// Handle review actions
if (isset($_GET['action']) && isset($_GET['id'])) {
    $review_id = intval($_GET['id']);
    $action = $_GET['action'];
    
    if ($action === 'approve') {
        $stmt = $conn->prepare("UPDATE reviews SET status = 'approved' WHERE id = ?");
        $stmt->bind_param("i", $review_id);
        $stmt->execute();
        
        // Log the action
        $admin_id = $_SESSION['id'];
        $action_text = "Approved review #$review_id";
        $log_stmt = $conn->prepare("INSERT INTO admin_logs (admin_id, action, target_type, target_id) VALUES (?, ?, 'review', ?)");
        $log_stmt->bind_param("isi", $admin_id, $action_text, $review_id);
        $log_stmt->execute();
        
        $_SESSION['message'] = "Review approved successfully";
    } elseif ($action === 'reject') {
        $stmt = $conn->prepare("UPDATE reviews SET status = 'rejected' WHERE id = ?");
        $stmt->bind_param("i", $review_id);
        $stmt->execute();
        
        // Log the action
        $admin_id = $_SESSION['id'];
        $action_text = "Rejected review #$review_id";
        $log_stmt = $conn->prepare("INSERT INTO admin_logs (admin_id, action, target_type, target_id) VALUES (?, ?, 'review', ?)");
        $log_stmt->bind_param("isi", $admin_id, $action_text, $review_id);
        $log_stmt->execute();
        
        $_SESSION['message'] = "Review rejected successfully";
    } elseif ($action === 'delete') {
        $stmt = $conn->prepare("DELETE FROM reviews WHERE id = ?");
        $stmt->bind_param("i", $review_id);
        $stmt->execute();
        
        // Log the action
        $admin_id = $_SESSION['id'];
        $action_text = "Deleted review #$review_id";
        $log_stmt = $conn->prepare("INSERT INTO admin_logs (admin_id, action, target_type, target_id) VALUES (?, ?, 'review', ?)");
        $log_stmt->bind_param("isi", $admin_id, $action_text, $review_id);
        $log_stmt->execute();
        
        $_SESSION['message'] = "Review deleted successfully";
    }
    
    header("Location: reviews.php");
    exit();
}

// Get filter status
$status_filter = isset($_GET['status']) ? $_GET['status'] : 'all';

// Build query based on filter
if ($status_filter === 'all') {
    $query = "SELECT r.*, u.name as student_name, f.name as faculty_name, c.name as course_name
              FROM reviews r 
              JOIN users u ON r.student_id = u.id 
              JOIN faculty f ON r.faculty_id = f.id
              JOIN courses c ON r.course_id = c.id
              ORDER BY r.created_at DESC";
    $stmt = $conn->prepare($query);
} else {
    $query = "SELECT r.*, u.name as student_name, f.name as faculty_name, c.name as course_name
              FROM reviews r 
              JOIN users u ON r.student_id = u.id 
              JOIN faculty f ON r.faculty_id = f.id
              JOIN courses c ON r.course_id = c.id
              WHERE r.status = ?
              ORDER BY r.created_at DESC";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $status_filter);
}

$stmt->execute();
$reviews_result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Review Moderation - Faculty Hub</title>
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
                <a href="reviews.php" class="nav-btn active">
                    <i class="fas fa-star"></i>
                    <span>Reviews</span>
                </a>
                <a href="users.php" class="nav-btn">
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
                <h1>Review Moderation</h1>
                <p>Approve, reject, or delete user reviews</p>
            </div>
        </div>
        
        <?php if (isset($_SESSION['message'])): ?>
            <div class="alert alert-success">
                <?php echo $_SESSION['message']; unset($_SESSION['message']); ?>
            </div>
        <?php endif; ?>
        
        <div class="filter-section">
            <h3>Filter by Status:</h3>
            <div class="filter-buttons">
                <a href="reviews.php?status=all" class="btn <?php echo $status_filter === 'all' ? 'btn-primary' : 'btn-secondary'; ?>">All</a>
                <a href="reviews.php?status=pending" class="btn <?php echo $status_filter === 'pending' ? 'btn-primary' : 'btn-secondary'; ?>">Pending</a>
                <a href="reviews.php?status=approved" class="btn <?php echo $status_filter === 'approved' ? 'btn-primary' : 'btn-secondary'; ?>">Approved</a>
                <a href="reviews.php?status=rejected" class="btn <?php echo $status_filter === 'rejected' ? 'btn-primary' : 'btn-secondary'; ?>">Rejected</a>
            </div>
        </div>
        
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Student</th>
                        <th>Faculty</th>
                        <th>Course</th>
                        <th>Rating</th>
                        <th>Comments</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($review = $reviews_result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $review['id']; ?></td>
                        <td><?php echo htmlspecialchars($review['student_name']); ?></td>
                        <td><?php echo htmlspecialchars($review['faculty_name']); ?></td>
                        <td><?php echo htmlspecialchars($review['course_name']); ?></td>
                        <td>
                            <?php
                            for ($i = 1; $i <= 5; $i++) {
                                echo $i <= $review['rating'] ? '<i class="fas fa-star text-warning"></i>' : '<i class="far fa-star"></i>';
                            }
                            ?>
                        </td>
                        <td><?php echo htmlspecialchars(substr($review['comments'], 0, 50)); ?>...</td>
                        <td>
                            <span class="badge bg-<?php 
                            if ($review['status'] === 'approved') echo 'success';
                            elseif ($review['status'] === 'rejected') echo 'danger';
                            else echo 'warning';
                            ?>">
                                <?php echo ucfirst($review['status']); ?>
                            </span>
                        </td>
                        <td><?php echo date('M j, Y', strtotime($review['created_at'])); ?></td>
                        <td>
                            <div class="action-buttons">
                                <?php if ($review['status'] === 'pending'): ?>
                                    <a href="reviews.php?action=approve&id=<?php echo $review['id']; ?>" class="btn btn-sm btn-success">Approve</a>
                                    <a href="reviews.php?action=reject&id=<?php echo $review['id']; ?>" class="btn btn-sm btn-warning">Reject</a>
                                <?php endif; ?>
                                <a href="reviews.php?action=delete&id=<?php echo $review['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this review?')">Delete</a>
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