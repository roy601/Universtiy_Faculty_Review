<?php
session_start();
include "DBconnect.php";

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
        :root {
            --bg-primary: #0a0e1a;
            --bg-secondary: #1a1f2e;
            --bg-tertiary: #252b3d;
          
            --gradient-primary: linear-gradient(135deg, #1e3a8a 0%, #3730a3 50%, #1e40af 100%);
            --gradient-secondary: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #334155 100%);
            --gradient-accent: linear-gradient(135deg, #06b6d4 0%, #8b5cf6 50%, #ec4899 100%);
          
            --neon-cyan: #00f5ff;
            --neon-purple: #bf00ff;
            --neon-pink: #ff0080;
            --neon-gold: #ffd700;
          
            --text-primary: #f8fafc;
            --text-secondary: #cbd5e1;
            --text-muted: #64748b;
          
            --glass-bg: rgba(255, 255, 255, 0.05);
            --glass-border: rgba(255, 255, 255, 0.1);
            --glass-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
          
            --glow-cyan: 0 0 20px rgba(0, 245, 255, 0.3);
            --glow-purple: 0 0 20px rgba(191, 0, 255, 0.3);
            --glow-pink: 0 0 20px rgba(255, 0, 128, 0.3);
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: "Inter", -apple-system, BlinkMacSystemFont, sans-serif;
            background: var(--bg-primary);
            color: var(--text-primary);
            line-height: 1.6;
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
            filter: blur(60px);
            opacity: 0.4;
            animation: float 20s infinite ease-in-out;
        }
        
        .orb-1 {
            width: 400px;
            height: 400px;
            background: radial-gradient(circle, var(--neon-cyan) 0%, transparent 70%);
            top: -200px;
            left: -200px;
            animation-delay: 0s;
        }
        
        .orb-2 {
            width: 300px;
            height: 300px;
            background: radial-gradient(circle, var(--neon-purple) 0%, transparent 70%);
            top: 50%;
            right: -150px;
            animation-delay: -7s;
        }
        
        .orb-3 {
            width: 350px;
            height: 350px;
            background: radial-gradient(circle, var(--neon-pink) 0%, transparent 70%);
            bottom: -175px;
            left: 30%;
            animation-delay: -14s;
        }
        
        @keyframes float {
            0%,
            100% {
                transform: translate(0, 0) rotate(0deg);
            }
            33% {
                transform: translate(100px, -100px) rotate(120deg);
            }
            66% {
                transform: translate(-50px, 50px) rotate(240deg);
            }
        }
        
        .navbar {
            background: var(--glass-bg);
            backdrop-filter: blur(20px);
            border-bottom: 1px solid var(--glass-border);
            padding: 1rem 0;
            position: fixed;
            top: 0;
            width: 100%;
            z-index: 1000;
            box-shadow: var(--glass-shadow);
        }
        
        .nav-container {
            max-width: 1400px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 2rem;
        }
        
        .nav-brand {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .brand-icon {
            width: 50px;
            height: 50px;
            background: var(--gradient-accent);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: var(--glow-cyan);
            animation: pulse-glow 3s infinite;
        }
        
        .brand-icon i {
            font-size: 1.5rem;
            color: white;
        }
        
        .brand-text {
            font-size: 1.5rem;
            font-weight: 700;
            background: var(--gradient-accent);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        @keyframes pulse-glow {
            0%,
            100% {
                box-shadow: var(--glow-cyan);
            }
            50% {
                box-shadow: var(--glow-purple);
            }
        }
        
        .nav-menu {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .nav-btn {
            background: var(--glass-bg);
            border: 1px solid var(--glass-border);
            color: var(--text-secondary);
            padding: 0.75rem 1.25rem;
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-weight: 500;
            backdrop-filter: blur(10px);
        }
        
        .nav-btn:hover,
        .nav-btn.active {
            background: var(--gradient-accent);
            color: white;
            transform: translateY(-2px);
            box-shadow: var(--glow-cyan);
        }
        
        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 2rem;
        }
        
        .section-header {
            margin-bottom: 3rem;
        }
        
        .section-title {
            font-size: 3rem;
            font-weight: 700;
            background: var(--gradient-accent);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 0.5rem;
        }
        
        .section-subtitle {
            color: var(--text-secondary);
            font-size: 1.2rem;
            font-weight: 400;
        }
        
        .btn {
            padding: 0.75rem 1.5rem;
            border-radius: 12px;
            cursor: pointer;
            font-weight: 600;
            font-size: 1rem;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            text-decoration: none;
        }
        
        .btn-primary {
            background: var(--gradient-accent);
            color: white;
            border: none;
            box-shadow: var(--glow-cyan);
            position: relative;
            overflow: hidden;
        }
        
        .btn-primary::before {
            content: "";
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s;
        }
        
        .btn-primary:hover::before {
            left: 100%;
        }
        
        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: var(--glow-purple);
        }
        
        .btn-secondary {
            background: var(--glass-bg);
            color: var(--text-primary);
            border: 1px solid var(--glass-border);
            backdrop-filter: blur(10px);
        }
        
        .btn-secondary:hover {
            background: var(--bg-tertiary);
            transform: translateY(-2px);
        }
        
        .sort-options {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 2rem;
            padding: 1.5rem;
            background: var(--glass-bg);
            backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border);
            border-radius: 16px;
            box-shadow: var(--glass-shadow);
        }
        
        .sort-options span {
            font-weight: 600;
            color: var(--text-secondary);
        }
        
        .faculty-list {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(400px, 1fr));
            gap: 2rem;
        }
        
        .faculty-item {
            background: var(--glass-bg);
            backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border);
            border-radius: 20px;
            box-shadow: var(--glass-shadow);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            overflow: hidden;
            position: relative;
        }
        
        .faculty-item:hover {
            transform: translateY(-10px) scale(1.02);
            box-shadow: var(--glow-cyan);
            border-color: var(--neon-cyan);
        }
        
        .faculty-info {
            padding: 2rem;
        }
        
        .faculty-info h3 {
            font-size: 1.5rem;
            margin-bottom: 0.5rem;
            color: var(--text-primary);
        }
        
        .designation, .department {
            color: var(--text-secondary);
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .designation i, .department i {
            color: var(--neon-cyan);
        }
        
        .rating {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin: 1rem 0;
        }
        
        .rating i {
            color: var(--neon-gold);
        }
        
        .rating span {
            color: var(--text-secondary);
            font-weight: 500;
        }
        
        .faculty-actions {
            display: flex;
            gap: 1rem;
            padding: 0 2rem 2rem;
        }
        
        .faculty-actions .btn {
            flex: 1;
            justify-content: center;
        }
        
        @media (max-width: 768px) {
            body {
                padding-top: 120px;
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
                font-size: 2rem;
            }
        
            .faculty-list {
                grid-template-columns: 1fr;
            }
            
            .sort-options {
                flex-direction: column;
                align-items: flex-start;
            }
        }
        
        /* Scrollbar styling */
        ::-webkit-scrollbar {
            width: 8px;
        }
        
        ::-webkit-scrollbar-track {
            background: var(--bg-secondary);
        }
        
        ::-webkit-scrollbar-thumb {
            background: var(--gradient-accent);
            border-radius: 4px;
        }
        
        ::-webkit-scrollbar-thumb:hover {
            background: var(--neon-cyan);
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
                <div class="brand-text">Faculty Hub</div>
            </div>
            <div class="nav-menu">
                <a href="dashboard.php" class="nav-btn"><i class="fas fa-home"></i> <span>Dashboard</span></a>
                <a href="faculty.php" class="nav-btn active"><i class="fas fa-chalkboard-teacher"></i> <span>Faculty</span></a>
                <a href="reviews.php" class="nav-btn"><i class="fas fa-star"></i> <span>Reviews</span></a>
                <a href="profile.php" class="nav-btn"><i class="fas fa-user"></i> <span>Profile</span></a>
                <a href="logout.php" class="nav-btn"><i class="fas fa-sign-out-alt"></i> <span>Logout</span></a>
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