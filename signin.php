<?php
// session_start();
// require_once('DBconnect.php');

// if (isset($_POST['frame']) && isset($_POST['pass'])) {
//     $u = $_POST['frame'];
//     $p = $_POST['pass'];

//     $sql = "SELECT * FROM users WHERE username=? AND password=?";
//     $stmt = $conn->prepare($sql);
//     $stmt->bind_param("ss", $u, $p);
//     $stmt->execute();
//     $result = $stmt->get_result();

//     if ($result->num_rows === 1) {
//         $user = $result->fetch_assoc();
//         $_SESSION['username'] = $user['username'];
//         $_SESSION['role'] = $user['role'];
//         header("Location: dashboard.php");
//         exit;
//     } else {
//         header("Location: login.php?error=Invalid+username+or+password");
//         exit;
//     }
// }
?>
