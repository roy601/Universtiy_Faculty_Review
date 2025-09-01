<?php
// setup.php - Run this once to set up the database and admin account
include "DBconnect.php";

echo "Setting up Faculty Hub database...<br>";

// Create users table
$sql = "CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('user', 'admin') NOT NULL DEFAULT 'user',
    name VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if ($conn->query($sql) === TRUE) {
    echo "Users table created successfully.<br>";
} else {
    echo "Error creating users table: " . $conn->error . "<br>";
}

// Create default admin account with properly hashed password
$admin_username = 'admin';
$admin_password = 'admin123'; // Change this to your desired admin password
$admin_name = 'Administrator';
$admin_role = 'admin';

// Hash the password
$hashed_password = password_hash($admin_password, PASSWORD_DEFAULT);

// Check if admin already exists
$check_sql = "SELECT id FROM users WHERE username = '$admin_username'";
$result = $conn->query($check_sql);

if ($result->num_rows > 0) {
    echo "Admin account already exists. Updating password...<br>";
    $update_sql = "UPDATE users SET password = '$hashed_password' WHERE username = '$admin_username'";
    if ($conn->query($update_sql) {
        echo "Admin password updated successfully.<br>";
    } else {
        echo "Error updating admin password: " . $conn->error . "<br>";
    }
} else {
    // Insert new admin account
    $insert_sql = "INSERT INTO users (username, password, role, name) 
                   VALUES ('$admin_username', '$hashed_password', '$admin_role', '$admin_name')";
    
    if ($conn->query($insert_sql)) {
        echo "Admin account created successfully.<br>";
        echo "Username: admin<br>";
        echo "Password: admin123<br>";
    } else {
        echo "Error creating admin account: " . $conn->error . "<br>";
    }
}

echo "Setup complete. <a href='index.php'>Go to login page</a>";

$conn->close();
?>