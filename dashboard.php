<?php 
session_start();
include "DBconnect.php";
if (isset($_SESSION['username']) && isset($_SESSION['id'])) { 
    
// Get user's reviews (assuming you have a reviews table)
$user_id = $_SESSION['id'];
$review_query = mysqli_query($conn, "SELECT * FROM reviews WHERE student_id = '$user_id'");
$student_reviews = [];
while ($row = mysqli_fetch_assoc($review_query)) {
    $student_reviews[] = $row;
}

// Get stats
$total_reviews = count($student_reviews);
$pending_reviews = 0;
$approved_reviews = 0;

foreach ($student_reviews as $review) {
    if ($review['status'] === 'pending') {
        $pending_reviews++;
    } elseif ($review['status'] === 'approved') {
        $approved_reviews++;
    }
}

// Get recent faculty (top rated) - assuming you have a faculty table
$faculty_query = mysqli_query($conn, "SELECT f.*, d.name as department_name, 
                                     AVG(r.rating) as avg_rating, COUNT(r.id) as total_reviews
                                     FROM faculty f 
                                     LEFT JOIN departments d ON f.department_id = d.id 
                                     LEFT JOIN reviews r ON f.id = r.faculty_id 
                                     GROUP BY f.id 
                                     ORDER BY avg_rating DESC 
                                     LIMIT 6");
$recent_faculty = [];
while ($row = mysqli_fetch_assoc($faculty_query)) {
    $recent_faculty[] = $row;
}

// Function to format date
function format_date($date) {
    return date('M j, Y', strtotime($date));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Faculty Hub - Dashboard</title>
    <link rel="stylesheet" href="dashboardstyle.css">
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
                <div class="brand-text">FacultyHub</div>
            </div>
            <div class="nav-menu">
                <a href="dashboard.php" class="nav-btn active">
                    <i class="fas fa-home"></i>
                    <span>Dashboard</span>
                </a>
                <a href="faculty.php" class="nav-btn">
                    <i class="fas fa-users"></i>
                    <span>Faculty</span>
                </a>
                <a href="my-reviews.php" class="nav-btn">
                    <i class="fas fa-star"></i>
                    <span>My Reviews</span>
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
                <h1>Welcome back, <?php echo htmlspecialchars($_SESSION['name']); ?>!</h1>
                <p>Ready to share your experience and help improve education quality?</p>
            </div>
            
            <div class="quick-stats">
                <div class="stat-item">
                    <div class="stat-number"><?php echo $total_reviews; ?></div>
                    <div class="stat-label">Total Reviews</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number"><?php echo $pending_reviews; ?></div>
                    <div class="stat-label">Pending</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number"><?php echo $approved_reviews; ?></div>
                    <div class="stat-label">Approved</div>
                </div>
            </div>
        </div>
        
        <div class="flex-container">
            <div class="user-card">
                <img src="<?php echo $_SESSION['role'] == 'admin' ? 'img/admin-default.png' : 'img/user-default.png'; ?>" 
                     class="card-img-top" 
                     alt="user image">
                <h5><?php echo htmlspecialchars($_SESSION['name']); ?></h5>
                <p class="text-muted"><?php echo ucfirst($_SESSION['role']); ?></p>
                <a href="logout.php" class="btn btn-dark">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
            
            <?php if ($_SESSION['role'] == 'admin') { ?>
            <div style="flex: 1;">
                <div class="members-section">
                    <h1><i class="fas fa-users"></i> System Members</h1>
                    <?php 
                    include 'php/members.php';
                    if (mysqli_num_rows($res) > 0) { ?>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th scope="col">#</th>
                                    <th scope="col">Name</th>
                                    <th scope="col">User name</th>
                                    <th scope="col">Role</th>
                                    <th scope="col">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $i = 1;
                                while ($rows = mysqli_fetch_assoc($res)) { ?>
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
                                        <a href="edit_user.php?id=<?php echo $rows['id']; ?>" class="btn btn-sm btn-primary">Edit</a>
                                        <a href="delete_user.php?id=<?php echo $rows['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this user?')">Delete</a>
                                    </td>
                                </tr>
                                <?php $i++; } ?>
                            </tbody>
                        </table>
                    </div>
                    <?php } ?>
                </div>
            </div>
            <?php } else { ?>
            <div style="flex: 1;">
                <div class="dashboard-grid">
                    <div class="dashboard-card">
                        <div class="card-header">
                            <h3><i class="fas fa-plus-circle"></i> Quick Actions</h3>
                        </div>
                        <div class="card-content">
                            <div class="action-buttons">
                                <a href="faculty.php" class="action-btn">
                                    <i class="fas fa-search"></i>
                                    <span>Browse Faculty</span>
                                    <small>Find and review faculty members</small>
                                </a>
                                
                                <a href="my-reviews.php" class="action-btn">
                                    <i class="fas fa-list"></i>
                                    <span>My Reviews</span>
                                    <small>View and manage your reviews</small>
                                </a>
                                
                                <a href="faculty.php?sort=rating" class="action-btn">
                                    <i class="fas fa-star"></i>
                                    <span>Top Rated Faculty</span>
                                    <small>Discover highly rated professors</small>
                                </a>
                            </div>
                        </div>
                    </div>
                    
                    <div class="dashboard-card">
                        <div class="card-header">
                            <h3><i class="fas fa-clock"></i> Recent Activity</h3>
                        </div>
                        <div class="card-content">
                            <?php if (empty($student_reviews)): ?>
                                <div class="no-activity">
                                    <i class="fas fa-inbox"></i>
                                    <p>No reviews yet</p>
                                    <a href="faculty.php" class="btn btn-primary">
                                        <i class="fas fa-plus"></i> Submit Your First Review
                                    </a>
                                </div>
                            <?php else: ?>
                                <div class="activity-list">
                                    <?php 
                                    $recent_reviews = array_slice($student_reviews, 0, 5);
                                    foreach ($recent_reviews as $review): 
                                        // Get faculty name
                                        $faculty_id = $review['faculty_id'];
                                        $faculty_result = mysqli_query($conn, "SELECT name FROM faculty WHERE id = '$faculty_id'");
                                        $faculty_name = "Unknown Faculty";
                                        if ($faculty_result && mysqli_num_rows($faculty_result) > 0) {
                                            $faculty_name = mysqli_fetch_assoc($faculty_result)['name'];
                                        }
                                        
                                        // Get course name
                                        $course_id = $review['course_id'];
                                        $course_result = mysqli_query($conn, "SELECT name FROM courses WHERE id = '$course_id'");
                                        $course_name = "Unknown Course";
                                        if ($course_result && mysqli_num_rows($course_result) > 0) {
                                            $course_name = mysqli_fetch_assoc($course_result)['name'];
                                        }
                                    ?>
                                        <div class="activity-item">
                                            <div class="activity-icon">
                                                <i class="fas fa-star"></i>
                                            </div>
                                            <div class="activity-content">
                                                <div class="activity-title">
                                                    Review for <?php echo htmlspecialchars($faculty_name); ?>
                                                </div>
                                                <div class="activity-meta">
                                                    <?php echo htmlspecialchars($course_name); ?> • 
                                                    <?php echo format_date($review['created_at']); ?> • 
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
                                    <a href="my-reviews.php" class="btn btn-secondary">
                                        <i class="fas fa-list"></i> View All Reviews
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <?php if (!empty($recent_faculty)): ?>
                <div class="featured-section">
                    <h2><i class="fas fa-users"></i> Featured Faculty</h2>
                    <div class="faculty-grid">
                        <?php foreach ($recent_faculty as $faculty_member): ?>
                            <div class="faculty-card">
                                <div class="faculty-avatar">
                                    <i class="fas fa-user-tie"></i>
                                </div>
                                <div class="faculty-info">
                                    <h3><?php echo htmlspecialchars($faculty_member['name']); ?></h3>
                                    <p class="faculty-designation"><?php echo htmlspecialchars($faculty_member['designation']); ?></p>
                                    <p class="faculty-department"><?php echo htmlspecialchars($faculty_member['department_name']); ?></p>
                                    
                                    <?php if ($faculty_member['avg_rating']): ?>
                                        <div class="faculty-rating">
                                            <div class="stars">
                                                <?php
                                                $rating = round($faculty_member['avg_rating']);
                                                for ($i = 1; $i <= 5; $i++) {
                                                    echo $i <= $rating ? '<i class="fas fa-star"></i>' : '<i class="far fa-star"></i>';
                                                }
                                                ?>
                                            </div>
                                            <span class="rating-text">
                                                <?php echo number_format($faculty_member['avg_rating'], 1); ?> 
                                                (<?php echo $faculty_member['total_reviews']; ?> reviews)
                                            </span>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <a href="faculty-detail.php?id=<?php echo $faculty_member['id']; ?>" class="btn btn-sm btn-primary">
                                        <i class="fas fa-eye"></i> View Profile
                                    </a>
                                    <a href="add-review.php?faculty_id=<?php echo $faculty_member['id']; ?>" class="btn btn-sm btn-success">
                                        <i class="fas fa-star"></i> Add Review
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            <?php } ?>
        </div>
    </div>
</body>
</html>
<?php } else {
    header("Location: index.php");
} ?>