<?php
session_start();
include "DBconnect.php";

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
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background-color: #f5f7fa;
            color: #333;
            line-height: 1.6;
            padding: 20px;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .faculty-profile {
            background: white;
            border-radius: 12px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
            overflow: hidden;
        }
        
        .faculty-header {
            display: flex;
            padding: 30px;
            background: linear-gradient(135deg, #3498db 0%, #2c3e50 100%);
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
            margin-right: 30px;
            flex-shrink: 0;
        }
        
        .faculty-avatar i {
            font-size: 60px;
        }
        
        .faculty-info {
            flex: 1;
            min-width: 300px;
        }
        
        .faculty-info h1 {
            font-size: 32px;
            margin-bottom: 8px;
            font-weight: 700;
        }
        
        .designation {
            font-size: 18px;
            margin-bottom: 5px;
            opacity: 0.9;
        }
        
        .department {
            font-size: 16px;
            margin-bottom: 15px;
            opacity: 0.8;
        }
        
        .email {
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .faculty-rating {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-top: 15px;
        }
        
        .stars {
            display: flex;
            gap: 3px;
        }
        
        .stars i {
            color: #f39c12;
            font-size: 20px;
        }
        
        .rating-text {
            font-size: 16px;
            font-weight: 500;
        }
        
        .faculty-actions {
            display: flex;
            align-items: center;
            justify-content: center;
            margin-left: 20px;
        }
        
        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
            text-align: center;
        }
        
        .btn-primary {
            background-color: #f39c12;
            color: white;
        }
        
        .btn-primary:hover {
            background-color: #e67e22;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }
        
        .faculty-bio {
            padding: 25px 30px;
            border-bottom: 1px solid #eee;
        }
        
        .faculty-bio h2 {
            color: #2c3e50;
            margin-bottom: 15px;
            font-size: 22px;
        }
        
        .faculty-bio p {
            line-height: 1.8;
            color: #555;
        }
        
        .faculty-reviews {
            padding: 25px 30px;
        }
        
        .faculty-reviews h2 {
            color: #2c3e50;
            margin-bottom: 20px;
            font-size: 22px;
            padding-bottom: 10px;
            border-bottom: 2px solid #f0f0f0;
        }
        
        .reviews-list {
            display: grid;
            gap: 20px;
        }
        
        .review-item {
            background: #f9f9f9;
            border-radius: 10px;
            padding: 20px;
            border-left: 4px solid #3498db;
        }
        
        .review-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 12px;
            flex-wrap: wrap;
            gap: 10px;
        }
        
        .review-header h4 {
            color: #2c3e50;
            font-size: 18px;
        }
        
        .course {
            background: #3498db;
            color: white;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 14px;
        }
        
        .review-rating {
            margin-bottom: 12px;
        }
        
        .review-rating i {
            color: #f39c12;
            font-size: 16px;
        }
        
        .review-comments {
            margin-bottom: 15px;
        }
        
        .review-comments p {
            color: #555;
            line-height: 1.6;
        }
        
        .review-footer {
            display: flex;
            justify-content: flex-end;
        }
        
        .date {
            color: #7f8c8d;
            font-size: 14px;
        }
        
        .no-reviews {
            text-align: center;
            padding: 40px 20px;
            color: #7f8c8d;
        }
        
        .no-reviews i {
            font-size: 50px;
            margin-bottom: 15px;
            color: #ddd;
        }
        
        .no-reviews p {
            font-size: 18px;
        }
        
        @media (max-width: 900px) {
            .faculty-header {
                flex-direction: column;
                text-align: center;
                align-items: center;
            }
            
            .faculty-avatar {
                margin-right: 0;
                margin-bottom: 20px;
            }
            
            .faculty-actions {
                margin-left: 0;
                margin-top: 20px;
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
                font-size: 50px;
            }
            
            .faculty-info h1 {
                font-size: 26px;
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