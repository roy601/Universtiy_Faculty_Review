<?php
// components/delete-course-review.php
// Safely delete a course review belonging to the logged-in user.
// Replaces the previous broken version.

ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();
include "../DBconnect.php";

// Ensure user is logged in
if (!isset($_SESSION['username']) || !isset($_SESSION['id'])) {
    header("Location: ../index.php");
    exit();
}

$student_id = intval($_SESSION['id']);
$rid = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($rid <= 0) {
    header("Location: my-course-reviews.php?error=invalid_id");
    exit();
}

// Verify ownership: ensure this review belongs to the current student
$check = $conn->prepare("SELECT id FROM course_reviews WHERE id = ? AND student_id = ? LIMIT 1");
if (!$check) {
    // If prepare fails, return a generic error
    header("Location: my-course-reviews.php?error=server_error");
    exit();
}
$check->bind_param("ii", $rid, $student_id);
$check->execute();
$res = $check->get_result();
$check->close();

if (!$res || $res->num_rows === 0) {
    // No matching review or not owned by this user
    header("Location: my-course-reviews.php?error=not_allowed");
    exit();
}

// Perform delete (limit by student_id as an extra safety)
$del = $conn->prepare("DELETE FROM course_reviews WHERE id = ? AND student_id = ?");
if (!$del) {
    header("Location: my-course-reviews.php?error=server_error");
    exit();
}
$del->bind_param("ii", $rid, $student_id);

if ($del->execute()) {
    $del->close();
    $conn->close();
    header("Location: my-course-reviews.php?message=deleted");
    exit();
} else {
    $del->close();
    $conn->close();
    header("Location: my-course-reviews.php?error=delete_failed");
    exit();
}

