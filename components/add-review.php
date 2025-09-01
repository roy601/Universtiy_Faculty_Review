<?php
session_start();
include "../DBconnect.php";

if (!isset($_SESSION['username']) || !isset($_SESSION['id'])) {
    header("Location: ../index.php");
    exit();
}

// Check if faculty_id is provided
if (!isset($_GET['faculty_id'])) {
    header("Location: faculty.php");
    exit();
}

$faculty_id = $_GET['faculty_id'];
$user_id = $_SESSION['id'];

// Get faculty details
$faculty_query = mysqli_query($conn, "SELECT * FROM faculty WHERE id = '$faculty_id'");
$faculty = mysqli_fetch_assoc($faculty_query);

// Get courses
$courses_query = mysqli_query($conn, "SELECT * FROM courses");

// Process form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $course_id = $_POST['course_id'];
    $rating = $_POST['rating'];
    $comments = $_POST['comments'];
    
    $insert_query = "INSERT INTO reviews (student_id, faculty_id, course_id, rating, comments, status) 
                     VALUES ('$user_id', '$faculty_id', '$course_id', '$rating', '$comments', 'pending')";
    
    if (mysqli_query($conn, $insert_query)) {
        header("Location: my-reviews.php?message=review_added");
        exit();
    } else {
        $error = "Error submitting review: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Review - Faculty Hub</title>
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
            color: #fff;
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

        /* Review container */
        .review-container {
            max-width: 800px;
            margin: 2rem auto;
            padding: 0 1.5rem;
        }

        .review-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 16px;
            padding: 2.5rem;
        }

        .review-header {
            margin-bottom: 2rem;
            text-align: center;
        }

        .review-header h1 {
            color: white;
            font-size: 2rem;
            margin-bottom: 0.5rem;
            font-weight: 600;
        }

        .review-header p {
            color: rgba(255, 255, 255, 0.8);
            font-size: 1.1rem;
        }

        .alert {
            padding: 1rem 1.5rem;
            border-radius: 12px;
            margin-bottom: 1.5rem;
            font-weight: 500;
        }

        .alert-danger {
            background: rgba(255, 71, 87, 0.2);
            color: #ff4757;
            border: 1px solid rgba(255, 71, 87, 0.3);
        }

        .review-form {
            margin-top: 1.5rem;
        }

        .form-group {
            margin-bottom: 1.8rem;
        }

        label {
            display: block;
            margin-bottom: 0.8rem;
            font-weight: 500;
            color: white;
            font-size: 1.1rem;
        }

        select, textarea {
            width: 100%;
            padding: 1rem 1.2rem;
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 12px;
            font-size: 1rem;
            color: white;
            transition: all 0.3s ease;
        }

        select:focus, textarea:focus {
            outline: none;
            border-color: rgba(102, 126, 234, 0.6);
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.2);
            background: rgba(255, 255, 255, 0.15);
        }

        textarea {
            resize: vertical;
            min-height: 140px;
        }

        select option {
            background: rgba(50, 50, 70, 0.9);
            color: white;
        }

        .rating-input {
            display: flex;
            flex-direction: row-reverse;
            justify-content: flex-end;
            gap: 0.5rem;
            margin-top: 0.5rem;
        }

        .rating-input input {
            display: none;
        }

        .rating-input label {
            cursor: pointer;
            font-size: 2.2rem;
            color: rgba(255, 255, 255, 0.3);
            transition: color 0.2s, transform 0.2s;
            margin-bottom: 0;
        }

        .rating-input input:checked ~ label,
        .rating-input label:hover,
        .rating-input label:hover ~ label {
            color: #ffce54;
            transform: scale(1.1);
        }

        .rating-input input:checked + label {
            color: #ffce54;
        }

        .form-actions {
            display: flex;
            gap: 1rem;
            margin-top: 2rem;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 1rem 2rem;
            border: none;
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
        }

        .btn-primary {
            background: linear-gradient(45deg, #667eea, #764ba2);
            color: white;
        }

        .btn-primary:hover {
            background: linear-gradient(45deg, #5a6fd8, #6a4190);
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.2);
        }

        .btn-secondary {
            background: rgba(255, 255, 255, 0.1);
            color: white;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .btn-secondary:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: translateY(-2px);
        }

        /* Responsive styles */
        @media (max-width: 768px) {
            body {
                padding-top: 100px;
            }
            
            .review-container {
                padding: 0 1rem;
            }
            
            .review-card {
                padding: 1.8rem;
            }
            
            .review-header h1 {
                font-size: 1.7rem;
            }
            
            .form-actions {
                flex-direction: column;
            }
            
            .btn {
                width: 100%;
            }
            
            .rating-input label {
                font-size: 1.8rem;
            }
            
            .nav-container {
                flex-direction: column;
                gap: 1rem;
            }
        }

        @media (max-width: 480px) {
            .review-header h1 {
                font-size: 1.5rem;
            }
        }
    </style>   
</head>
<body>
    <!-- Animated background elements -->
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
                <span class="brand-text">FacultyHub</span>
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
    
    <div class="review-container">
        <div class="review-card">
            <div class="review-header">
                <h1>Add Review for <?php echo htmlspecialchars($faculty['name']); ?></h1>
                <p>Share your experience with this faculty member</p>
            </div>
            
            <?php if (isset($error)): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i> 
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" class="review-form">
                <div class="form-group">
                    <label for="course_id"><i class="fas fa-book"></i> Course:</label>
                    <select name="course_id" id="course_id" required>
                        <option value="">Select a course</option>
                        <?php 
                        // Reset pointer for courses query
                        mysqli_data_seek($courses_query, 0);
                        while ($course = mysqli_fetch_assoc($courses_query)): ?>
                            <option value="<?php echo $course['id']; ?>"><?php echo htmlspecialchars($course['name']); ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label><i class="fas fa-star"></i> Rating:</label>
                    <div class="rating-input">
                        <?php for ($i = 5; $i >= 1; $i--): ?>
                            <input type="radio" name="rating" id="rating<?php echo $i; ?>" value="<?php echo $i; ?>" required>
                            <label for="rating<?php echo $i; ?>"><i class="fas fa-star"></i></label>
                        <?php endfor; ?>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="comments"><i class="fas fa-comment"></i> Comments:</label>
                    <textarea name="comments" id="comments" placeholder="Share your thoughts about this faculty member's teaching style, communication, and overall effectiveness..." required></textarea>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-paper-plane"></i> Submit Review
                    </button>
                    <a href="faculty.php" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Enhance the rating system with interactive feedback
        document.addEventListener('DOMContentLoaded', function() {
            const stars = document.querySelectorAll('.rating-input label');
            
            stars.forEach(star => {
                star.addEventListener('mouseover', function() {
                    const ratingValue = this.getAttribute('for').replace('rating', '');
                    highlightStars(ratingValue);
                });
                
                star.addEventListener('mouseout', function() {
                    const checked = document.querySelector('.rating-input input:checked');
                    if (checked) {
                        highlightStars(checked.value);
                    } else {
                        resetStars();
                    }
                });
            });
            
            function highlightStars(value) {
                const stars = document.querySelectorAll('.rating-input label');
                stars.forEach(star => {
                    const starValue = star.getAttribute('for').replace('rating', '');
                    if (starValue <= value) {
                        star.style.color = '#ffce54';
                    } else {
                        star.style.color = 'rgba(255, 255, 255, 0.3)';
                    }
                });
            }
            
            function resetStars() {
                const stars = document.querySelectorAll('.rating-input label');
                stars.forEach(star => {
                    star.style.color = 'rgba(255, 255, 255, 0.3)';
                });
                
                // Highlight any checked star again
                const checked = document.querySelector('.rating-input input:checked');
                if (checked) {
                    highlightStars(checked.value);
                }
            }
            
            // Initialize with any existing checked value
            const checked = document.querySelector('.rating-input input:checked');
            if (checked) {
                highlightStars(checked.value);
            }
        });
    </script>
</body>
</html>