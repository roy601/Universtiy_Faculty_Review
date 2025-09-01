<?php
session_start();
include "../DBconnect.php";

if (!isset($_SESSION['username']) || !isset($_SESSION['id'])) {
    header("Location: index.php");
    exit();
}

// Get sorting parameter
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'name';

// Build query based on sort parameter
$order_by = "f.name ASC";
if ($sort == 'rating') {
    $order_by = "avg_rating DESC";
} elseif ($sort == 'reviews') {
    $order_by = "total_reviews DESC";
}

$query = "SELECT f.*, d.name as department_name, 
          AVG(r.rating) as avg_rating, COUNT(r.id) as total_reviews
          FROM faculty f 
          LEFT JOIN departments d ON f.department_id = d.id 
          LEFT JOIN reviews r ON f.id = r.faculty_id 
          GROUP BY f.id 
          ORDER BY $order_by";

$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Browse Faculty - Faculty Hub</title>
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

        .btn-secondary {
            background: rgba(255, 255, 255, 0.1);
            color: white;
        }

        .btn-secondary:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: translateY(-2px);
        }

        .btn-sm {
            padding: 0.4rem 0.8rem;
            font-size: 0.8rem;
        }

        /* Sort options */
        .sort-options {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 2rem;
            padding: 1.5rem;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 16px;
        }

        .sort-options span {
            font-weight: 600;
            color: rgba(255, 255, 255, 0.9);
        }

        /* Faculty list */
        .faculty-list {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 2rem;
        }

        .faculty-item {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 16px;
            padding: 2rem;
            transition: all 0.3s ease;
        }

        .faculty-item:hover {
            background: rgba(255, 255, 255, 0.15);
            transform: translateY(-5px);
        }

        .faculty-info {
            margin-bottom: 1.5rem;
        }

        .faculty-info h3 {
            color: white;
            font-size: 1.5rem;
            margin-bottom: 0.5rem;
        }

        .designation, .department {
            color: rgba(255, 255, 255, 0.8);
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .designation i, .department i {
            color: #667eea;
        }

        .rating {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin: 1rem 0;
        }

        .rating i {
            color: #ffce54;
        }

        .rating span {
            color: rgba(255, 255, 255, 0.8);
            font-weight: 500;
        }

        .reviews-count {
            color: rgba(255, 255, 255, 0.7);
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 1rem;
        }

        .reviews-count i {
            color: #667eea;
        }

        .faculty-actions {
            display: flex;
            gap: 1rem;
        }

        .faculty-actions .btn {
            flex: 1;
            justify-content: center;
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
        
            .faculty-list {
                grid-template-columns: 1fr;
            }
            
            .sort-options {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .faculty-actions {
                flex-direction: column;
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
                <a href="faculty.php" class="nav-btn active">
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
        <div class="section-header">
            <h1 class="section-title">Browse Faculty</h1>
            <p class="section-subtitle">Discover and review faculty members</p>
        </div>
        
        <div class="sort-options">
            <span>Sort by:</span>
            <a href="faculty.php?sort=name" class="btn <?php echo $sort == 'name' ? 'btn-primary' : 'btn-secondary'; ?>">
                <i class="fas fa-sort-alpha-down"></i> Name
            </a>
            <a href="faculty.php?sort=rating" class="btn <?php echo $sort == 'rating' ? 'btn-primary' : 'btn-secondary'; ?>">
                <i class="fas fa-star"></i> Rating
            </a>
            <a href="faculty.php?sort=reviews" class="btn <?php echo $sort == 'reviews' ? 'btn-primary' : 'btn-secondary'; ?>">
                <i class="fas fa-comments"></i> Reviews
            </a>
        </div>
        
        <div class="faculty-list">
            <?php while ($faculty = mysqli_fetch_assoc($result)): ?>
                <div class="faculty-item">
                    <div class="faculty-info">
                        <h3><?php echo htmlspecialchars($faculty['name']); ?></h3>
                        <p class="designation"><i class="fas fa-user-tie"></i> <?php echo htmlspecialchars($faculty['designation']); ?></p>
                        <p class="department"><i class="fas fa-building"></i> <?php echo htmlspecialchars($faculty['department_name']); ?></p>
                        
                        <?php if ($faculty['avg_rating']): ?>
                            <div class="rating">
                                <?php
                                $rating = round($faculty['avg_rating']);
                                for ($i = 1; $i <= 5; $i++) {
                                    echo $i <= $rating ? '<i class="fas fa-star"></i>' : '<i class="far fa-star"></i>';
                                }
                                ?>
                                <span>(<?php echo number_format($faculty['avg_rating'], 1); ?>)</span>
                            </div>
                            <p class="reviews-count"><i class="fas fa-comment"></i> <?php echo $faculty['total_reviews']; ?> reviews</p>
                        <?php else: ?>
                            <div class="rating">
                                <span>No ratings yet</span>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="faculty-actions">
                        <a href="faculty-detail.php?id=<?php echo $faculty['id']; ?>" class="btn btn-primary">
                            <i class="fas fa-user-circle"></i> View Profile
                        </a>
                        <a href="add-review.php?faculty_id=<?php echo $faculty['id']; ?>" class="btn btn-secondary">
                            <i class="fas fa-plus"></i> Add Review
                        </a>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </div>
</body>
</html>