<?php
// components/course-detail.php
ini_set('display_errors',1);
error_reporting(E_ALL);

session_start();
include "../DBconnect.php";

// small helper for safe escaping (used throughout)
function e($s){ return htmlspecialchars((string)$s, ENT_QUOTES|ENT_SUBSTITUTE, 'UTF-8'); }

if (!isset($_SESSION['username']) || !isset($_SESSION['id'])) {
    header("Location: ../index.php");
    exit;
}

$course_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($course_id <= 0) {
    header("Location: courses.php");
    exit;
}

// Fetch course + aggregated stats (approved reviews only for public aggregates)
$stmt = $conn->prepare("
  SELECT c.id, c.name, c.code, c.description, d.name AS department_name,
         COALESCE(AVG(cr.rating),0) AS avg_rating,
         COALESCE(SUM(cr.difficulty = 'easy'),0) AS easy_count,
         COALESCE(SUM(cr.difficulty = 'medium'),0) AS medium_count,
         COALESCE(SUM(cr.difficulty = 'hard'),0) AS hard_count,
         COALESCE(COUNT(cr.id),0) AS total_reviews
  FROM courses c
  LEFT JOIN departments d ON c.department_id = d.id
  LEFT JOIN course_reviews cr ON cr.course_id = c.id AND cr.status = 'approved'
  WHERE c.id = ?
  GROUP BY c.id, c.name, c.code, c.description, d.name
  LIMIT 1
");
$stmt->bind_param("i", $course_id);
$stmt->execute();
$res = $stmt->get_result();
if (!$res || $res->num_rows === 0) {
    // not found -> redirect back
    $stmt->close();
    header("Location: courses.php");
    exit;
}
$course = $res->fetch_assoc();
$stmt->close();

// Load approved reviews for this course
$rstmt = $conn->prepare("
  SELECT cr.*, COALESCE(u.name, u.username) AS student_name
  FROM course_reviews cr
  LEFT JOIN users u ON cr.student_id = u.id
  WHERE cr.course_id = ? AND cr.status = 'approved'
  ORDER BY cr.created_at DESC
");
$rstmt->bind_param("i", $course_id);
$rstmt->execute();
$reviews = $rstmt->get_result();
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title><?php echo e($course['name']); ?> — Course</title>

<link rel="stylesheet" href="../dashboardstyle.css">
<link rel="stylesheet" href="../style.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<style>
/* small inline adjustments to keep the page tidy and match theme */
.container{ max-width:1000px; margin:28px auto; padding:0 18px; }
.header{ display:flex; justify-content:space-between; align-items:center; margin-bottom:18px; gap:12px; flex-wrap:wrap; }
.card{ background:rgba(255,255,255,0.03); padding:16px; border-radius:12px; color:#fff; border:1px solid rgba(255,255,255,0.02); box-shadow: 0 8px 18px rgba(0,0,0,0.05); }
.stat-row{ display:flex; gap:12px; margin-top:10px; flex-wrap:wrap; }
.stat{ background:rgba(255,255,255,0.02); padding:8px 10px; border-radius:8px; color:rgba(255,255,255,0.9); }
.reviews{ margin-top:16px; }
.review{ background:rgba(255,255,255,0.02); padding:12px; border-radius:10px; margin-bottom:12px; color: #fff; border:1px solid rgba(255,255,255,0.01); }
.small-muted{ color:rgba(255,255,255,0.85); }
.course-title { font-size:1.6rem; margin:0; color:#fff; }
.course-code { color: rgba(255,255,255,0.85); font-weight:600; margin-left:8px; font-size:0.95rem; }
.btn { display:inline-flex; align-items:center; gap:8px; padding:10px 14px; border-radius:10px; text-decoration:none; font-weight:700; cursor:pointer; border:none; }
.btn-primary { background: linear-gradient(45deg,#667eea,#764ba2); color: #fff; }
.btn-secondary { background: rgba(255,255,255,0.04); color: #fff; }
@media (max-width:640px){ .header{ flex-direction:column; align-items:flex-start; } }
</style>
</head>
<body>
  <div class="animated-bg"><div class="gradient-orb orb-1"></div><div class="gradient-orb orb-2"></div><div class="gradient-orb orb-3"></div></div>

<?php include '../navbar.php'; ?>


  <div class="container">
    <div class="header">
      <div>
        <h1 class="course-title"><?php echo e($course['name']); ?> <span class="course-code">(<?php echo e($course['code']); ?>)</span></h1>
        <div class="small-muted"><?php echo e($course['department_name']); ?></div>
      </div>

      <div style="display:flex;gap:8px;align-items:center;">
        <a class="btn btn-secondary" href="courses.php"><i class="fas fa-arrow-left"></i> Back</a>
        <a class="btn btn-primary" href="add-course-review.php?course_id=<?php echo intval($course_id); ?>"><i class="fas fa-plus"></i> Add Review</a>
      </div>
    </div>

    <div class="card">
      <?php if (!empty($course['description'])): ?>
        <p style="margin:0 0 10px; color: rgba(255,255,255,0.9);"><?php echo nl2br(e($course['description'])); ?></p>
      <?php endif; ?>

      <div class="stat-row">
        <div class="stat"><strong>Avg rating</strong><div><?php echo number_format($course['avg_rating'],1); ?></div></div>
        <div class="stat"><strong>Easy</strong><div><?php echo intval($course['easy_count']); ?></div></div>
        <div class="stat"><strong>Medium</strong><div><?php echo intval($course['medium_count']); ?></div></div>
        <div class="stat"><strong>Hard</strong><div><?php echo intval($course['hard_count']); ?></div></div>
        <div class="stat"><strong>Total reviews</strong><div><?php echo intval($course['total_reviews']); ?></div></div>
      </div>
    </div>

    <div class="reviews">
      <h3 style="color:#fff; margin-top:18px;">Reviews</h3>

      <?php if ($reviews && $reviews->num_rows > 0): ?>
        <?php while ($r = $reviews->fetch_assoc()): ?>
          <div class="review">
            <div style="display:flex;justify-content:space-between;align-items:center;gap:12px;">
              <div>
                <strong><?php echo e($r['student_name']); ?></strong>
                <div class="small-muted"><?php echo e(ucfirst($r['difficulty'])); ?> &bull; <?php echo $r['rating'] ? intval($r['rating']).'/5' : ''; ?></div>
              </div>
              <div class="small-muted"><?php echo date('M j, Y', strtotime($r['created_at'])); ?></div>
            </div>

            <div style="margin-top:10px; color: rgba(255,255,255,0.9);">
              <?php echo nl2br(e($r['comments'])); ?>
            </div>

            <?php if (isset($_SESSION['id']) && intval($_SESSION['id']) === intval($r['student_id'])): ?>
              <div style="margin-top:10px; display:flex; gap:8px;">
                <a href="edit-course-review.php?id=<?php echo intval($r['id']); ?>" class="btn btn-secondary"><i class="fas fa-edit"></i> Edit</a>
                <a href="delete-course-review.php?id=<?php echo intval($r['id']); ?>" class="btn btn-secondary" onclick="return confirm('Delete this review? This cannot be undone.');"><i class="fas fa-trash"></i> Delete</a>
              </div>
            <?php endif; ?>
          </div>
        <?php endwhile; ?>
      <?php else: ?>
        <div class="card" style="margin-top:8px; padding:18px;">
          <div style="color:rgba(255,255,255,0.85)">No reviews yet for this course. Be the first to review it!</div>
        </div>
      <?php endif; ?>

    </div>
  </div>

<script>
  // nothing fancy required here
</script>
</body>
</html>
<?php
// cleanup
if (isset($rstmt) && $rstmt) $rstmt->close();
$conn->close();
?>