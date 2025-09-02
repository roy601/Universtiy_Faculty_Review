<?php
// components/faculty.php
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();
include "../DBconnect.php";

// protect route
if (!isset($_SESSION['username']) || !isset($_SESSION['id'])) {
    header("Location: ../index.php");
    exit();
}

// sanitize & defaults
$allowed_sorts = ['name','rating','reviews'];
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'name';
if (!in_array($sort, $allowed_sorts)) $sort = 'name';

$q_raw = isset($_GET['q']) ? trim($_GET['q']) : '';
$q = $q_raw; // used with prepared statements

// determine order by
$order_by = "f.name ASC";
if ($sort === 'rating') $order_by = "avg_rating DESC";
if ($sort === 'reviews') $order_by = "total_reviews DESC";

// build and execute faculty query safely
$faculty_result = false;
$stmt = null;
try {
    if ($q !== '') {
        $sql = "
          SELECT f.id, f.name, f.designation, d.name AS department_name,
                 COALESCE(AVG(r.rating),0) AS avg_rating,
                 COALESCE(COUNT(DISTINCT r.id),0) AS total_reviews
          FROM faculty f
          LEFT JOIN departments d ON f.department_id = d.id
          LEFT JOIN reviews r ON f.id = r.faculty_id
          WHERE (f.name LIKE ? OR d.name LIKE ?)
          GROUP BY f.id, f.name, f.designation, d.name
          ORDER BY {$order_by}
        ";
        $stmt = $conn->prepare($sql);
        $like = '%' . $q . '%';
        $stmt->bind_param('ss', $like, $like);
        $stmt->execute();
        $faculty_result = $stmt->get_result();
    } else {
        $sql = "
          SELECT f.id, f.name, f.designation, d.name AS department_name,
                 COALESCE(AVG(r.rating),0) AS avg_rating,
                 COALESCE(COUNT(DISTINCT r.id),0) AS total_reviews
          FROM faculty f
          LEFT JOIN departments d ON f.department_id = d.id
          LEFT JOIN reviews r ON f.id = r.faculty_id
          GROUP BY f.id, f.name, f.designation, d.name
          ORDER BY {$order_by}
        ";
        $faculty_result = $conn->query($sql);
    }
} catch (Exception $ex) {
    // on error, $faculty_result remains false
    $faculty_result = false;
}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Browse Faculty</title>
<link rel="stylesheet" href="../dashboardstyle.css">
<link rel="stylesheet" href="../style.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<style>
/* small inline overrides */
.search-card {
  width: 100%;
  background: rgba(255,255,255,0.03);
  border-radius: 14px;
  padding: 18px;
  display: flex;
  align-items: center;
  gap: 12px;
  box-shadow: 0 6px 24px rgba(0,0,0,0.06);
  border: 1px solid rgba(255,255,255,0.04);
  margin-bottom: 18px;
}
.search-card form { display:flex; gap:12px; flex:1; align-items:center; }
.search-card .search-input { flex:1; min-width:160px; padding:12px 14px; border-radius:10px; border:1px solid rgba(255,255,255,0.12); background: rgba(255,255,255,0.02); color:#fff; }
.search-card .small-select { padding:10px 12px; border-radius:10px; border:1px solid rgba(255,255,255,0.06); background: rgba(255,255,255,0.02); color:#fff; }
.search-card .search-button { padding:10px 12px; border-radius:10px; border:none; background: linear-gradient(45deg,#667eea,#764ba2); color:#fff; font-weight:600; display:inline-flex; align-items:center; gap:8px; cursor:pointer; }
@media (max-width:720px){ .search-card { flex-direction:column; align-items:stretch; } .search-card .search-button{ width:100%; } }

/* Faculty item visual */
.faculty-list { display:flex; flex-direction:column; gap:1rem; margin-top:1rem; }
.faculty-item { display:flex; justify-content:space-between; align-items:flex-start; padding:18px; border-radius:12px; background:rgba(255,255,255,0.02); }
.faculty-info h3{ color:#fff; margin:0 0 6px 0; }
.faculty-info p{ color:rgba(255,255,255,0.8); margin:4px 0; }
.faculty-actions a{ margin-left:.5rem; }
.rating i{ color:#ffd166; margin-right:3px; }
</style>
</head>
<body>
  <div class="animated-bg">
    <div class="gradient-orb orb-1"></div>
    <div class="gradient-orb orb-2"></div>
    <div class="gradient-orb orb-3"></div>
  </div>

<?php include '../navbar.php'; ?>


  <div class="container">
    <div class="section-header">
      <h1 class="section-title">Browse Faculty</h1>
      <p class="section-subtitle">Discover and review faculty members</p>
    </div>

    <div class="search-card" role="search" aria-label="Search faculty">
      <form method="GET" action="faculty.php" aria-label="Faculty search form">
        <input type="text" name="q" value="<?php echo htmlspecialchars($q_raw); ?>" placeholder="Search by faculty or department..." class="search-input" aria-label="Search query">
        <select name="sort" class="small-select" aria-label="Sort">
          <option value="name" <?php echo ($sort === 'name') ? 'selected' : ''; ?>>Name</option>
          <option value="rating" <?php echo ($sort === 'rating') ? 'selected' : ''; ?>>Rating</option>
          <option value="reviews" <?php echo ($sort === 'reviews') ? 'selected' : ''; ?>>Reviews</option>
        </select>
        <button type="submit" class="search-button"><i class="fas fa-search"></i> Search</button>
        <a href="faculty.php" class="btn btn-secondary" style="margin-left:8px;padding:10px 12px;border-radius:10px;">Reset</a>
      </form>
    </div>

    <div class="sort-options" style="margin-top:0;">
      <span style="color:rgba(255,255,255,0.9); margin-right:12px;">Sort by:</span>
      <a href="faculty.php?sort=name" class="btn <?php echo $sort == 'name' ? 'btn-primary' : 'btn-secondary'; ?>"><i class="fas fa-sort-alpha-down"></i> Name</a>
      <a href="faculty.php?sort=rating" class="btn <?php echo $sort == 'rating' ? 'btn-primary' : 'btn-secondary'; ?>"><i class="fas fa-star"></i> Rating</a>
      <a href="faculty.php?sort=reviews" class="btn <?php echo $sort == 'reviews' ? 'btn-primary' : 'btn-secondary'; ?>"><i class="fas fa-comments"></i> Reviews</a>
    </div>

    <div class="faculty-list">
      <?php if ($faculty_result && $faculty_result->num_rows > 0): ?>
        <?php while ($f = $faculty_result->fetch_assoc()): ?>
          <div class="faculty-item">
            <div class="faculty-info">
              <h3><?php echo htmlspecialchars($f['name']); ?></h3>
              <p class="designation"><i class="fas fa-user-tie"></i> <?php echo htmlspecialchars($f['designation']); ?></p>
              <p class="department"><i class="fas fa-building"></i> <?php echo htmlspecialchars($f['department_name']); ?></p>

              <?php if ((int)$f['total_reviews'] > 0): ?>
                <div class="rating" style="margin-top:8px;">
                  <?php $round = round($f['avg_rating']); for ($i=1;$i<=5;$i++) echo $i <= $round ? '<i class="fas fa-star"></i>' : '<i class="far fa-star"></i>'; ?>
                  <span style="color:rgba(255,255,255,0.85); margin-left:8px;">(<?php echo number_format($f['avg_rating'],1); ?>)</span>
                </div>
                <p style="margin-top:6px;"><i class="fas fa-comment"></i> <?php echo intval($f['total_reviews']); ?> reviews</p>
              <?php else: ?>
                <p style="margin-top:8px; color:rgba(255,255,255,0.8)"><i class="far fa-star"></i> No ratings yet</p>
              <?php endif; ?>
            </div>

            <div class="faculty-actions" style="display:flex; gap:8px;">
              <a href="faculty-detail.php?id=<?php echo urlencode($f['id']); ?>" class="btn btn-primary"><i class="fas fa-user-circle"></i> View Profile</a>
              <a href="add-review.php?faculty_id=<?php echo urlencode($f['id']); ?>" class="btn btn-secondary"><i class="fas fa-plus"></i> Add Review</a>
              <a href="faculty-detail.php?id=<?php echo intval($f['id']); ?>#reviews" class="btn btn-secondary"><i class="fas fa-comments"></i> View Reviews</a>
            </div>
          </div>
        <?php endwhile; ?>
      <?php else: ?>
        <div style="padding:18px; border-radius:12px; background:rgba(255,255,255,0.03); color:rgba(255,255,255,0.9);">
          No faculty found. Try a different search term or reset filters.
        </div>
      <?php endif; ?>
    </div>

  </div>

<script>
/* no inline review actions here */
</script>
</body>
</html>
<?php
// cleanup
if (isset($stmt) && $stmt) $stmt->close();
$conn->close();
?>
