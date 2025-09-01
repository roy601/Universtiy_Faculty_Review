<?php
// components/my-course-reviews.php
ini_set('display_errors',1); error_reporting(E_ALL);
session_start();
include "../DBconnect.php";
if (!isset($_SESSION['username']) || !isset($_SESSION['id'])) { header("Location: ../index.php"); exit; }

$user_id = intval($_SESSION['id']);
$stmt = $conn->prepare("SELECT cr.*, c.name AS course_name, c.code AS course_code FROM course_reviews cr LEFT JOIN courses c ON cr.course_id = c.id WHERE cr.student_id = ? ORDER BY cr.created_at DESC");
$stmt->bind_param("i",$user_id); $stmt->execute(); $res = $stmt->get_result();
?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>My Course Reviews</title>
<link rel="stylesheet" href="../dashboardstyle.css"><link rel="stylesheet" href="../style.css"><link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>.container{max-width:1000px;margin:28px auto;padding:0 18px}.review-item{background:rgba(255,255,255,0.03);padding:12px;border-radius:10px;margin-bottom:12px;color:#fff}.meta{display:flex;justify-content:space-between;align-items:center}</style>
</head><body>
<div class="animated-bg"><div class="gradient-orb orb-1"></div><div class="gradient-orb orb-2"></div><div class="gradient-orb orb-3"></div></div>
<nav class="navbar"><div class="nav-container"><div class="nav-brand"><div class="brand-icon"><i class="fas fa-graduation-cap"></i></div><div class="brand-text">FacultyHub</div></div><div class="nav-menu"><a href="../dashboard_user.php" class="nav-btn"><i class="fas fa-home"></i> Dashboard</a><a href="courses.php" class="nav-btn"><i class="fas fa-book"></i> Courses</a><a href="my-course-reviews.php" class="nav-btn active"><i class="fas fa-star"></i> My Course Reviews</a><a href="../logout.php" class="nav-btn"><i class="fas fa-sign-out-alt"></i> Logout</a></div></div></nav>

<div class="container">
  <h1 style="color:#fff">My Course Reviews</h1>
  <?php if ($res && $res->num_rows > 0): while($r = $res->fetch_assoc()): ?>
    <div class="review-item">
      <div class="meta">
        <div>
          <strong><?php echo htmlspecialchars($r['course_name'] . ' (' . $r['course_code'] . ')'); ?></strong>
          <div class="small-muted"><?php echo htmlspecialchars($r['difficulty']); ?> • <?php echo $r['rating'] ? intval($r['rating']).'/5' : ''; ?></div>
        </div>
        <div>
          <a href="edit-course-review.php?id=<?php echo intval($r['id']); ?>" class="btn btn-sm btn-primary">Edit</a>
          <a href="delete-course-review.php?id=<?php echo intval($r['id']); ?>" class="btn btn-sm btn-danger delete-review">Delete</a>
        </div>
      </div>
      <div style="margin-top:8px"><?php echo nl2br(htmlspecialchars($r['comments'])); ?></div>
      <div class="small-muted" style="margin-top:8px"><?php echo date('M j, Y', strtotime($r['created_at'])); ?> — <?php echo htmlspecialchars($r['status']); ?></div>
    </div>
  <?php endwhile; else: ?>
    <div style="padding:16px;border-radius:10px;background:rgba(255,255,255,0.02);color:rgba(255,255,255,0.9)">You have not submitted any course reviews yet.</div>
  <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded',function(){
  document.querySelectorAll('.delete-review').forEach(function(el){
    el.addEventListener('click',function(e){
      if(!confirm('Delete this course review?')) e.preventDefault();
    });
  });
});
</script>
<?php $stmt->close(); $conn->close(); ?></body></html>