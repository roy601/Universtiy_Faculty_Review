<?php
session_start();
include "../DBconnect.php";

// Ensure only admins can access
if (!isset($_SESSION['username']) || !isset($_SESSION['id']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit();
}

// Get statistics for reports
$total_users = $conn->query("SELECT COUNT(*) as count FROM users")->fetch_assoc()['count'];
$total_faculty = $conn->query("SELECT COUNT(*) as count FROM faculty")->fetch_assoc()['count'];
$total_reviews = $conn->query("SELECT COUNT(*) as count FROM reviews")->fetch_assoc()['count'];
$pending_reviews = $conn->query("SELECT COUNT(*) as count FROM reviews WHERE status = 'pending'")->fetch_assoc()['count'];

// Get reviews by status
$reviews_by_status = $conn->query("SELECT status, COUNT(*) as count FROM reviews GROUP BY status");

// Get top rated faculty
$top_faculty = $conn->query("
    SELECT f.name, AVG(r.rating) as avg_rating, COUNT(r.id) as review_count 
    FROM faculty f 
    LEFT JOIN reviews r ON f.id = r.faculty_id 
    WHERE r.status = 'approved'
    GROUP BY f.id 
    HAVING avg_rating IS NOT NULL 
    ORDER BY avg_rating DESC 
    LIMIT 5
");

// Get recent activity from admin logs
$recent_activity = $conn->query("
    SELECT al.*, u.name as admin_name 
    FROM admin_logs al 
    JOIN users u ON al.admin_id = u.id 
    ORDER BY al.created_at DESC 
    LIMIT 10
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Reports - Faculty Hub</title>
    <link rel="stylesheet" href="../dashboardstyle.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
                <a href="users.php" class="nav-btn">
                    <i class="fas fa-user-cog"></i>
                    <span>Users</span>
                </a>
                <a href="reports.php" class="nav-btn active">
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
                <h1>System Activity</h1>
                
            </div>
        </div>
<!--         
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-number"><?php echo $total_users; ?></div>
                    <div class="stat-label">Total Users</div>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-chalkboard-teacher"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-number"><?php echo $total_faculty; ?></div>
                    <div class="stat-label">Faculty Members</div>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-star"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-number"><?php echo $total_reviews; ?></div>
                    <div class="stat-label">Total Reviews</div>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-number"><?php echo $pending_reviews; ?></div>
                    <div class="stat-label">Pending Reviews</div>
                </div>
            </div>
        </div> -->
        
        <div class="recent-activity">
            <h3>Recent Admin Activity</h3>
            <div class="activity-list">
                <?php while ($activity = $recent_activity->fetch_assoc()): ?>
                <div class="activity-item">
                    <div class="activity-icon">
                        <i class="fas fa-history"></i>
                    </div>
                    <div class="activity-content">
                        <div class="activity-title">
                            <?php echo htmlspecialchars($activity['admin_name']); ?> - <?php echo htmlspecialchars($activity['action']); ?>
                        </div>
                        <div class="activity-meta">
                            <?php echo date('M j, Y g:i A', strtotime($activity['created_at'])); ?>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
        </div>
    </div>

    <script>
        // Reviews by Status Chart
        const statusCtx = document.getElementById('statusChart').getContext('2d');
        const statusChart = new Chart(statusCtx, {
            type: 'pie',
            data: {
                labels: [
                    <?php 
                    $reviews_by_status->data_seek(0);
                    while ($status = $reviews_by_status->fetch_assoc()): 
                        echo "'" . ucfirst($status['status']) . "',";
                    endwhile; 
                    ?>
                ],
                datasets: [{
                    data: [
                        <?php 
                        $reviews_by_status->data_seek(0);
                        while ($status = $reviews_by_status->fetch_assoc()): 
                            echo $status['count'] . ",";
                        endwhile; 
                        ?>
                    ],
                    backgroundColor: [
                        '#FF6384',
                        '#36A2EB',
                        '#FFCE56'
                    ]
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
        
        // Top Faculty Chart
        const facultyCtx = document.getElementById('topFacultyChart').getContext('2d');
        const facultyChart = new Chart(facultyCtx, {
            type: 'bar',
            data: {
                labels: [
                    <?php 
                    while ($faculty = $top_faculty->fetch_assoc()): 
                        echo "'" . addslashes($faculty['name']) . "',";
                    endwhile; 
                    ?>
                ],
                datasets: [{
                    label: 'Average Rating',
                    data: [
                        <?php 
                        $top_faculty->data_seek(0);
                        while ($faculty = $top_faculty->fetch_assoc()): 
                            echo number_format($faculty['avg_rating'], 1) . ",";
                        endwhile; 
                        ?>
                    ],
                    backgroundColor: '#4BC0C0'
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 5
                    }
                }
            }
        });
    </script>
</body>
</html>
<?php $conn->close(); ?>