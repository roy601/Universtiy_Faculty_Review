<?php
// components/courses.php
ini_set('display_errors',1); error_reporting(E_ALL);
session_start();
include "../DBconnect.php";

if (!isset($_SESSION['username']) || !isset($_SESSION['id'])) {
  header("Location: ../index.php"); exit;
}

$q_raw = isset($_GET['q']) ? trim($_GET['q']) : '';
$q = $q_raw;

try {
  if ($q !== '') {
    $sql = "SELECT c.id, c.name, c.code, d.name AS department_name,
                   COALESCE(AVG(cr.rating),0) AS avg_rating,
                   COALESCE(SUM(cr.difficulty = 'easy'),0) AS easy_count,
                   COALESCE(SUM(cr.difficulty = 'medium'),0) AS medium_count,
                   COALESCE(SUM(cr.difficulty = 'hard'),0) AS hard_count,
                   COALESCE(COUNT(cr.id),0) AS total_reviews
            FROM courses c
            LEFT JOIN departments d ON c.department_id = d.id
            LEFT JOIN course_reviews cr ON cr.course_id = c.id
            WHERE c.name LIKE ? OR c.code LIKE ? OR d.name LIKE ?
            GROUP BY c.id, c.name, c.code, d.name
            ORDER BY c.name ASC";
    $stmt = $conn->prepare($sql);
    $like = "%$q%";
    $stmt->bind_param("sss", $like, $like, $like);
    $stmt->execute();
    $result = $stmt->get_result();
  } else {
    $sql = "SELECT c.id, c.name, c.code, d.name AS department_name,
                   COALESCE(AVG(cr.rating),0) AS avg_rating,
                   COALESCE(SUM(cr.difficulty = 'easy'),0) AS easy_count,
                   COALESCE(SUM(cr.difficulty = 'medium'),0) AS medium_count,
                   COALESCE(SUM(cr.difficulty = 'hard'),0) AS hard_count,
                   COALESCE(COUNT(cr.id),0) AS total_reviews
            FROM courses c
            LEFT JOIN departments d ON c.department_id = d.id
            LEFT JOIN course_reviews cr ON cr.course_id = c.id
            GROUP BY c.id, c.name, c.code, d.name
            ORDER BY c.name ASC";
    $result = $conn->query($sql);
  }
} catch (Exception $ex) {
  $result = false;
}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Browse Courses</title>
<link rel="stylesheet" href="../dashboardstyle.css">
<link rel="stylesheet" href="../style.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
.container{max-width:1100px;margin:28px auto;padding:0 18px;}
.search-card{width:100%;background:rgba(255,255,255,0.03);border-radius:14px;padding:14px;display:flex;gap:12px;align-items:center;margin-bottom:18px;}
.search-input{flex:1;padding:10px 12px;border-radius:10px;border:1px solid rgba(255,255,255,0.06);background:rgba(255,255,255,0.02);color:#fff;}
.course-list{display:grid;grid-template-columns:1fr;gap:12px}
.course-item{display:flex;justify-content:space-between;align-items:center;padding:14px;border-radius:10px;background:rgba(255,255,255,0.02)}
.course-meta h3{margin:0;color:#fff}
.small-muted{color:rgba(255,255,255,0.8)}
.badge{padding:6px 10px;border-radius:10px;background:rgba(255,255,255,0.04);color:rgba(255,255,255,0.9)}
.difficulty {display:flex;gap:8px;align-items:center}
.difficulty span{padding:6px 10px;border-radius:8px;background:rgba(255,255,255,0.03);color:#fff;font-weight:600}
</style>
</head>
<body>
<div class="animated-bg"><div class="gradient-orb orb-1"></div><div class="gradient-orb orb-2"></div><div class="gradient-orb orb-3"></div></div>
<?php include '../navbar.php'; ?>


<div class="container">
  <div style="margin-bottom:12px;">
    <h1 class="section-title">Browse Courses</h1>
    <p class="section-subtitle">Search and review courses</p>
  </div>

  <div class="search-card">
    <form method="get" action="courses.php" style="display:flex;flex:1;gap:8px;align-items:center;">
      <input type="text" name="q" class="search-input" placeholder="Search by course name, code or department" value="<?php echo htmlspecialchars($q_raw); ?>">
      <button class="btn btn-primary" type="submit"><i class="fas fa-search"></i> Search</button>
      <a href="courses.php" class="btn btn-secondary" style="margin-left:8px;">Reset</a>
    </form>
  </div>

  <div class="course-list">
    <?php if ($result && $result->num_rows > 0): ?>
      <?php while ($c = $result->fetch_assoc()): ?>
        <div class="course-item">
          <div class="course-meta">
            <h3><?php echo htmlspecialchars($c['name']); ?> <small class="small-muted"> (<?php echo htmlspecialchars($c['code']); ?>)</small></h3>
            <div class="small-muted"><?php echo htmlspecialchars($c['department_name']); ?></div>
            <div style="margin-top:8px;">
              <span class="badge">Avg rating: <?php echo number_format($c['avg_rating'],1); ?></span>
              <span class="badge" style="margin-left:8px;"><?php echo intval($c['total_reviews']); ?> reviews</span>
            </div>
          </div>
          <div style="display:flex;align-items:center;gap:10px;">
            <a href="course-detail.php?id=<?php echo intval($c['id']); ?>" class="btn btn-primary"><i class="fas fa-eye"></i> View</a>
            <a href="add-course-review.php?course_id=<?php echo intval($c['id']); ?>" class="btn btn-secondary"><i class="fas fa-plus"></i> Review</a>
          </div>
        </div>
      <?php endwhile; ?>
    <?php else: ?>
      <div style="padding:20px;border-radius:10px;background:rgba(255,255,255,0.03);color:rgba(255,255,255,0.9);">No courses found.</div>
    <?php endif; ?>
  </div>
</div>

<?php if (isset($stmt) && $stmt) $stmt->close(); $conn->close(); ?>
</body>
</html>