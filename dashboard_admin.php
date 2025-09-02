<?php
session_start();
include "DBconnect.php";

// Ensure only admins can access
if (!isset($_SESSION['username']) || !isset($_SESSION['id']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit();
}

// Get system statistics
$users_count = $conn->query("SELECT COUNT(*) as count FROM users")->fetch_assoc()['count'];
$faculty_count = $conn->query("SELECT COUNT(*) as count FROM faculty")->fetch_assoc()['count'];
$reviews_count = $conn->query("SELECT COUNT(*) as count FROM reviews")->fetch_assoc()['count'];
$pending_reviews = $conn->query("SELECT COUNT(*) as count FROM reviews WHERE status = 'pending'")->fetch_assoc()['count'];

// Get recent reviews
$recent_reviews_query = $conn->prepare("SELECT r.*, u.name as student_name, f.name as faculty_name 
                                       FROM reviews r 
                                       JOIN users u ON r.student_id = u.id 
                                       JOIN faculty f ON r.faculty_id = f.id 
                                       ORDER BY r.id DESC 
                                       LIMIT 5");
$recent_reviews_query->execute();
$recent_reviews_result = $recent_reviews_query->get_result();

$recent_reviews = [];
while ($row = mysqli_fetch_assoc($recent_reviews_result)) {
    $recent_reviews[] = $row;
}

// Get members data - check if created_at column exists first
$column_check = $conn->query("SHOW COLUMNS FROM users LIKE 'created_at'");
if ($column_check->num_rows > 0) {
    // Column exists, use it for ordering
    $members_res = $conn->query("SELECT * FROM users ORDER BY created_at DESC");
} else {
    // Column doesn't exist, order by id instead
    $members_res = $conn->query("SELECT * FROM users ORDER BY id DESC");
}

// Get current user's profile picture
$profile_picture = null;
$stmt = $conn->prepare("SELECT profile_picture FROM users WHERE id = ?");
$stmt->bind_param("i", $_SESSION['id']);
$stmt->execute();
$result = $stmt->get_result();
if ($row = $result->fetch_assoc()) {
    $profile_picture = $row['profile_picture'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Faculty Hub - Admin Dashboard</title>
    <link rel="stylesheet" href="dashboardstyle.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Responsive action buttons */
        .action-buttons {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
            justify-content: center;
        }

        .action-buttons .btn {
            display: flex;
            align-items: center;
            padding: 0.375rem 0.75rem;
            white-space: nowrap;
        }

        .action-buttons .btn i {
            margin-right: 0.25rem;
        }

        .btn-text {
            display: inline;
        }

        /* Responsive adjustments for smaller screens */
        @media (max-width: 768px) {
            .action-buttons {
                flex-direction: column;
                gap: 0.25rem;
            }
            
            .action-buttons .btn {
                width: 100%;
                justify-content: center;
            }
            
            .btn-text {
                display: none;
            }
            
            .action-buttons .btn i {
                margin-right: 0;
            }
            
            .table-responsive {
                overflow-x: auto;
            }
            
            .members-section table th:nth-child(2),
            .members-section table td:nth-child(2) {
                min-width: 120px;
            }
        }
        
        /* Form styles */
        .user-form {
            max-width: 600px;
            margin: 0 auto;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 1rem;
        }

        .form-actions {
            display: flex;
            gap: 1rem;
            justify-content: flex-end;
            margin-top: 2rem;
        }

        .alert {
            padding: 1rem;
            margin-bottom: 1.5rem;
            border-radius: 4px;
        }

        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
    </style>
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
                <a href="dashboard_admin.php" class="nav-btn active">
                    <i class="fas fa-home"></i>
                    <span>Dashboard</span>
                </a>
                <a href="components/faculty_management.php" class="nav-btn">
                    <i class="fas fa-users"></i>
                    <span>Faculty</span>
                </a>
                <a href="components/reviews.php" class="nav-btn">
                    <i class="fas fa-star"></i>
                    <span>Reviews</span>
                </a>
                <a href="components/users.php" class="nav-btn">
                    <i class="fas fa-user-cog"></i>
                    <span>Users</span>
                </a>
                <a href="components/reports.php" class="nav-btn">
                    <i class="fas fa-chart-bar"></i>
                    <span>Activity</span>
                </a>
                <a href="logout.php" class="nav-btn">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Logout</span>
                </a>
            </div>
        </div>
    </nav>

    <div class="dashboard-container">
        <div class="dashboard-header">
            <div class="welcome-section">
                <h1>Admin Dashboard, <?php echo htmlspecialchars($_SESSION['name']); ?>!</h1>
                <p>Manage the Faculty Hub system and monitor activities</p>
            </div>
            
            <div class="quick-stats">
                <div class="stat-item">
                    <div class="stat-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-number"><?php echo $users_count; ?></div>
                        <div class="stat-label">Users</div>
                    </div>
                </div>
                <div class="stat-item">
                    <div class="stat-icon">
                        <i class="fas fa-chalkboard-teacher"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-number"><?php echo $faculty_count; ?></div>
                        <div class="stat-label">Faculty</div>
                    </div>
                </div>
                <div class="stat-item">
                    <div class="stat-icon">
                        <i class="fas fa-star"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-number"><?php echo $reviews_count; ?></div>
                        <div class="stat-label">Reviews</div>
                    </div>
                </div>
                <div class="stat-item">
                    <div class="stat-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-number"><?php echo $pending_reviews; ?></div>
                        <div class="stat-label">Pending</div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="flex-container">
            <div class="user-card">
                <div class="user-avatar admin">
                    <?php if ($profile_picture && file_exists('img/uploads/' . $profile_picture)): ?>
                        <img src="img/uploads/<?php echo htmlspecialchars($profile_picture); ?>" alt="Profile Picture">
                    <?php else: ?>
                        <i class="fas fa-user"></i>
                    <?php endif; ?>
                </div>
                <h3><?php echo htmlspecialchars($_SESSION['name']); ?></h3>
                <p class="user-role"><?php echo ucfirst($_SESSION['role']); ?></p>
                <div class="user-stats">
                    <div class="user-stat">
                        <span class="stat-value"><?php echo $users_count; ?></span>
                        <span class="stat-label">Users</span>
                    </div>
                    <div class="user-stat">
                        <span class="stat-value"><?php echo $reviews_count; ?></span>
                        <span class="stat-label">Reviews</span>
                    </div>
                </div>
                <div style="margin-top: 15px;">
                    <a href="components/upload_profile.php" class="btn btn-primary">
                        <i class="fas fa-camera"></i> Update Profile Picture
                    </a>
                </div>
                <a href="logout.php" class="btn btn-dark">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
            
            <div style="flex: 1;">
                <div class="dashboard-grid">
                    <div class="dashboard-card">
                        <div class="card-header">
                            <h3><i class="fas fa-tachometer-alt"></i> Quick Actions</h3>
                        </div>
                        <div class="card-content">
                            <div class="action-buttons">
                                <a href="components/users.php" class="action-btn">
                                    <i class="fas fa-user-plus"></i>
                                    <div class="action-content">
                                        <span>Manage Users</span>
                                        <small>Add, edit or remove users</small>
                                    </div>
                                </a>
                                
                                <a href="components/reviews.php" class="action-btn">
                                    <i class="fas fa-star"></i>
                                    <div class="action-content">
                                        <span>Review Moderation</span>
                                        <small>Approve or reject reviews</small>
                                    </div>
                                </a>
                                
                                <a href="components/faculty_management.php" class="action-btn">
                                    <i class="fas fa-chalkboard-teacher"></i>
                                    <div class="action-content">
                                        <span>Manage Faculty</span>
                                        <small>Add or edit faculty members</small>
                                    </div>
                                </a>
                                
                                <a href="components/reports.php" class="action-btn">
                                    <i class="fas fa-chart-bar"></i>
                                    <div class="action-content">
                                        <span>Activity</span>
                                        <small>View system activity</small>
                                    </div>
                                </a>
                            </div>
                        </div>
                    </div>
                    
                    <div class="dashboard-card">
                        <div class="card-header">
                            <h3><i class="fas fa-clock"></i> Recent Reviews</h3>
                        </div>
                        <div class="card-content">
                            <?php if (empty($recent_reviews)): ?>
                                <div class="no-activity">
                                    <i class="fas fa-inbox"></i>
                                    <p>No reviews yet</p>
                                </div>
                            <?php else: ?>
                                <div class="activity-list">
                                    <?php foreach ($recent_reviews as $review): ?>
                                        <div class="activity-item">
                                            <div class="activity-icon">
                                                <i class="fas fa-star"></i>
                                            </div>
                                            <div class="activity-content">
                                                <div class="activity-title">
                                                    <?php echo htmlspecialchars($review['student_name']); ?> reviewed 
                                                    <?php echo htmlspecialchars($review['faculty_name']); ?>
                                                </div>
                                                <div class="activity-meta">
                                                    <?php 
                                                    // Check if created_at exists for this review
                                                    if (isset($review['created_at'])) {
                                                        echo date('M j, Y', strtotime($review['created_at']));
                                                    } else {
                                                        echo "Date not available";
                                                    }
                                                    ?> • 
                                                    <span class="status status-<?php echo $review['status']; ?>">
                                                        <?php echo ucfirst($review['status']); ?>
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="activity-rating">
                                                <?php
                                                $rating = isset($review['rating']) ? $review['rating'] : 0;
                                                for ($i = 1; $i <= 5; $i++) {
                                                    echo $i <= $rating ? '<i class="fas fa-star"></i>' : '<i class="far fa-star"></i>';
                                                }
                                                ?>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                
                                <div class="card-footer">
                                    <a href="components/reviews.php" class="btn btn-secondary">
                                        <i class="fas fa-list"></i> View All Reviews
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <div class="members-section">
                    <div class="section-header">
                        <h2><i class="fas fa-users"></i> System Members</h2>
                        <a href="components/users.php" class="btn btn-sm btn-primary">View All</a>
                    </div>
                    
                    <?php if (mysqli_num_rows($members_res) > 0) { ?>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th scope="col">#</th>
                                    <th scope="col">Name</th>
                                    <th scope="col">User name</th>
                                    <th scope="col">Role</th>
                                    <th scope="col" class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $i = 1;
                                while ($rows = mysqli_fetch_assoc($members_res)) { 
                                    if ($i > 5) break; // Show only 5 users
                                ?>
                                <tr>
                                    <th scope="row"><?php echo $i; ?></th>
                                    <td><?php echo htmlspecialchars($rows['name']); ?></td>
                                    <td><?php echo htmlspecialchars($rows['username']); ?></td>
                                    <td>
                                        <span class="badge bg-<?php echo $rows['role'] == 'admin' ? 'primary' : 'secondary'; ?>">
                                            <?php echo ucfirst($rows['role']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="action-buttons">
                                            <a href="components/edit_user.php?id=<?php echo $rows['id']; ?>" class="btn btn-sm btn-primary">
                                                <i class="fas fa-edit"></i>
                                                <span class="btn-text">Edit</span>
                                            </a>
                                            <a href="components/delete_user.php?id=<?php echo $rows['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this user?')">
                                                <i class="fas fa-trash"></i>
                                                <span class="btn-text">Delete</span>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                <?php $i++; } ?>
                            </tbody>
                        </table>
                    </div>
                    <?php } else { ?>
                        <div class="no-activity">
                            <i class="fas fa-users-slash"></i>
                            <p>No users found</p>
                        </div>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
<?php
// Close database connection
$conn->close();
?>