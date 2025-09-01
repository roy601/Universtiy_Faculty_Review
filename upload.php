<?php
session_start();
include "DBconnect.php";

// Check if user is logged in
if (!isset($_SESSION['username']) || !isset($_SESSION['id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit();
}

// Check if file was uploaded
if (!isset($_FILES['profile_picture']) || $_FILES['profile_picture']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success' => false, 'message' => 'No file uploaded or upload error']);
    exit();
}

$user_id = $_SESSION['id'];
$file = $_FILES['profile_picture'];

// Validate file
$allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
$max_size = 5 * 1024 * 1024; // 5MB

if (!in_array($file['type'], $allowed_types)) {
    echo json_encode(['success' => false, 'message' => 'Only JPG, PNG, GIF, and WebP files are allowed']);
    exit();
}

if ($file['size'] > $max_size) {
    echo json_encode(['success' => false, 'message' => 'File size must be less than 5MB']);
    exit();
}

// Generate unique filename
$extension = pathinfo($file['name'], PATHINFO_EXTENSION);
$filename = 'user_' . $user_id . '_' . time() . '.' . $extension;
$upload_path = 'img/uploads/' . $filename;

// Create uploads directory if it doesn't exist
if (!file_exists('img/uploads')) {
    mkdir('img/uploads', 0777, true);
}

// Move uploaded file
if (move_uploaded_file($file['tmp_name'], $upload_path)) {
    // Update database
    $stmt = $conn->prepare("UPDATE users SET profile_picture = ? WHERE id = ?");
    $stmt->bind_param("si", $filename, $user_id);
    
    if ($stmt->execute()) {
        // Also store in images table for history
        $stmt2 = $conn->prepare("INSERT INTO user_images (user_id, filename, filepath) VALUES (?, ?, ?)");
        $stmt2->bind_param("iss", $user_id, $filename, $upload_path);
        $stmt2->execute();
        
        echo json_encode(['success' => true, 'filename' => $filename, 'message' => 'Profile picture updated successfully']);
    } else {
        // Delete the file if DB update failed
        unlink($upload_path);
        echo json_encode(['success' => false, 'message' => 'Database update failed']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'File upload failed']);
}

$conn->close();
?>