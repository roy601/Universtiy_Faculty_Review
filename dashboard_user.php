<?php
// dashboard_user.php
// Full replacement — organized layout, larger distinct user box, no shortcuts.
// Small change: user-box left action now says "My Faculty Reviews".
// Added: Profile picture upload functionality

ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();
include "DBconnect.php";

if (!isset($_SESSION['username']) || !isset($_SESSION['id'])) {
    header("Location: index.php");
    exit();
}

$user_id = intval($_SESSION['id']);

// Get current user's profile picture
$profile_picture = null;
$stmt = $conn->prepare("SELECT profile_picture FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
if ($row = $result->fetch_assoc()) {
    $profile_picture = $row['profile_picture'];
}

// counts
$counts = ['faculty' => 0, 'courses' => 0, 'my_reviews' => 0, 'my_course_reviews' => 0];
try { $r = $conn->query("SELECT COUNT(*) AS c FROM faculty"); if ($r) $counts['faculty'] = intval($r->fetch_assoc()['c']); } catch (Exception $ex) {}
try { $r = $conn->query("SELECT COUNT(*) AS c FROM courses"); if ($r) $counts['courses'] = intval($r->fetch_assoc()['c']); } catch (Exception $ex) {}

try {
    $stmt = $conn->prepare("SELECT COUNT(*) AS c FROM reviews WHERE student_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res) $counts['my_reviews'] = intval($res->fetch_assoc()['c']);
    $stmt->close();
} catch (Exception $ex) {}

try {
    $stmt = $conn->prepare("SELECT COUNT(*) AS c FROM course_reviews WHERE student_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res) $counts['my_course_reviews'] = intval($res->fetch_assoc()['c']);
    $stmt->close();
} catch (Exception $ex) {}

function e($s){ return htmlspecialchars((string)$s, ENT_QUOTES|ENT_SUBSTITUTE, 'UTF-8'); }
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width,initial-scale=1" />
<title>Dashboard — FacultyHub</title>

<link rel="stylesheet" href="dashboardstyle.css">
<link rel="stylesheet" href="style.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<style>
:root{
  --card-bg: rgba(255,255,255,0.03);
  --muted: rgba(255,255,255,0.88);
  --muted-2: rgba(255,255,255,0.68);
  --accent1: #667eea;
  --accent2: #764ba2;
}

/* make sure fixed nav doesn't overlap content */
.navbar { z-index: 1200; position: fixed; top: 0; left: 0; right: 0; }

/* container pushed below navbar */
.container { max-width: 1200px; margin: 110px auto 56px; padding: 0 20px 56px; }

/* hero grid: welcome left, user box right */
.hero {
  display: grid;
  grid-template-columns: 1fr 360px;
  gap: 22px;
  align-items: start;
  margin-bottom: 28px;
}
@media (max-width:1024px) { .hero { grid-template-columns: 1fr 300px; } }
@media (max-width:820px) { .hero { grid-template-columns: 1fr; } }

/* welcome card */
.welcome-card {
  background: linear-gradient(135deg, rgba(102,126,234,0.06), rgba(118,75,162,0.05));
  padding: 30px;
  border-radius: 14px;
  color: #fff;
  border: 1px solid rgba(255,255,255,0.03);
  box-shadow: 0 14px 40px rgba(0,0,0,0.07);
}
.welcome-card h1 { font-size: 2rem; margin: 0 0 8px; font-weight:800; letter-spacing:-0.5px; }
.welcome-card p { color:var(--muted-2); margin:0 0 14px; font-size:1rem; }

/* user box (distinct and larger) */
.user-box {
  background: var(--card-bg);
  padding: 20px;
  border-radius: 14px;
  color: #fff;
  display:flex;
  gap:16px;
  align-items:center;
  justify-content:space-between;
  flex-direction:column;
  border: 1px solid rgba(255,255,255,0.02);
  box-shadow: 0 10px 28px rgba(0,0,0,0.06);
  min-height:140px;
}
.user-top { display:flex; gap:14px; align-items:center; width:100%; }
.avatar {
  width:72px; height:72px; border-radius:12px; display:flex; align-items:center; justify-content:center;
  background:linear-gradient(45deg,var(--accent1),var(--accent2)); font-size:28px; color:#fff; font-weight:800;
  flex-shrink:0;
  overflow: hidden;
}
.avatar img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}
.user-meta { min-width:0; }
.user-meta .name { font-size:1.12rem; font-weight:800; color:#fff; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; max-width:200px; }
.user-meta .role { color:var(--muted-2); margin-top:6px; font-size:0.92rem; }
.user-meta .username { color:var(--muted-2); margin-top:6px; font-size:0.86rem; }

/* user actions horizontal on wide, vertical on small */
.user-actions { display:flex; gap:10px; width:100%; justify-content:space-between; margin-top:12px; }
.user-actions a { flex:1; display:inline-flex; gap:8px; align-items:center; justify-content:center; padding:10px 12px; border-radius:10px; text-decoration:none; font-weight:800; border:none; }
.user-actions .primary { background: linear-gradient(45deg,var(--accent1),var(--accent2)); color:#fff; }
.user-actions .ghost { background: rgba(255,255,255,0.03); color:#fff; }

/* cards grid: two rows, two columns for desktop, single column on small */
.grid {
  display:grid;
  grid-template-columns: repeat(2, 1fr);
  gap:18px;
  margin-top:8px;
}
@media (max-width:900px) { .grid { grid-template-columns: 1fr; } }

.card {
  background: var(--card-bg);
  padding:20px;
  border-radius:14px;
  color:#fff;
  border: 1px solid rgba(255,255,255,0.02);
  box-shadow: 0 8px 18px rgba(0,0,0,0.04);
  display:flex;
  flex-direction:column;
  min-height:150px;
  transition: transform .16s ease, box-shadow .16s ease;
}
.card:hover { transform: translateY(-6px); box-shadow: 0 18px 42px rgba(0,0,0,0.08); }

.card .top { display:flex; gap:12px; align-items:center; }
.card .icon { width:56px; height:56px; border-radius:12px; display:flex; align-items:center; justify-content:center; font-size:20px; color:#fff; background:linear-gradient(45deg,var(--accent1),var(--accent2)); box-shadow: 0 8px 20px rgba(0,0,0,0.06); }
.card .title { font-weight:800; font-size:1.05rem; margin:0; }
.card .sub { color:var(--muted-2); margin-top:8px; font-size:0.95rem; }

.card .count { margin-top:14px; font-size:1.6rem; font-weight:900; color:#fff; }

.card .actions { display:flex; gap:10px; margin-top:auto; }
.card .actions a { display:inline-flex; align-items:center; justify-content:center; gap:8px; padding:10px 12px; border-radius:10px; text-decoration:none; font-weight:800; border:none; }
.actions .primary { background: linear-gradient(45deg,var(--accent1),var(--accent2)); color:#fff; }
.actions .ghost { background: rgba(255,255,255,0.03); color:#fff; }

.small { font-size:0.92rem; color:var(--muted-2); }

/* profile picture update button */
.update-picture-btn {
  margin-top: 10px;
  display: inline-flex;
  align-items: center;
  gap: 6px;
  padding: 8px 12px;
  background: rgba(255,255,255,0.05);
  border-radius: 8px;
  color: #fff;
  text-decoration: none;
  font-size: 0.85rem;
  transition: background 0.2s;
}
.update-picture-btn:hover {
  background: rgba(255,255,255,0.1);
}

/* responsive tweaks */
@media (max-width:520px) {
  .user-actions { flex-direction:column; }
  .avatar { width:64px; height:64px; font-size:24px; }
  .card { min-height:130px; padding:16px; }
}
</style>
</head>
<body>
  <div class="animated-bg">
    <div class="gradient-orb orb-1"></div>
    <div class="gradient-orb orb-2"></div>
    <div class="gradient-orb orb-3"></div>
  </div>

  <nav class="navbar">
    <div class="nav-container">
      <div class="nav-brand">
        <div class="brand-icon"><i class="fas fa-graduation-cap"></i></div>
        <div class="brand-text">FacultyHub</div>
      </div>

      <div class="nav-menu">
        <a href="dashboard_user.php" class="nav-btn active"><i class="fas fa-home"></i> Dashboard</a>
        <a href="components/faculty.php" class="nav-btn"><i class="fas fa-users"></i> Faculty</a>
        <a href="components/courses.php" class="nav-btn"><i class="fas fa-book"></i> Courses</a>
        <a href="components/my-reviews.php" class="nav-btn"><i class="fas fa-star"></i> My Reviews</a>
        <a href="components/my-course-reviews.php" class="nav-btn"><i class="fas fa-star-half-alt"></i> My Course Reviews</a>
        <a href="logout.php" class="nav-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
      </div>
    </div>
  </nav>

  <div class="container" role="main">
    <div class="hero">
      <div class="welcome-card">
        <h1>Welcome back, <?php echo e($_SESSION['username']); ?> 👋</h1>
        <p>Jump right into what matters — browse faculty, explore courses, or manage your reviews.</p>

        <div style="display:flex;gap:12px;flex-wrap:wrap;margin-top:12px;">
          <a href="components/faculty.php" class="btn btn-primary" style="padding:12px 16px;"><i class="fas fa-users"></i> Browse Faculty</a>
          <a href="components/courses.php" class="btn btn-ghost" style="padding:12px 16px;"><i class="fas fa-book"></i> Browse Courses</a>
        </div>
      </div>

      <div class="user-box" aria-label="User summary">
        <div class="user-top">
          <div class="avatar">
            <?php if ($profile_picture && file_exists('img/uploads/' . $profile_picture)): ?>
              <img src="img/uploads/<?php echo e($profile_picture); ?>" alt="Profile Picture">
            <?php else: ?>
              <?php echo strtoupper(substr(e($_SESSION['username']),0,1)); ?>
            <?php endif; ?>
          </div>

          <div class="user-meta">
            <div class="name"><?php echo e($_SESSION['name'] ?? $_SESSION['username']); ?></div>
            <div class="role">Role: <?php echo e($_SESSION['role'] ?? 'user'); ?></div>
            <div class="username small"><?php echo e($_SESSION['username']); ?></div>
            
            <!-- Profile picture update button -->
            <a href="components/upload_profile.php" class="update-picture-btn">
              <i class="fas fa-camera"></i> Update Picture
            </a>
          </div>
        </div>

        <div class="user-actions">
          <!-- Changed the left button text to "My Faculty Reviews" per your request -->
          <a href="components/my-reviews.php" class="primary"><i class="fas fa-star"></i> My Faculty Reviews</a>
          <a href="components/my-course-reviews.php" class="ghost"><i class="fas fa-star-half-alt"></i> My Course Reviews</a>
        </div>
      </div>
    </div>

    <!-- organized cards: two columns -->
    <div class="grid" role="region" aria-label="Primary panels">
      <div class="card" title="Faculty">
        <div class="top">
          <div class="icon"><i class="fas fa-users"></i></div>
          <div>
            <div class="title">Faculty</div>
            <div class="sub small">Browse faculty members</div>
          </div>
        </div>

        <div class="count"><?php echo e($counts['faculty']); ?></div>

        <div class="actions">
          <a href="components/faculty.php" class="primary"><i class="fas fa-users"></i> Browse</a>
          <a href="components/add-review.php" class="ghost"><i class="fas fa-plus"></i> Add</a>
        </div>
      </div>

      <div class="card" title="Courses">
        <div class="top">
          <div class="icon"><i class="fas fa-book"></i></div>
          <div>
            <div class="title">Courses</div>
            <div class="sub small">Browse courses</div>
          </div>
        </div>

        <div class="count"><?php echo e($counts['courses']); ?></div>

        <div class="actions">
          <a href="components/courses.php" class="primary"><i class="fas fa-book"></i> Browse</a>
          <a href="components/add-course-review.php" class="ghost"><i class="fas fa-plus"></i> Review</a>
        </div>
      </div>

      <div class="card" title="My Faculty Reviews">
        <div class="top">
          <div class="icon"><i class="fas fa-star"></i></div>
          <div>
            <div class="title">My Faculty Reviews</div>
            <div class="sub small">Manage your faculty reviews</div>
          </div>
        </div>

        <div class="count"><?php echo e($counts['my_reviews']); ?></div>

        <div class="actions">
          <a href="components/my-reviews.php" class="primary"><i class="fas fa-star"></i> My Reviews</a>
        </div>
      </div>

      <div class="card" title="My Course Reviews">
        <div class="top">
          <div class="icon"><i class="fas fa-star-half-alt"></i></div>
          <div>
            <div class="title">My Course Reviews</div>
            <div class="sub small">Manage your course reviews</div>
          </div>
        </div>

        <div class="count"><?php echo e($counts['my_course_reviews']); ?></div>

        <div class="actions">
          <a href="components/my-course-reviews.php" class="primary"><i class="fas fa-star"></i> My Course Reviews</a>
        </div>
      </div>
    </div>
  </div>

<script>
  // small defensive: prevent nav/content overlap on very short viewports
  document.addEventListener('DOMContentLoaded', function(){
    var nav = document.querySelector('.navbar');
    var c = document.querySelector('.container');
    if (nav && c) {
      var h = nav.getBoundingClientRect().height;
      c.style.marginTop = (h + 36) + 'px';
    }
  });
</script>
</body>
</html>
<?php
$conn->close();
?>