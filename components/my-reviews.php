<?php
session_start();
include "../DBconnect.php";

if (!isset($_SESSION['username']) || !isset($_SESSION['id'])) {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['id'];
$query = "SELECT r.*, f.name as faculty_name, c.name as course_name 
          FROM reviews r 
          JOIN faculty f ON r.faculty_id = f.id 
          JOIN courses c ON r.course_id = c.id 
          WHERE r.student_id = '$user_id' 
          ORDER BY r.created_at DESC";

$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Reviews - Faculty Hub</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        /* Reset and base styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #333;
            min-height: 100vh;
            position: relative;
            overflow-x: hidden;
            padding-top: 80px;
        }

        .animated-bg {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
            overflow: hidden;
        }

        .gradient-orb {
            position: absolute;
            border-radius: 50%;
            filter: blur(80px);
            opacity: 0.2;
        }

        .orb-1 {
            width: 300px;
            height: 300px;
            background: linear-gradient(45deg, #ff9a9e 0%, #fad0c4 100%);
            top: 10%;
            left: 10%;
            animation: float 15s infinite ease-in-out;
        }

        .orb-2 {
            width: 400px;
            height: 400px;
            background: linear-gradient(45deg, #a1c4fd 0%, #c2e9fb 100%);
            bottom: 10%;
            right: 10%;
            animation: float 18s infinite ease-in-out reverse;
        }

        .orb-3 {
            width: 250px;
            height: 250px;
            background: linear-gradient(45deg, #ffecd2 0%, #fcb69f 100%);
            top: 50%;
            right: 20%;
            animation: float 12s infinite ease-in-out;
        }

        @keyframes float {
            0%, 100% {
                transform: translate(0, 0) rotate(0deg);
            }
            25% {
                transform: translate(20px, 30px) rotate(5deg);
            }
            50% {
                transform: translate(0, 60px) rotate(0deg);
            }
            75% {
                transform: translate(-20px, 30px) rotate(-5deg);
            }
        }

        /* Navigation */
        .navbar {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
            padding: 0.8rem 0;
            position: fixed;
            top: 0;
            width: 100%;
            z-index: 1000;
        }

        .nav-container {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 1.5rem;
        }

        .nav-brand {
            display: flex;
            align-items: center;
            gap: 0.8rem;
        }

        .brand-icon {
            width: 40px;
            height: 40px;
            background: linear-gradient(45deg, #667eea, #764ba2);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.2rem;
        }

        .brand-text {
            font-size: 1.4rem;
            font-weight: 700;
            color: white;
        }

        .nav-menu {
            display: flex;
            gap: 0.5rem;
        }

        .nav-btn {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.7rem 1.2rem;
            border-radius: 8px;
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .nav-btn:hover {
            background: rgba(255, 255, 255, 0.1);
            color: white;
        }

        .nav-btn.active {
            background: rgba(255, 255, 255, 0.2);
            color: white;
        }

        .nav-btn i {
            font-size: 1.1rem;
        }

        /* Container */
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 1.5rem;
        }

        /* Section header */
        .section-header {
            margin-bottom: 2rem;
        }

        .section-title {
            color: white;
            font-size: 2.2rem;
            margin-bottom: 0.5rem;
        }

        .section-subtitle {
            color: rgba(255, 255, 255, 0.8);
            font-size: 1.1rem;
        }

        /* Buttons */
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.7rem 1.5rem;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
        }

        .btn-primary {
            background: linear-gradient(45deg, #667eea, #764ba2);
            color: white;
        }

        .btn-primary:hover {
            background: linear-gradient(45deg, #5a6fd8, #6a4190);
            transform: translateY(-2px);
        }

        .btn-sm {
            padding: 0.4rem 0.8rem;
            font-size: 0.8rem;
        }

        .btn-danger {
            background: linear-gradient(45deg, #ff4757, #dc3545);
            color: white;
        }

        .btn-danger:hover {
            background: linear-gradient(45deg, #e84151, #c82333);
            transform: translateY(-2px);
        }

        /* Reviews list */
        .reviews-list {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }

        .review-item {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 16px;
            padding: 2rem;
            transition: all 0.3s ease;
        }

        .review-item:hover {
            background: rgba(255, 255, 255, 0.15);
            transform: translateY(-5px);
        }

        .review-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .review-header h3 {
            color: white;
            font-size: 1.5rem;
        }

        .course {
            background: rgba(255, 255, 255, 0.1);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.9rem;
        }

        .status {
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 600;
        }

        .status-approved {
            background: rgba(46, 213, 115, 0.2);
            color: #2ed573;
        }

        .status-pending {
            background: rgba(255, 177, 66, 0.2);
            color: #ffb142;
        }

        .status-rejected {
            background: rgba(255, 71, 87, 0.2);
            color: #ff4757;
        }

        .review-rating {
            margin-bottom: 1rem;
        }

        .review-rating i {
            color: #ffce54;
            font-size: 1.2rem;
        }

        .review-comments {
            margin-bottom: 1.5rem;
        }

        .review-comments p {
            color: rgba(255, 255, 255, 0.9);
            line-height: 1.6;
        }

        .review-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .date {
            color: rgba(255, 255, 255, 0.7);
            font-size: 0.9rem;
        }

        .review-actions {
            display: flex;
            gap: 0.5rem;
        }

        .no-reviews {
            text-align: center;
            padding: 4rem 2rem;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 16px;
        }

        .no-reviews i {
            font-size: 3rem;
            color: rgba(255, 255, 255, 0.5);
            margin-bottom: 1.5rem;
        }

        .no-reviews p {
            color: rgba(255, 255, 255, 0.8);
            font-size: 1.2rem;
            margin-bottom: 2rem;
        }

        /* Responsive styles */
        @media (max-width: 768px) {
            body {
                padding-top: 100px;
            }
            
            .nav-container {
                flex-direction: column;
                gap: 1rem;
            }
        
            .nav-menu {
                flex-wrap: wrap;
                justify-content: center;
            }
        
            .section-title {
                font-size: 1.8rem;
            }
            
            .review-header {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .review-footer {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .review-actions {
                width: 100%;
                justify-content: flex-end;
            }
        }
    </style>
</head>
<body>
    <!-- Animated Background -->
    <div class="animated-bg">
        <div class="gradient-orb orb-1"></div>
        <div class="gradient-orb orb-2"></div>
        <div class="gradient-orb orb-3"></div>
    </div>
    
    <!-- Navigation -->
    <nav class="navbar">
        <div class="nav-container">
            <div class="nav-brand">
                <div class="brand-icon">
                    <i class="fas fa-graduation-cap"></i>
                </div>
                <div class="brand-text">FacultyHub</div>
            </div>
            <div class="nav-menu">
                <a href="../dashboard_user.php" class="nav-btn">
                    <i class="fas fa-home"></i>
                    <span>Dashboard</span>
                </a>
                <a href="faculty.php" class="nav-btn">
                    <i class="fas fa-users"></i>
                    <span>Faculty</span>
                </a>
                <a href="my-reviews.php" class="nav-btn active">
                    <i class="fas fa-star"></i>
                    <span>My Reviews</span>
                </a>
                <a href="../logout.php" class="nav-btn">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Logout</span>
                </a>
            </div>
        </div>
    </nav>
    
    <div class="container">
        <div class="section-header">
            <h1 class="section-title">My Reviews</h1>
            <p class="section-subtitle">View and manage your submitted reviews</p>
        </div>
        
        <?php if (mysqli_num_rows($result) > 0): ?>
            <div class="reviews-list">
                <?php while ($review = mysqli_fetch_assoc($result)): ?>
                    <div class="review-item">
                        <div class="review-header">
                            <h3><?php echo htmlspecialchars($review['faculty_name']); ?></h3>
                            <span class="course"><?php echo htmlspecialchars($review['course_name']); ?></span>
                            <span class="status status-<?php echo $review['status']; ?>">
                                <?php echo ucfirst($review['status']); ?>
                            </span>
                        </div>
                        
                        <div class="review-rating">
                            <?php
                            for ($i = 1; $i <= 5; $i++) {
                                echo $i <= $review['rating'] ? '<i class="fas fa-star"></i>' : '<i class="far fa-star"></i>';
                            }
                            ?>
                        </div>
                        
                        <div class="review-comments">
                            <p><?php echo htmlspecialchars($review['comments']); ?></p>
                        </div>
                        
                        <div class="review-footer">
                            <span class="date"><?php echo date('M j, Y', strtotime($review['created_at'])); ?></span>
                            <div class="review-actions">
                                <a href="edit-review.php?id=<?php echo $review['id']; ?>" class="btn btn-sm btn-primary">Edit</a>
                                <a href="delete-review.php?id=<?php echo $review['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this review?')">Delete</a>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="no-reviews">
                <i class="fas fa-inbox"></i>
                <p>You haven't submitted any reviews yet.</p>
                <a href="faculty.php" class="btn btn-primary">Browse Faculty</a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>