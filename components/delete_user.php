<?php
session_start();
include "../DBconnect.php";

// Ensure only admins can access
if (!isset($_SESSION['username']) || !isset($_SESSION['id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

// Check if user ID is provided
if (!isset($_GET['id'])) {
    header("Location: users.php");
    exit();
}

$user_id = $_GET['id'];

// Prevent admin from deleting themselves
if ($user_id == $_SESSION['id']) {
    header("Location: users.php?error=Cannot delete your own account");
    exit();
}

// Check if user exists
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    // User not found
    header("Location: users.php?error=User not found");
    exit();
}

// Delete user
$delete_stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
$delete_stmt->bind_param("i", $user_id);

if ($delete_stmt->execute()) {
    header("Location: users.php?success=User deleted successfully");
} else {
    header("Location: users.php?error=Error deleting user");
}

$conn->close();
exit();
?>