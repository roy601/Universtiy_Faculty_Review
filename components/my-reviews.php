<?php
// components/my-reviews.php
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();
include "../DBconnect.php";

// Require login
if (!isset($_SESSION['username']) || !isset($_SESSION['id'])) {
    header("Location: ../index.php");
    exit();
}

$user_id = intval($_SESSION['id']);

// read optional messages
$msg = '';
if (isset($_GET['message'])) {
    if ($_GET['message'] === 'deleted') $msg = 'Review deleted successfully.';
    if ($_GET['message'] === 'updated') $msg = 'Review updated and submitted for re-approval.';
    if ($_GET['message'] === 'review_added') $msg = 'Review submitted and pending approval.';
}
$errorMsg = '';
if (isset($_GET['error'])) {
    if ($_GET['error'] === 'invalid_id') $errorMsg = 'Invalid review ID.';
    if ($_GET['error'] === 'not_allowed') $errorMsg = 'You are not allowed to delete this review.';
    if ($_GET['error'] === 'delete_failed') $errorMsg = 'Failed to delete the review.';
}

// fetch user's reviews with faculty & course names
$reviews = [];
try {
    $sql = "
      SELECT r.id, r.rating, r.comments, r.status, r.created_at,
             f.name AS faculty_name, f.designation AS faculty_designation,
             c.name AS course_name
      FROM reviews r
      LEFT JOIN faculty f ON r.faculty_id = f.id
      LEFT JOIN courses c ON r.course_id = c.id
      WHERE r.student_id = ?
      ORDER BY r.created_at DESC
    ";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $reviews[] = $row;
    }
    $stmt->close();
} catch (Exception $ex) {
    $errorMsg = "Error loading your reviews: " . $ex->getMessage();
}

// helper: safe echo
function e($s) { return htmlspecialchars((string)$s, ENT_QUOTES|ENT_SUBSTITUTE, 'UTF-8'); }
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>My Reviews</title>
<link rel="stylesheet" href="../dashboardstyle.css">
<link rel="stylesheet" href="../style.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
/* Local tweaks to present reviews nicely */
.container { max-width: 1100px; margin: 110px auto 60px; padding: 0 18px; }
.section-header { margin-bottom: 18px; display:flex; justify-content:space-between; align-items:flex-start; gap:12px; }
.section-title { font-size:2rem; font-weight:800; color:#fff; margin:0; }
.section-subtitle { color: rgba(255,255,255,0.8); margin-top:6px; }

/* messages */
.message { padding:12px 16px; border-radius:10px; margin-bottom:12px; }
.message.success { background: rgba(29, 209, 161, 0.08); color:#1dd3a7; border:1px solid rgba(29,209,167,0.08); }
.message.error { background: rgba(255,71,87,0.08); color:#ff4757; border:1px solid rgba(255,71,87,0.15); }

/* reviews list */
.reviews-list { display:flex; flex-direction:column; gap:14px; margin-top:6px; }
.review-card {
  background: rgba(255,255,255,0.02);
  border-radius:12px;
  padding:18px;
  display:flex;
  flex-direction:column;
  border:1px solid rgba(255,255,255,0.03);
  box-shadow: 0 8px 20px rgba(0,0,0,0.04);
}
.review-top { display:flex; justify-content:space-between; align-items:flex-start; gap:12px; flex-wrap:wrap; }
.review-meta { display:flex; gap:12px; align-items:center; }
.faculty-name { font-weight:800; font-size:1.05rem; color:#fff; margin:0; }
.course-name { color:rgba(255,255,255,0.85); font-weight:600; margin-left:6px; font-size:0.98rem; }
.status-badge { font-weight:700; font-size:0.8rem; padding:6px 8px; border-radius:999px; }
.status-pending { background: linear-gradient(90deg, rgba(255,193,7,0.12), rgba(255,193,7,0.06)); color:#ffb703; border:1px solid rgba(255,193,7,0.12); }
.status-approved { background: linear-gradient(90deg, rgba(29,209,161,0.06), rgba(29,209,161,0.02)); color:#1dd3a7; border:1px solid rgba(29,209,161,0.06); }
.status-rejected { background: linear-gradient(90deg, rgba(255,71,87,0.06), rgba(255,71,87,0.02)); color:#ff4757; border:1px solid rgba(255,71,87,0.06); }

/* rating stars */
.review-rating { margin-top:10px; color:#ffd166; font-size:1.05rem; }
.review-comments { margin-top:12px; color:rgba(255,255,255,0.9); line-height:1.55; white-space:pre-wrap; }

/* footer area */
.review-footer { display:flex; justify-content:space-between; align-items:center; gap:12px; margin-top:14px; flex-wrap:wrap; }
.review-date { color:rgba(255,255,255,0.7); font-size:0.9rem; }
.review-actions { display:flex; gap:8px; align-items:center; }
.btn { display:inline-flex; align-items:center; gap:8px; padding:8px 12px; border-radius:10px; font-weight:700; text-decoration:none; cursor:pointer; border:none; }
.btn-primary { background: linear-gradient(45deg,#667eea,#764ba2); color:#fff; }
.btn-ghost { background: rgba(255,255,255,0.03); color:#fff; }
.btn-danger { background: transparent; color:#ff6b81; font-weight:700; padding:6px 10px; }

/* small screens */
@media (max-width:720px) {
  .review-top { flex-direction:column; align-items:flex-start; }
  .review-footer { flex-direction:column; align-items:flex-start; gap:8px; }
}
</style>
</head>
<body>
  <?php include '../navbar.php'; ?>

  <div class="container" role="main">
    <div class="section-header">
      <div>
        <h1 class="section-title">My Reviews</h1>
        <div class="section-subtitle">View and manage your submitted reviews</div>
      </div>
      <div style="color:rgba(255,255,255,0.8); margin-top:6px;">Quick: edit or remove your reviews here</div>
    </div>

    <?php if ($msg): ?>
      <div class="message success"><?php echo e($msg); ?></div>
    <?php endif; ?>
    <?php if ($errorMsg): ?>
      <div class="message error"><?php echo e($errorMsg); ?></div>
    <?php endif; ?>

    <?php if (count($reviews) === 0): ?>
      <div class="review-card">
        <div style="color:rgba(255,255,255,0.9); font-weight:700; margin-bottom:8px;">No reviews yet</div>
        <div style="color:rgba(255,255,255,0.78);">You haven't submitted any reviews yet. Browse faculty to add a review.</div>
        <div style="margin-top:14px;">
          <a href="faculty.php" class="btn btn-primary"><i class="fas fa-users"></i> Browse Faculty</a>
        </div>
      </div>
    <?php else: ?>
      <div class="reviews-list" aria-live="polite">
        <?php foreach ($reviews as $rev): ?>
          <div class="review-card" id="review-<?php echo intval($rev['id']); ?>">
            <div class="review-top">
              <div class="review-meta">
                <div>
                  <p class="faculty-name"><?php echo e($rev['faculty_name'] ?? 'Unknown'); ?></p>
                  <div style="display:flex; align-items:center; gap:8px; margin-top:6px;">
                    <span class="course-name"><?php echo e($rev['course_name'] ?? 'Unknown course'); ?></span>
                    <?php if (!empty($rev['faculty_designation'])): ?>
                      <span style="color:rgba(255,255,255,0.7); font-size:0.9rem;">• <?php echo e($rev['faculty_designation']); ?></span>
                    <?php endif; ?>
                  </div>
                </div>
              </div>

              <div>
                <?php
                  $status = $rev['status'] ?? 'pending';
                  $cls = $status === 'approved' ? 'status-approved' : ($status === 'rejected' ? 'status-rejected' : 'status-pending');
                ?>
                <div class="status-badge <?php echo e($cls); ?>"><?php echo ucfirst(e($status)); ?></div>
              </div>
            </div>

            <div class="review-rating" aria-hidden="true">
              <?php $rating = intval($rev['rating']); for ($i=1;$i<=5;$i++): ?>
                <?php if ($i <= $rating && $rating > 0): ?>
                  <i class="fas fa-star"></i>
                <?php else: ?>
                  <i class="far fa-star" style="color:rgba(255,255,255,0.26)"></i>
                <?php endif; ?>
              <?php endfor; ?>
            </div>

            <div class="review-comments">
              <?php echo nl2br(e($rev['comments'])); ?>
            </div>

            <div class="review-footer">
              <div class="review-date"><?php echo date('M j, Y', strtotime($rev['created_at'])); ?></div>

              <div class="review-actions">
                <a href="edit-review.php?id=<?php echo intval($rev['id']); ?>" class="btn btn-primary"><i class="fas fa-edit"></i> Edit</a>
                <a href="delete-review.php?id=<?php echo intval($rev['id']); ?>" class="btn btn-danger delete-review"><i class="fas fa-trash"></i> Delete</a>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>

<script>
// confirmation for delete buttons
document.addEventListener('DOMContentLoaded', function() {
  document.querySelectorAll('.delete-review').forEach(function(el) {
    el.addEventListener('click', function(e) {
      if (!confirm('Are you sure you want to delete this review? This action cannot be undone.')) {
        e.preventDefault();
      }
    });
  });
});
</script>
</body>
</html>
<?php
$conn->close();
?>


