<?php
// navbar.php
// Reusable navigation bar for the whole app.
// Place this file in your project root: /University_Faculty_Review/navbar.php

if (session_status() === PHP_SESSION_NONE) session_start();

// Simple helper to mark active link
$uri = $_SERVER['REQUEST_URI'] ?? '';
function nav_active($needle) {
    global $uri;
    return (strpos($uri, $needle) !== false) ? 'nav-btn active' : 'nav-btn';
}

// If your app folder name is different than "University_Faculty_Review",
// update the $base variable accordingly.
$base = '/University_Faculty_Review';
$username = $_SESSION['username'] ?? '';
?>
<nav class="navbar">
  <div class="nav-container">
    <div class="nav-brand">
      <div class="brand-icon"><i class="fas fa-graduation-cap"></i></div>
      <div class="brand-text">FacultyHub</div>
    </div>

    <div class="nav-menu" role="navigation" aria-label="Main navigation">
      <a href="<?php echo $base; ?>/dashboard_user.php" class="<?php echo nav_active('/dashboard_user.php'); ?>">
        <i class="fas fa-home"></i> Dashboard
      </a>

      <a href="<?php echo $base; ?>/components/faculty.php" class="<?php echo nav_active('/components/faculty.php'); ?>">
        <i class="fas fa-users"></i> Faculty
      </a>

      <a href="<?php echo $base; ?>/components/courses.php" class="<?php echo nav_active('/components/courses.php'); ?>">
        <i class="fas fa-book"></i> Courses
      </a>

      <a href="<?php echo $base; ?>/components/my-reviews.php" class="<?php echo nav_active('/components/my-reviews.php'); ?>">
        <i class="fas fa-star"></i> My Reviews
      </a>

      <a href="<?php echo $base; ?>/components/my-course-reviews.php" class="<?php echo nav_active('/components/my-course-reviews.php'); ?>">
        <i class="fas fa-star-half-alt"></i> My Course Reviews
      </a>

      <a href="<?php echo $base; ?>/logout.php" class="nav-btn">
        <i class="fas fa-sign-out-alt"></i> Logout
      </a>
    </div>
  </div>
</nav>
