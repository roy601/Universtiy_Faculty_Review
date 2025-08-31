<?php 
session_start();
include "DBconnect.php";

if (!isset($_SESSION['username']) || !isset($_SESSION['id'])) {
    header("Location: index.php");
    exit();
}

// Redirect based on role
if ($_SESSION['role'] == 'admin') {
    header("Location: dashboard_admin.php");
} else {
    header("Location: dashboard_user.php");
}
exit();
?>