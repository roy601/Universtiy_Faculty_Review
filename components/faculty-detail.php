<?php
session_start();
include "../DBconnect.php";

if (!isset($_SESSION['username']) || !isset($_SESSION['id'])) {
    header("Location: index.php");
    exit();
}

// Check if faculty id is provided
if (!isset($_GET['id'])) {
    header("Location: faculty.php");
    exit();
}

$faculty_id = $_GET['id'];

// Get faculty details
$faculty_query = mysqli_query($conn, "SELECT f.*, d.name as department_name, 
                                     AVG(r.rating) as avg_rating, COUNT(r.id) as total_reviews
                                     FROM faculty f 
                                     LEFT JOIN departments d ON f.department_id = d.id 
                                     LEFT JOIN reviews r ON f.id = r.faculty_id 
                                     WHERE f.id = '$faculty_id' 
                                     GROUP BY f.id");
$faculty = mysqli_fetch_assoc($faculty_query);

// Get reviews for this faculty
$reviews_query = mysqli_query($conn, "SELECT r.*, u.name as student_name, c.name as course_name 
                                     FROM reviews r 
                                     JOIN users u ON r.student_id = u.id 
                                     JOIN courses c ON r.course_id = c.id 
                                     WHERE r.faculty_id = '$faculty_id' AND r.status = 'approved' 
                                     ORDER BY r.created_at DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($faculty['name']); ?> - Faculty Hub</title>
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

        /* Faculty profile */
        .faculty-profile {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 16px;
            overflow: hidden;
            margin-bottom: 2rem;
        }

        .faculty-header {
            display: flex;
            padding: 2rem;
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.8) 0%, rgba(118, 75, 162, 0.8) 100%);
            color: white;
            flex-wrap: wrap;
        }

        .faculty-avatar {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 2rem;
            flex-shrink: 0;
        }

        .faculty-avatar i {
            font-size: 3rem;
            color: white;
        }

        .faculty-info {
            flex: 1;
            min-width: 300px;
        }

        .faculty-info h1 {
            font-size: 2rem;
            margin-bottom: 0.5rem;
            font-weight: 700;
            color: white;
        }

        .designation {
            font-size: 1.1rem;
            margin-bottom: 0.3rem;
            color: rgba(255, 255, 255, 0.9);
        }

        .department {
            font-size: 1rem;
            margin-bottom: 1rem;
            color: rgba(255, 255, 255, 0.8);
        }

        .email {
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: rgba(255, 255, 255, 0.9);
        }

        .faculty-rating {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-top: 1rem;
        }

        .stars {
            display: flex;
            gap: 0.2rem;
        }

        .stars i {
            color: #ffce54;
            font-size: 1.2rem;
        }

        .rating-text {
            font-size: 1rem;
            font-weight: 500;
            color: rgba(255, 255, 255, 0.9);
        }

        .faculty-actions {
            display: flex;
            align-items: center;
            justify-content: center;
            margin-left: 1rem;
        }

        /* Faculty bio */
        .faculty-bio {
            padding: 1.5rem 2rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .faculty-bio h2 {
            color: white;
            margin-bottom: 1rem;
            font-size: 1.5rem;
        }

        .faculty-bio p {
            line-height: 1.6;
            color: rgba(255, 255, 255, 0.9);
        }

        /* Faculty reviews */
        .faculty-reviews {
            padding: 1.5rem 2rem;
        }

        .faculty-reviews h2 {
            color: white;
            margin-bottom: 1.5rem;
            font-size: 1.5rem;
            padding-bottom: 0.5rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .reviews-list {
            display: grid;
            gap: 1rem;
        }

        .review-item {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 12px;
            padding: 1.5rem;
            border-left: 4px solid #667eea;
        }

        .review-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 0.8rem;
            flex-wrap: wrap;
            gap: 0.5rem;
        }

        .review-header h4 {
            color: white;
            font-size: 1.1rem;
        }

        .course {
            background: rgba(102, 126, 234, 0.3);
            color: white;
            padding: 0.3rem 0.8rem;
            border-radius: 20px;
            font-size: 0.8rem;
        }

        .review-rating {
            margin-bottom: 0.8rem;
        }

        .review-rating i {
            color: #ffce54;
            font-size: 1rem;
        }

        .review-comments {
            margin-bottom: 1rem;
        }

        .review-comments p {
            color: rgba(255, 255, 255, 0.9);
            line-height: 1.6;
        }

        .review-footer {
            display: flex;
            justify-content: flex-end;
        }

        .date {
            color: rgba(255, 255, 255, 0.7);
            font-size: 0.8rem;
        }

        .no-reviews {
            text-align: center;
            padding: 3rem 2rem;
            color: rgba(255, 255, 255, 0.7);
        }

        .no-reviews i {
            font-size: 3rem;
            margin-bottom: 1rem;
            color: rgba(255, 255, 255, 0.5);
        }

        .no-reviews p {
            font-size: 1.1rem;
        }

        /* Responsive styles */
        @media (max-width: 900px) {
            .faculty-header {
                flex-direction: column;
                text-align: center;
                align-items: center;
            }
            
            .faculty-avatar {
                margin-right: 0;
                margin-bottom: 1.5rem;
            }
            
            .faculty-actions {
                margin-left: 0;
                margin-top: 1.5rem;
                width: 100%;
            }
            
            .faculty-info {
                min-width: auto;
            }
        }
        
        @media (max-width: 600px) {
            .faculty-avatar {
                width: 100px;
                height: 100px;
            }
            
            .faculty-avatar i {
                font-size: 2.5rem;
            }
            
            .faculty-info h1 {
                font-size: 1.8rem;
            }
            
            .review-header {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .btn {
                width: 100%;
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
                <a href="my-reviews.php" class="nav-btn">
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
        <div class="faculty-profile">
            <div class="faculty-header">
                <div class="faculty-avatar">
                    <i class="fas fa-user-tie"></i>
                </div>
                <div class="faculty-info">
                    <h1><?php echo htmlspecialchars($faculty['name']); ?></h1>
                    <p class="designation"><?php echo htmlspecialchars($faculty['designation']); ?></p>
                    <p class="department"><?php echo htmlspecialchars($faculty['department_name']); ?></p>
                    
                    <?php if ($faculty['email']): ?>
                        <p class="email"><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($faculty['email']); ?></p>
                    <?php endif; ?>
                    
                    <?php if ($faculty['avg_rating']): ?>
                        <div class="faculty-rating">
                            <div class="stars">
                                <?php
                                $rating = round($faculty['avg_rating']);
                                for ($i = 1; $i <= 5; $i++) {
                                    echo $i <= $rating ? '<i class="fas fa-star"></i>' : '<i class="far fa-star"></i>';
                                }
                                ?>
                            </div>
                            <span class="rating-text">
                                <?php echo number_format($faculty['avg_rating'], 1); ?> 
                                (<?php echo $faculty['total_reviews']; ?> reviews)
                            </span>
                        </div>
                    <?php else: ?>
                        <div class="faculty-rating">
                            <div class="stars">
                                <i class="far fa-star"></i>
                                <i class="far fa-star"></i>
                                <i class="far fa-star"></i>
                                <i class="far fa-star"></i>
                                <i class="far fa-star"></i>
                            </div>
                            <span class="rating-text">No ratings yet</span>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="faculty-actions">
                    <a href="add-review.php?faculty_id=<?php echo $faculty['id']; ?>" class="btn btn-primary">Add Review</a>
                </div>
            </div>
            
            <?php if ($faculty['bio']): ?>
                <div class="faculty-bio">
                    <h2>About</h2>
                    <p><?php echo htmlspecialchars($faculty['bio']); ?></p>
                </div>
            <?php endif; ?>
            
            <div class="faculty-reviews">
                <h2>Reviews</h2>
                
                <?php if (mysqli_num_rows($reviews_query) > 0): ?>
                    <div class="reviews-list">
                        <?php while ($review = mysqli_fetch_assoc($reviews_query)): ?>
                            <div class="review-item">
                                <div class="review-header">
                                    <h4><?php echo htmlspecialchars($review['student_name']); ?></h4>
                                    <span class="course"><?php echo htmlspecialchars($review['course_name']); ?></span>
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
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <div class="no-reviews">
                        <i class="fas fa-comment-slash"></i>
                        <p>No reviews yet for this faculty member.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
