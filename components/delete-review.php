<?php
// components/delete-review.php
ob_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();
include "../DBconnect.php";

if (!isset($_SESSION['username']) || !isset($_SESSION['id'])) {
    header("Location: ../index.php");
    exit();
}

$student_id = intval($_SESSION['id']);
$review_id  = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($review_id <= 0) {
    header("Location: my-reviews.php?error=invalid_id");
    exit();
}

// verify ownership
$stmt = $conn->prepare("SELECT id FROM reviews WHERE id = ? AND student_id = ?");
if (!$stmt) {
    // Prepare failed
    header("Location: my-reviews.php?error=server_error");
    exit();
}
$stmt->bind_param("ii", $review_id, $student_id);
$stmt->execute();
$res = $stmt->get_result();
if ($res->num_rows === 0) {
    header("Location: my-reviews.php?error=not_allowed");
    exit();
}

// delete
$del = $conn->prepare("DELETE FROM reviews WHERE id = ?");
if (!$del) {
    header("Location: my-reviews.php?error=server_error");
    exit();
}
$del->bind_param("i", $review_id);
if ($del->execute()) {
    header("Location: my-reviews.php?message=deleted");
    exit();
} else {
    header("Location: my-reviews.php?error=delete_failed");
    exit();
}

$conn->close();
ob_end_flush();
?>