<?php
// components/edit-review.php
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();
include "../DBconnect.php";

// require login
if (!isset($_SESSION['username']) || !isset($_SESSION['id'])) {
    header("Location: ../index.php");
    exit();
}

$user_id = intval($_SESSION['id']);
$review_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($review_id <= 0) {
    header("Location: my-reviews.php?error=invalid_id");
    exit();
}

$error = '';
$success = '';

// load review & check ownership
$stmt = $conn->prepare("
    SELECT r.id, r.rating, r.comments, r.status, r.faculty_id, r.course_id,
           f.name AS faculty_name, c.name AS course_name
    FROM reviews r
    LEFT JOIN faculty f ON r.faculty_id = f.id
    LEFT JOIN courses c ON r.course_id = c.id
    WHERE r.id = ? AND r.student_id = ?
    LIMIT 1
");
$stmt->bind_param("ii", $review_id, $user_id);
$stmt->execute();
$res = $stmt->get_result();

if (!$res || $res->num_rows === 0) {
    header("Location: my-reviews.php?error=not_found");
    exit();
}
$review = $res->fetch_assoc();
$stmt->close();

// handle POST update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rating = isset($_POST['rating']) ? intval($_POST['rating']) : 0;
    $comments = isset($_POST['comments']) ? trim($_POST['comments']) : '';

    if ($rating < 1 || $rating > 5) {
        $error = "Please select a rating between 1 and 5.";
    } elseif ($comments === '') {
        $error = "Please enter your comments.";
    } else {
        $u = $conn->prepare("UPDATE reviews SET rating = ?, comments = ?, updated_at = NOW(), status = 'pending' WHERE id = ? AND student_id = ?");
        $u->bind_param("isii", $rating, $comments, $review_id, $user_id);
        if ($u->execute()) {
            $u->close();
            header("Location: my-reviews.php?message=updated");
            exit();
        } else {
            $error = "Update failed: " . htmlspecialchars($conn->error);
            $u->close();
        }
    }
}

// helper for safe output
function e($s){ return htmlspecialchars((string)$s, ENT_QUOTES|ENT_SUBSTITUTE, 'UTF-8'); }
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Edit Review — FacultyHub</title>

  <link rel="stylesheet" href="../dashboardstyle.css">
  <link rel="stylesheet" href="../style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

  <style>
    /* Page layout */
    .container { max-width: 1000px; margin: 28px auto; padding: 0 18px; }
    .card { display:flex; gap:24px; background: rgba(255,255,255,0.03); border-radius:12px; padding:20px; align-items:flex-start; }
    .left, .right { flex:1; min-width:0; }
    .left { max-width: 420px; }
    .meta { color: rgba(255,255,255,0.9); margin-bottom:8px; }
    .meta h2 { margin:0 0 6px; color:#fff; }
    .meta p { margin:6px 0; color:rgba(255,255,255,0.82); }

    /* Form */
    .form-card { background: transparent; padding: 6px 0; }
    label { display:block; margin-bottom:6px; color:#fff; font-weight:600; }
    .select-input, .text-input, textarea { width:100%; padding:12px 14px; border-radius:10px; border:1px solid rgba(255,255,255,0.06); background: rgba(255,255,255,0.02); color:#fff; font-size:1rem; }
    textarea { min-height:160px; resize:vertical; }

    .form-actions { display:flex; gap:12px; align-items:center; margin-top:14px; flex-wrap:wrap; }
    .btn { display:inline-flex; align-items:center; gap:8px; padding:10px 16px; border-radius:10px; cursor:pointer; text-decoration:none; border:none; font-weight:600; }
    .btn-primary { background: linear-gradient(45deg,#667eea,#764ba2); color:white; }
    .btn-secondary { background: rgba(255,255,255,0.06); color: white; }
    .btn-danger { background: transparent; color:#ff6b6b; border:1px solid rgba(255,107,107,0.12); }

    /* Message */
    .message { padding:12px; border-radius:8px; margin-bottom:12px; }
    .message.error { background: rgba(255,71,87,0.12); color:#ff4757; border:1px solid rgba(255,71,87,0.12); }
    .message.success { background: rgba(72,187,120,0.12); color:#48bb78; border:1px solid rgba(72,187,120,0.12); }

    /* star rating visual */
    .stars { display:flex; gap:6px; align-items:center; }
    .stars i { font-size:22px; color: rgba(255,255,255,0.25); cursor:pointer; transition: transform .08s ease; }
    .stars i.hover, .stars i.active { color:#ffd166; transform: translateY(-2px); }

    /* responsive */
    @media (max-width:900px) {
      .card { flex-direction:column; }
      .left { max-width:none; }
    }
  </style>
</head>
<body>
  <div class="animated-bg"><div class="gradient-orb orb-1"></div><div class="gradient-orb orb-2"></div><div class="gradient-orb orb-3"></div></div>

<?php include '../navbar.php'; ?>


  <div class="container">
    <h1 style="color:white; margin-bottom:10px;"><i class="fas fa-edit"></i> Edit Review</h1>
    <p style="color:rgba(255,255,255,0.8); margin-bottom:18px;">Editing will set the review back to pending for re-approval.</p>

    <?php if ($error): ?>
      <div class="message error"><i class="fas fa-exclamation-circle"></i> <?php echo e($error); ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
      <div class="message success"><i class="fas fa-check-circle"></i> <?php echo e($success); ?></div>
    <?php endif; ?>

    <div class="card" role="main" aria-labelledby="edit-review-title">
      <div class="left" aria-hidden="false">
        <div class="meta">
          <h2 id="edit-review-title"><?php echo e($review['faculty_name'] ?? 'Faculty'); ?></h2>
          <p style="margin-top:6px;"><strong>Course</strong><br><span style="color:rgba(255,255,255,0.85);"><?php echo e($review['course_name'] ?? 'Course'); ?></span></p>
          <p style="margin-top:6px;"><strong>Status</strong><br><span style="color:rgba(255,255,255,0.75); text-transform:capitalize;"><?php echo e($review['status']); ?></span></p>

          <div style="margin-top:16px;">
            <div style="color:rgba(255,255,255,0.85); font-weight:700; margin-bottom:8px;">Your current rating</div>
            <div class="stars" aria-hidden="false">
              <!-- visual stars will be controlled by JS; these reflect the current rating -->
              <?php $cur = intval($review['rating']); for ($i=1;$i<=5;$i++): ?>
                <i class="<?php echo $i <= $cur ? 'active' : ''; ?> fas fa-star" data-value="<?php echo $i; ?>"></i>
              <?php endfor; ?>
            </div>
          </div>

        </div>
      </div>

      <div class="right">
        <form method="POST" novalidate>
          <div class="form-card">
            <label for="rating">Rating</label>
            <!-- hidden input stores numeric rating for form submit -->
            <input type="hidden" name="rating" id="rating" value="<?php echo intval($review['rating']); ?>">

            <!-- accessible dropdown fallback for rating -->
            <select id="rating-select" style="display:none;" name="rating_select">
              <option value="">Select rating</option>
              <?php for ($i=5;$i>=1;$i--): ?>
                <option value="<?php echo $i; ?>" <?php echo intval($review['rating']) === $i ? 'selected' : ''; ?>><?php echo $i; ?> star<?php echo $i>1 ? 's' : ''; ?></option>
              <?php endfor; ?>
            </select>

            <label for="comments" style="margin-top:12px;">Comments</label>
            <textarea name="comments" id="comments" class="text-input" placeholder="Edit your review"><?php echo e($review['comments']); ?></textarea>
          </div>

          <div class="form-actions" style="margin-top:18px;">
            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Changes</button>
            <a href="my-reviews.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Cancel</a>
            <a href="delete-review.php?id=<?php echo intval($review['id']); ?>" class="btn btn-danger delete-review" style="margin-left:auto;"><i class="fas fa-trash"></i> Delete</a>
          </div>
        </form>
      </div>
    </div>
  </div>

<script>
// star rating interaction
document.addEventListener('DOMContentLoaded', function() {
  var stars = document.querySelectorAll('.stars i');
  var hidden = document.getElementById('rating');
  var selectFallback = document.getElementById('rating-select');

  function setRating(val) {
    hidden.value = val;
    // sync fallback select (if visible)
    if (selectFallback) selectFallback.value = val;
    stars.forEach(function(s){
      var v = parseInt(s.getAttribute('data-value'),10);
      if (v <= val) s.classList.add('active');
      else s.classList.remove('active');
    });
  }

  stars.forEach(function(s){
    s.addEventListener('mouseenter', function(){
      var val = parseInt(this.getAttribute('data-value'),10);
      stars.forEach(function(ss){ if (parseInt(ss.getAttribute('data-value'),10) <= val) ss.classList.add('hover'); else ss.classList.remove('hover'); });
    });
    s.addEventListener('mouseleave', function(){
      stars.forEach(function(ss){ ss.classList.remove('hover'); });
    });
    s.addEventListener('click', function(){
      var val = parseInt(this.getAttribute('data-value'),10);
      setRating(val);
    });
  });

  // initialize from hidden input (already set server-side)
  var cur = parseInt(hidden.value || '0', 10);
  if (cur > 0) setRating(cur);

  // delete confirm
  document.querySelectorAll('.delete-review').forEach(function(el){
    el.addEventListener('click', function(e){
      if (!confirm('Are you sure you want to delete this review? This cannot be undone.')) {
        e.preventDefault();
      }
    });
  });
});
</script>
</body>
</html>
<?php
// cleanup
$conn->close();
?>
message.txt
11 KB