<?php 
session_start();
if (isset($_SESSION['username']) && isset($_SESSION['id'])) {
    if ($_SESSION['role'] === 'admin') {
        header("Location: dashboard_admin.php");
    } else {
        header("Location: dashboard_user.php");
    }
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Faculty Hub - Sign Up</title>
  <link rel="stylesheet" href="styles.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    .form-toggle {
      text-align: center;
      margin-top: 1.5rem;
      padding-top: 1.5rem;
      border-top: 1px solid rgba(255, 255, 255, 0.1);
    }
    
    .form-toggle a {
      color: #667eea;
      text-decoration: none;
      font-weight: 600;
    }
    
    .form-toggle a:hover {
      text-decoration: underline;
    }
  </style>
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
      
      <!-- SIGNUP FORM -->
      <p class="hero-subtitle">Create a new account</p>
      
      <form action="php/register.php" method="post">
        <?php if (isset($_GET['error'])) { ?>
        <div class="alert error" role="alert">
          <i class="fas fa-exclamation-circle"></i>
          <?=htmlspecialchars($_GET['error'])?>
        </div>
        <?php } ?>
        
        <?php if (isset($_GET['success'])) { ?>
        <div class="alert success" role="alert">
          <i class="fas fa-check-circle"></i>
          <?=htmlspecialchars($_GET['success'])?>
        </div>
        <?php } ?>
        
        <div class="form-group">
          <label for="name">Full Name</label>
          <div class="input-with-icon">
            <i class="fas fa-user"></i>
            <input type="text" 
                   class="form-control" 
                   name="name" 
                   id="name"
                   placeholder="Enter your full name"
                   value="<?= isset($_GET['name']) ? htmlspecialchars($_GET['name']) : '' ?>"
                   required>
          </div>
        </div>
        
        <div class="form-group">
          <label for="username">Username</label>
          <div class="input-with-icon">
            <i class="fas fa-user"></i>
            <input type="text" 
                   class="form-control" 
                   name="username" 
                   id="username"
                   placeholder="Choose a username"
                   value="<?= isset($_GET['username']) ? htmlspecialchars($_GET['username']) : '' ?>"
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
                   placeholder="Create a password (min. 4 characters)"
                   required>
          </div>
        </div>
        
        <div class="form-group">
          <label for="confirm_password">Confirm Password</label>
          <div class="input-with-icon">
            <i class="fas fa-lock"></i>
            <input type="password" 
                   name="confirm_password" 
                   class="form-control" 
                   id="confirm_password"
                   placeholder="Confirm your password"
                   required>
          </div>
        </div>
        
        <button type="submit" class="btn-primary">
          <i class="fas fa-user-plus"></i> SIGN UP
        </button>
        
        <div class="form-toggle">
          <p>Already have an account? <a href="index.php">Sign in here</a></p>
        </div>
      </form>
    </div>
  </div>
</body>
</html>