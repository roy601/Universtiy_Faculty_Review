<?php
session_start();
include "../DBconnect.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validate inputs
    if (empty($name) || empty($username) || empty($password) || empty($confirm_password)) {
        header("Location: ../signup.php?error=All fields are required&name=" . urlencode($name) . "&username=" . urlencode($username));
        exit();
    }
    
    if (strlen($password) < 4) {
        header("Location: ../signup.php?error=Password must be at least 4 characters long&name=" . urlencode($name) . "&username=" . urlencode($username));
        exit();
    }
    
    if ($password !== $confirm_password) {
        header("Location: ../signup.php?error=Passwords do not match&name=" . urlencode($name) . "&username=" . urlencode($username));
        exit();
    }
    
    // Check if username already exists
    $check_stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
    $check_stmt->bind_param("s", $username);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if ($check_result->num_rows > 0) {
        header("Location: ../signup.php?error=Username already exists&name=" . urlencode($name) . "&username=" . urlencode($username));
        exit();
    }
    
    // Store password in plain text (NOT RECOMMENDED FOR PRODUCTION)
    $role = 'user';
    
    $insert_stmt = $conn->prepare("INSERT INTO users (name, username, password, role) VALUES (?, ?, ?, ?)");
    $insert_stmt->bind_param("ssss", $name, $username, $password, $role);
    
    if ($insert_stmt->execute()) {
        header("Location: ../index.php?success=Account created successfully. Please sign in.");
        exit();
    } else {
        header("Location: ../signup.php?error=Registration failed. Please try again.&name=" . urlencode($name) . "&username=" . urlencode($username));
        exit();
    }
} else {
    header("Location: ../signup.php");
    exit();
}
?>