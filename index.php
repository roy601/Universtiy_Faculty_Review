<?php 
session_start();
if (isset($_SESSION['username']) && isset($_SESSION['id'])) {
    header("Location: dashboard.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Faculty Hub - Login</title>
  <link rel="stylesheet" href="styles.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
  <div class="animated-bg">
      <div class="gradient-orb orb-1"></div>
      <div class="gradient-orb orb-2"></div>
      <div class="gradient-orb orb-3"></div>
  </div>

  <div class="container">
    <div class="login-form">
      <div class="brand-header">
        <i class="fas fa-graduation-cap"></i>
        <h1>Faculty Hub</h1>
      </div>
      <p class="hero-subtitle">Login to continue</p>
      
      <form action="php/check-login.php" method="post">
        <?php if (isset($_GET['error'])) { ?>
        <div class="alert error" role="alert">
          <i class="fas fa-exclamation-circle"></i>
          <?=htmlspecialchars($_GET['error'])?>
        </div>
        <?php } ?>
        
        <div class="form-group">
          <label for="username">Username</label>
          <div class="input-with-icon">
            <i class="fas fa-user"></i>
            <input type="text" 
                   class="form-control" 
                   name="username" 
                   id="username"
                   placeholder="Enter your username"
                   required>
          </div>
        </div>
        
        <div class="form-group">
          <label for="password">Password</label>
          <div class="input-with-icon">
            <i class="fas fa-lock"></i>
            <input type="password" 
                   name="password" 
                   class="form-control" 
                   id="password"
                   placeholder="Enter your password"
                   required>
          </div>
        </div>
        
        <div class="form-group">
          <label class="form-label">Select User Type:</label>
          <div class="input-with-icon">
            <i class="fas fa-user-tag"></i>
            <select class="form-select"
                    name="role" 
                    aria-label="User role selection">
              <option selected value="user">User</option>
              <option value="admin">Admin</option>
            </select>
          </div>
        </div>
        
        <button type="submit" class="btn-primary">
          <i class="fas fa-sign-in-alt"></i> LOGIN
        </button>
      </form>
      
      <div class="login-footer">
        <p>Don't have an account? <a href="#">Contact administrator</a></p>
      </div>
    </div>
  </div>
</body>
</html>