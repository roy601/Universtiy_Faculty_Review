<?php
session_start();
include "DBconnect.php";

if (!isset($_SESSION['username']) || !isset($_SESSION['id'])) {
    header("Location: index.php");
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
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 12px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
            padding: 30px;
        }
        
        h1 {
            color: #2c3e50;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid #eee;
            font-weight: 600;
        }
        
        .alert {
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-weight: 500;
        }
        
        .alert-danger {
            background-color: #ffebee;
            color: #c62828;
            border: 1px solid #ef9a9a;
        }
        
        .review-form {
            margin-top: 20px;
        }
        
        .form-group {
            margin-bottom: 25px;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #2c3e50;
        }
        
        select, textarea {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 16px;
            transition: border-color 0.3s;
        }
        
        select:focus, textarea:focus {
            outline: none;
            border-color: #3498db;
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.2);
        }
        
        textarea {
            resize: vertical;
            min-height: 120px;
        }
        
        .rating-input {
            display: flex;
            flex-direction: row-reverse;
            justify-content: flex-end;
            gap: 5px;
        }
        
        .rating-input input {
            display: none;
        }
        
        .rating-input label {
            cursor: pointer;
            font-size: 28px;
            color: #ddd;
            transition: color 0.2s;
        }
        
        .rating-input input:checked ~ label,
        .rating-input label:hover,
        .rating-input label:hover ~ label {
            color: #f39c12;
        }
        
        .rating-input input:checked + label {
            color: #f39c12;
        }
        
        .form-actions {
            display: flex;
            gap: 15px;
            margin-top: 30px;
        }
        
        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .btn-primary {
            background-color: #3498db;
            color: white;
        }
        
        .btn-primary:hover {
            background-color: #2980b9;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        
        .btn-secondary {
            background-color: #95a5a6;
            color: white;
        }
        
        .btn-secondary:hover {
            background-color: #7f8c8d;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        
        @media (max-width: 600px) {
            .container {
                padding: 20px;
            }
            
            .form-actions {
                flex-direction: column;
            }
            
            .btn {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Add Review for <?php echo htmlspecialchars($faculty['name']); ?></h1>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <form method="POST" class="review-form">
            <div class="form-group">
                <label for="course_id">Course:</label>
                <select name="course_id" id="course_id" required>
                    <option value="">Select a course</option>
                    <?php while ($course = mysqli_fetch_assoc($courses_query)): ?>
                        <option value="<?php echo $course['id']; ?>"><?php echo htmlspecialchars($course['name']); ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="rating">Rating:</label>
                <div class="rating-input">
                    <?php for ($i = 5; $i >= 1; $i--): ?>
                        <input type="radio" name="rating" id="rating<?php echo $i; ?>" value="<?php echo $i; ?>" required>
                        <label for="rating<?php echo $i; ?>"><i class="fas fa-star"></i></label>
                    <?php endfor; ?>
                </div>
            </div>
            
            <div class="form-group">
                <label for="comments">Comments:</label>
                <textarea name="comments" id="comments" rows="5" placeholder="Share your experience with this faculty member..." required></textarea>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Submit Review</button>
                <a href="faculty.php" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
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
                        star.style.color = '#f39c12';
                    } else {
                        star.style.color = '#ddd';
                    }
                });
            }
            
            function resetStars() {
                const stars = document.querySelectorAll('.rating-input label');
                stars.forEach(star => {
                    star.style.color = '#ddd';
                });
            }
        });
    </script>
</body>
</html>