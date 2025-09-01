<?php
session_start();
include "../DBconnect.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password']; // Don't trim passwords!
    $role = $_POST['role'];
    
    if (empty($username) || empty($password) || empty($role)) {
        header("Location: ../index.php?error=All fields are required");
        exit();
    }
    
    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ? AND role = ?");
    $stmt->bind_param("ss", $username, $role);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        
        // Compare plain text passwords (INSECURE)
        if ($password === $user['password']) {
            $_SESSION['id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['name'] = $user['name'];
            $_SESSION['role'] = $user['role'];
            
            // Redirect based on role
            if ($user['role'] === 'admin') {
                header("Location: ../dashboard_admin.php");
            } else {
                header("Location: ../dashboard_user.php");
            }
            exit();
        } else {
            header("Location: ../index.php?error=Incorrect password");
            exit();
        }
    } else {
        header("Location: ../index.php?error=User not found or incorrect role selected");
        exit();
    }
} else {
    header("Location: ../index.php");
    exit();
}
?>