<?php
session_start();
include "../DBconnect.php";

// Allow only logged-in non-admin users
if (!isset($_SESSION['username']) || !isset($_SESSION['id']) || $_SESSION['role'] === 'admin') {
    header("Location: ../index.php");
    exit();
}

$student_id = intval($_SESSION['id']);
$error = "";
$success = "";

/**
 * Helper: get or create 'Uncategorized' department and return its id
 */
function get_or_create_uncat_department($conn) {
    $name = 'Uncategorized';
    $stmt = $conn->prepare("SELECT id FROM departments WHERE name = ?");
    $stmt->bind_param("s", $name);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($row = $res->fetch_assoc()) {
        return intval($row['id']);
    }
    // create
    $stmt = $conn->prepare("INSERT INTO departments (name, description) VALUES (?, ?)");
    $desc = 'Auto-created default department';
    $stmt->bind_param("ss", $name, $desc);
    if ($stmt->execute()) {
        return intval($stmt->insert_id);
    }
    return null;
}

/**
 * Helper: find department by name, or create it if provided name isn't empty
 */
function find_or_create_department_by_name($conn, $dept_name) {
    $dept_name = trim($dept_name);
    if ($dept_name === '') {
        return get_or_create_uncat_department($conn);
    }
    // try find
    $stmt = $conn->prepare("SELECT id FROM departments WHERE name = ?");
    $stmt->bind_param("s", $dept_name);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($row = $res->fetch_assoc()) {
        return intval($row['id']);
    }
    // insert
    $stmt = $conn->prepare("INSERT INTO departments (name, description) VALUES (?, ?)");
    $desc = 'Created by user suggestion';
    $stmt->bind_param("ss", $dept_name, $desc);
    if ($stmt->execute()) {
        return intval($stmt->insert_id);
    }
    return null;
}

/**
 * Helper: insert faculty and return id (or return existing if same name exists)
 */
function get_or_create_faculty($conn, $faculty_name, $department_id) {
    $faculty_name = trim($faculty_name);
    if ($faculty_name === '') return null;
    // check existing (by name)
    $stmt = $conn->prepare("SELECT id FROM faculty WHERE name = ? LIMIT 1");
    $stmt->bind_param("s", $faculty_name);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($row = $res->fetch_assoc()) {
        return intval($row['id']);
    }
    // insert
    $stmt = $conn->prepare("INSERT INTO faculty (name, designation, department_id, bio, created_at) VALUES (?, ?, ?, ?, NOW())");
    $designation = 'Lecturer'; // default placeholder
    $bio = 'Added by student suggestion';
    $stmt->bind_param("ssis", $faculty_name, $designation, $department_id, $bio);
    if ($stmt->execute()) {
        return intval($stmt->insert_id);
    }
    return null;
}

/**
 * Helper: get or create course
 */
function get_or_create_course($conn, $course_name, $course_code, $department_id) {
    $course_name = trim($course_name);
    if ($course_name === '') return null;
    // try existing by name or code
    $stmt = $conn->prepare("SELECT id FROM courses WHERE name = ? LIMIT 1");
    $stmt->bind_param("s", $course_name);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($row = $res->fetch_assoc()) return intval($row['id']);

    if ($course_code && trim($course_code) !== '') {
        $stmt = $conn->prepare("SELECT id FROM courses WHERE code = ? LIMIT 1");
        $stmt->bind_param("s", $course_code);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($row = $res->fetch_assoc()) return intval($row['id']);
    }

    // insert
    $stmt = $conn->prepare("INSERT INTO courses (name, code, department_id, description, credits, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
    $desc = 'Added by student suggestion';
    $credits = 3;
    $stmt->bind_param("ssiss", $course_name, $course_code, $department_id, $desc, $credits);
    if ($stmt->execute()) {
        return intval($stmt->insert_id);
    }
    return null;
}

// Handle POST submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Received values
    $faculty_id_post = isset($_POST['faculty_id']) ? $_POST['faculty_id'] : '';
    $course_id_post  = isset($_POST['course_id']) ? $_POST['course_id'] : '';
    $rating = isset($_POST['rating']) ? intval($_POST['rating']) : 0;
    $comments = isset($_POST['comments']) ? trim($_POST['comments']) : '';

    // New faculty fields (if user chose to add)
    $new_faculty_name = isset($_POST['new_faculty_name']) ? trim($_POST['new_faculty_name']) : '';
    $new_faculty_dept = isset($_POST['new_faculty_dept']) ? trim($_POST['new_faculty_dept']) : '';

    // New course fields
    $new_course_name = isset($_POST['new_course_name']) ? trim($_POST['new_course_name']) : '';
    $new_course_code = isset($_POST['new_course_code']) ? trim($_POST['new_course_code']) : '';
    $new_course_dept = isset($_POST['new_course_dept']) ? trim($_POST['new_course_dept']) : '';

    // Basic validation
    if (($faculty_id_post === '' || $faculty_id_post === '0') && $new_faculty_name === '') {
        $error = "Please select a faculty or add a new faculty name.";
    } elseif (($course_id_post === '' || $course_id_post === '0') && $new_course_name === '') {
        $error = "Please select a course or add a new course name.";
    } elseif ($rating < 1 || $rating > 5) {
        $error = "Please provide a rating between 1 and 5.";
    } elseif ($comments === '') {
        $error = "Please add comments about the faculty.";
    } else {
        // Determine final department and insert new faculty/course as needed
        // If new faculty is provided, find/create its department (if provided) or use uncategorized
        if ($new_faculty_name !== '') {
            $dept_id_for_faculty = ($new_faculty_dept !== '') ? find_or_create_department_by_name($conn, $new_faculty_dept) : get_or_create_uncat_department($conn);
            $faculty_id = get_or_create_faculty($conn, $new_faculty_name, $dept_id_for_faculty);
            if (!$faculty_id) {
                $error = "Failed to create new faculty. Try again.";
            }
        } else {
            // use selected existing faculty
            $faculty_id = intval($faculty_id_post);
        }

        // Now handle course
        if ($new_course_name !== '') {
            // prefer department from new_course_dept, else use faculty's department if available
            if ($new_course_dept !== '') {
                $dept_id_for_course = find_or_create_department_by_name($conn, $new_course_dept);
            } else {
                // try to get department of the faculty we just used
                $dept_id_for_course = null;
                if (!empty($faculty_id)) {
                    $stmt = $conn->prepare("SELECT department_id FROM faculty WHERE id = ? LIMIT 1");
                    $stmt->bind_param("i", $faculty_id);
                    $stmt->execute();
                    $res = $stmt->get_result();
                    if ($r = $res->fetch_assoc()) {
                        $dept_id_for_course = intval($r['department_id']);
                    }
                }
                if (!$dept_id_for_course) $dept_id_for_course = get_or_create_uncat_department($conn);
            }

            $course_id = get_or_create_course($conn, $new_course_name, $new_course_code, $dept_id_for_course);
            if (!$course_id) {
                $error = "Failed to create new course. Try again.";
            }
        } else {
            $course_id = intval($course_id_post);
        }

        // If no error so far, insert the review
        if ($error === '') {
            $stmt = $conn->prepare("INSERT INTO reviews (student_id, faculty_id, course_id, rating, comments, status) VALUES (?, ?, ?, ?, ?, 'pending')");
            $stmt->bind_param("iiiis", $student_id, $faculty_id, $course_id, $rating, $comments);
            if ($stmt->execute()) {
                // success -> redirect to my-reviews page
                header("Location: my-reviews.php?message=review_added");
                exit();
            } else {
                $error = "Error saving review: " . $conn->error;
            }
        }
    }
}

// Preselect if faculty_id provided in URL
$preselected_faculty = isset($_GET['faculty_id']) ? intval($_GET['faculty_id']) : 0;

// Fetch faculty and courses for selects
$faculty_stmt = $conn->prepare("SELECT id, name FROM faculty ORDER BY name");
$faculty_stmt->execute();
$faculty_result = $faculty_stmt->get_result();

$course_stmt = $conn->prepare("SELECT id, name FROM courses ORDER BY name");
$course_stmt->execute();
$course_result = $course_stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width,initial-scale=1" />
<title>Add Review - FacultyHub</title>
<link rel="stylesheet" href="../dashboardstyle.css">
<link rel="stylesheet" href="../style.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
/* small overrides */
.review-container { max-width: 900px; margin: 2rem auto; padding: 0 1rem; }
.review-card { padding: 2rem; border-radius: 12px; }
.form-group { margin-bottom: 1.25rem; }
label { display:block; margin-bottom: .5rem; color: #fff; font-weight:600; }
.select-input, textarea, input[type="text"] { width:100%; padding: .8rem 1rem; border-radius:8px; border:1px solid rgba(255,255,255,0.12); background: rgba(255,255,255,0.04); color: #fff; }
.form-actions { margin-top: 1rem; display:flex; gap: .75rem; flex-wrap:wrap; }
.message { margin-bottom: 1rem; padding: .8rem 1rem; border-radius:8px; }
.message.error { background: rgba(255,71,87,0.15); color:#ff4757; border:1px solid rgba(255,71,87,0.2); }
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
        <div class="nav-brand"><div class="brand-icon"><i class="fas fa-graduation-cap"></i></div><span class="brand-text">FacultyHub</span></div>
        <div class="nav-menu">
            <a href="../dashboard_user.php" class="nav-btn"><i class="fas fa-home"></i> <span>Dashboard</span></a>
            <a href="faculty.php" class="nav-btn"><i class="fas fa-users"></i> <span>Faculty</span></a>
            <a href="my-reviews.php" class="nav-btn"><i class="fas fa-star"></i> <span>My Reviews</span></a>
            <a href="../logout.php" class="nav-btn"><i class="fas fa-sign-out-alt"></i> <span>Logout</span></a>
        </div>
    </div>
</nav>

<div class="review-container">
    <div class="review-card">
        <div class="review-header">
            <h1 style="color:white; margin-bottom:.3rem;"><i class="fas fa-star"></i> Submit Review</h1>
            <p style="color:rgba(255,255,255,0.8); margin-bottom:1rem;">Tell others about your experience</p>
        </div>

        <?php if ($error): ?>
            <div class="message error"><i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="POST" class="review-form" id="reviewForm" novalidate>
            <div class="form-group">
                <label for="faculty_id"><i class="fas fa-user"></i> Faculty</label>
                <select name="faculty_id" id="faculty_id" class="select-input">
                    <option value="">Select Faculty</option>
                    <?php while ($f = $faculty_result->fetch_assoc()): ?>
                        <option value="<?php echo $f['id']; ?>" <?php echo ($preselected_faculty === intval($f['id'])) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($f['name']); ?>
                        </option>
                    <?php endwhile; ?>
                    <option value="__new">+ Add new faculty</option>
                </select>
            </div>

            <div id="newFacultyBlock" style="display:none; margin-bottom:1rem;">
                <div class="form-group">
                    <label for="new_faculty_name">New Faculty Name</label>
                    <input type="text" name="new_faculty_name" id="new_faculty_name" class="select-input" placeholder="Full name (e.g. Dr. John Doe)">
                </div>
                <div class="form-group">
                    <label for="new_faculty_dept">Department (optional)</label>
                    <input type="text" name="new_faculty_dept" id="new_faculty_dept" class="select-input" placeholder="Department name (optional)">
                </div>
            </div>

            <div class="form-group">
                <label for="course_id"><i class="fas fa-book"></i> Course</label>
                <select name="course_id" id="course_id" class="select-input">
                    <option value="">Select Course</option>
                    <?php
                    // Rewind or re-run query for courses (we used prepared earlier)
                    $course_stmt->execute();
                    $course_result = $course_stmt->get_result();
                    while ($c = $course_result->fetch_assoc()): ?>
                        <option value="<?php echo $c['id']; ?>"><?php echo htmlspecialchars($c['name']); ?></option>
                    <?php endwhile; ?>
                    <option value="__new">+ Add new course</option>
                </select>
            </div>

            <div id="newCourseBlock" style="display:none; margin-bottom:1rem;">
                <div class="form-group">
                    <label for="new_course_name">New Course Name</label>
                    <input type="text" name="new_course_name" id="new_course_name" class="select-input" placeholder="Course title (e.g. Calculus I)">
                </div>
                <div class="form-group">
                    <label for="new_course_code">Course Code (optional)</label>
                    <input type="text" name="new_course_code" id="new_course_code" class="select-input" placeholder="e.g. MATH101">
                </div>
                <div class="form-group">
                    <label for="new_course_dept">Department (optional)</label>
                    <input type="text" name="new_course_dept" id="new_course_dept" class="select-input" placeholder="Department name (optional)">
                </div>
            </div>

            <div class="form-group">
                <label for="rating"><i class="fas fa-star-half-alt"></i> Rating</label>
                <select name="rating" id="rating" class="select-input" required>
                    <option value="">Select Rating</option>
                    <?php for ($i=5;$i>=1;$i--): ?>
                        <option value="<?php echo $i; ?>"><?php echo $i; ?> star<?php echo $i>1 ? 's' : ''; ?></option>
                    <?php endfor; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="comments"><i class="fas fa-comment"></i> Comments</label>
                <textarea name="comments" id="comments" rows="5" placeholder="Share your thoughts..." required></textarea>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary"><i class="fas fa-paper-plane"></i> Submit Review</button>
                <a href="my-reviews.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back to My Reviews</a>
            </div>
        </form>
    </div>
</div>

<script>
// Toggle new faculty/course blocks
document.addEventListener('DOMContentLoaded', function() {
    const facultySelect = document.getElementById('faculty_id');
    const newFacultyBlock = document.getElementById('newFacultyBlock');
    const courseSelect = document.getElementById('course_id');
    const newCourseBlock = document.getElementById('newCourseBlock');

    function toggleFaculty() {
        if (facultySelect.value === '__new') {
            newFacultyBlock.style.display = 'block';
        } else {
            newFacultyBlock.style.display = 'none';
            // clear inputs when hiding
            document.getElementById('new_faculty_name').value = '';
            document.getElementById('new_faculty_dept').value = '';
        }
    }

    function toggleCourse() {
        if (courseSelect.value === '__new') {
            newCourseBlock.style.display = 'block';
        } else {
            newCourseBlock.style.display = 'none';
            document.getElementById('new_course_name').value = '';
            document.getElementById('new_course_code').value = '';
            document.getElementById('new_course_dept').value = '';
        }
    }

    facultySelect.addEventListener('change', toggleFaculty);
    courseSelect.addEventListener('change', toggleCourse);

    // initialize (in case user opened with preselected)
    toggleFaculty();
    toggleCourse();
});
</script>
</body>
</html>
<?php $conn->close(); ?>



