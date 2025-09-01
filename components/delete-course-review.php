<?php
// components/delete-course-review.php
ini_set('display_errors',1); error_reporting(E_ALL);
session_start();
include "../DBconnect.php";
if (!isset($_SESSION['username'])  !isset($_SESSION['id'])) { header("Location: ../index.php"); exit; }

$student_id = intval($_SESSION['id']);
$rid = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($rid <= 0) { header("Location: my-course-reviews.php?error=invalid_id"); exit; }

// verify ownership
$check = $conn->prepare("SELECT id FROM course_reviews WHERE id = ? AND student_id = ?");
$check->bind_param("ii",$rid,$student_id); $check->execute(); $r = $check->get_result();
if (!$r  $r->num_rows === 0) { header("Location: my-course-reviews.php?error=not_allowed"); exit; }

// delete
$del = $conn->prepare("DELETE FROM course_reviews WHERE id = ?");
$del->bind_param("i",$rid);
if ($del->execute()) {
  header("Location: my-course-reviews.php?message=deleted"); exit;
} else {
  header("Location: my-course-reviews.php?error=delete_failed"); exit;
}
?>
