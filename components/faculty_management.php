<?php
session_start();
include "../DBconnect.php";

// Ensure only admins can access
if (!isset($_SESSION['username']) || !isset($_SESSION['id']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit();
}

// Handle faculty actions
if (isset($_GET['action']) && isset($_GET['id'])) {
    $faculty_id = intval($_GET['id']);
    $action = $_GET['action'];
    
    if ($action === 'delete') {
        $stmt = $conn->prepare("DELETE FROM faculty WHERE id = ?");
        $stmt->bind_param("i", $faculty_id);
        $stmt->execute();
        
        // Log the action
        $admin_id = $_SESSION['id'];
        $action_text = "Deleted faculty #$faculty_id";
        $log_stmt = $conn->prepare("INSERT INTO admin_logs (admin_id, action, target_type, target_id) VALUES (?, ?, 'faculty', ?)");
        $log_stmt->bind_param("isi", $admin_id, $action_text, $faculty_id);
        $log_stmt->execute();
        
        $_SESSION['message'] = "Faculty member deleted successfully";
        header("Location: faculty_management.php");
        exit();
    }
}

// Handle form submission for adding/editing faculty
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $designation = trim($_POST['designation']);
    $department_id = intval($_POST['department_id']);
    $bio = trim($_POST['bio']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    
    if (isset($_POST['faculty_id'])) {
        // Edit existing faculty
        $faculty_id = intval($_POST['faculty_id']);
        $stmt = $conn->prepare("UPDATE faculty SET name = ?, designation = ?, department_id = ?, bio = ?, email = ?, phone = ? WHERE id = ?");
        $stmt->bind_param("ssisssi", $name, $designation, $department_id, $bio, $email, $phone, $faculty_id);
        $stmt->execute();
        
        // Log the action
        $admin_id = $_SESSION['id'];
        $action_text = "Updated faculty: $name";
        $log_stmt = $conn->prepare("INSERT INTO admin_logs (admin_id, action, target_type, target_id) VALUES (?, ?, 'faculty', ?)");
        $log_stmt->bind_param("isi", $admin_id, $action_text, $faculty_id);
        $log_stmt->execute();
        
        $_SESSION['message'] = "Faculty member updated successfully";
    } else {
        // Add new faculty
        $stmt = $conn->prepare("INSERT INTO faculty (name, designation, department_id, bio, email, phone, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
        $stmt->bind_param("ssisss", $name, $designation, $department_id, $bio, $email, $phone);
        $stmt->execute();
        
        // Log the action
        $admin_id = $_SESSION['id'];
        $action_text = "Added new faculty: $name";
        $faculty_id = $stmt->insert_id;
        $log_stmt = $conn->prepare("INSERT INTO admin_logs (admin_id, action, target_type, target_id) VALUES (?, ?, 'faculty', ?)");
        $log_stmt->bind_param("isi", $admin_id, $action_text, $faculty_id);
        $log_stmt->execute();
        
        $_SESSION['message'] = "Faculty member added successfully";
    }
    
    header("Location: faculty_management.php");
    exit();
}

// Get all faculty members
$faculty_result = $conn->query("SELECT f.*, d.name as department_name FROM faculty f JOIN departments d ON f.department_id = d.id ORDER BY f.name");

// Get all departments for the form
$departments_result = $conn->query("SELECT * FROM departments ORDER BY name");

// Check if we're editing a faculty member
$editing_faculty = null;
if (isset($_GET['edit'])) {
    $faculty_id = intval($_GET['edit']);
    $stmt = $conn->prepare("SELECT * FROM faculty WHERE id = ?");
    $stmt->bind_param("i", $faculty_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $editing_faculty = $result->fetch_assoc();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Faculty Management - Faculty Hub</title>
    <link rel="stylesheet" href="../dashboardstyle.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Additional styles for faculty management */
        .alert {
            background: rgba(46, 213, 115, 0.2);
            color: #2ed573;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            border-left: 4px solid #2ed573;
        }
        
        .flex-container {
            display: flex;
            gap: 2rem;
            margin-bottom: 2rem;
        }
        
        .form-card, .table-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 16px;
            padding: 2rem;
        }
        
        .form-card {
            flex: 1;
            max-width: 500px;
        }
        
        .table-card {
            flex: 2;
        }
        
        .form-card h2, .table-card h2 {
            color: white;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.7rem;
        }
        
        .form-group {
            margin-bottom: 1.2rem;
        }
        
        .form-group label {
            display: block;
            color: rgba(255, 255, 255, 0.9);
            margin-bottom: 0.5rem;
            font-weight: 600;
        }
        
        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 0.8rem 1rem;
            border-radius: 8px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            background: rgba(255, 255, 255, 0.1);
            color: white;
            font-family: inherit;
        }
        
        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #667eea;
        }
        
        .form-actions {
            display: flex;
            gap: 1rem;
            margin-top: 1.5rem;
        }
        
        .btn-danger {
            background: linear-gradient(45deg, #ff4757, #ff6b81);
            color: white;
        }
        
        .btn-danger:hover {
            background: linear-gradient(45deg, #ff2e43, #ff4d68);
        }
        
        .action-buttons {
            display: flex;
            gap: 0.5rem;
        }
        
        @media (max-width: 1024px) {
            .flex-container {
                flex-direction: column;
            }
            
            .form-card {
                max-width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="animated-bg">
        <div class="gradient-orb orb-1"></div>
        <div class="gradient-orb orb-2"></div>
        <div class="gradient-orb orb-3"></div>
    </div>
    
    <nav class="navbar">
        <div class="nav-container">
            <div class="nav-brand">
                <div class="brand-icon">
                    <i class="fas fa-graduation-cap"></i>
                </div>
                <div class="brand-text">FacultyHub Admin</div>
            </div>
            <div class="nav-menu">
                <a href="../dashboard_admin.php" class="nav-btn">
                    <i class="fas fa-home"></i>
                    <span>Dashboard</span>
                </a>
                <a href="faculty_management.php" class="nav-btn active">
                    <i class="fas fa-users"></i>
                    <span>Faculty</span>
                </a>
                <a href="reviews.php" class="nav-btn">
                    <i class="fas fa-star"></i>
                    <span>Reviews</span>
                </a>
                <a href="users.php" class="nav-btn">
                    <i class="fas fa-user-cog"></i>
                    <span>Users</span>
                </a>
                <a href="reports.php" class="nav-btn">
                    <i class="fas fa-chart-bar"></i>
                    <span>Activity</span>
                </a>
                <a href="../logout.php" class="nav-btn">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Logout</span>
                </a>
            </div>
        </div>
    </nav>

    <div class="dashboard-container">
        <div class="dashboard-header">
            <div class="welcome-section">
                <h1><i class="fas fa-users"></i> Faculty Management</h1>
                <p>Add, edit, or remove faculty members</p>
            </div>
            <div class="quick-stats">
                <div class="stat-item">
                    <div class="stat-icon">
                        <i class="fas fa-chalkboard-teacher"></i>
                    </div>
                    <div class="stat-content">
                        <span class="stat-number">
                            <?php 
                            $count_result = $conn->query("SELECT COUNT(*) as count FROM faculty");
                            echo $count_result->fetch_assoc()['count'];
                            ?>
                        </span>
                        <span class="stat-label">Total Faculty</span>
                    </div>
                </div>
            </div>
        </div>
        
        <?php if (isset($_SESSION['message'])): ?>
            <div class="alert">
                <i class="fas fa-check-circle"></i> <?php echo $_SESSION['message']; unset($_SESSION['message']); ?>
            </div>
        <?php endif; ?>
        
        <div class="flex-container">
            <div class="form-card">
                <h2><i class="fas <?php echo $editing_faculty ? 'fa-user-edit' : 'fa-user-plus'; ?>"></i> <?php echo $editing_faculty ? 'Edit Faculty Member' : 'Add New Faculty Member'; ?></h2>
                <form method="POST">
                    <?php if ($editing_faculty): ?>
                        <input type="hidden" name="faculty_id" value="<?php echo $editing_faculty['id']; ?>">
                    <?php endif; ?>
                    
                    <div class="form-group">
                        <label for="name">Full Name</label>
                        <input type="text" id="name" name="name" value="<?php echo $editing_faculty ? htmlspecialchars($editing_faculty['name']) : ''; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="designation">Designation</label>
                        <input type="text" id="designation" name="designation" value="<?php echo $editing_faculty ? htmlspecialchars($editing_faculty['designation']) : ''; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="department_id">Department</label>
                        <select id="department_id" name="department_id" required>
                            <option value="">Select Department</option>
                            <?php 
                            // Reset pointer and loop through departments
                            $departments_result->data_seek(0);
                            while ($dept = $departments_result->fetch_assoc()): 
                            ?>
                                <option value="<?php echo $dept['id']; ?>" <?php echo ($editing_faculty && $editing_faculty['department_id'] == $dept['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($dept['name']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" value="<?php echo $editing_faculty ? htmlspecialchars($editing_faculty['email']) : ''; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="phone">Phone</label>
                        <input type="text" id="phone" name="phone" value="<?php echo $editing_faculty ? htmlspecialchars($editing_faculty['phone']) : ''; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="bio">Bio/Description</label>
                        <textarea id="bio" name="bio" rows="4"><?php echo $editing_faculty ? htmlspecialchars($editing_faculty['bio']) : ''; ?></textarea>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas <?php echo $editing_faculty ? 'fa-save' : 'fa-plus'; ?>"></i>
                            <?php echo $editing_faculty ? 'Update Faculty' : 'Add Faculty'; ?>
                        </button>
                        
                        <?php if ($editing_faculty): ?>
                            <a href="faculty_management.php" class="btn btn-secondary">Cancel</a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
            
            <div class="table-card">
                <h2><i class="fas fa-list"></i> Faculty Members</h2>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Designation</th>
                                <th>Department</th>
                                <th>Email</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            // Reset pointer and loop through faculty
                            $faculty_result->data_seek(0);
                            while ($faculty = $faculty_result->fetch_assoc()): 
                            ?>
                            <tr>
                                <td><?php echo htmlspecialchars($faculty['name']); ?></td>
                                <td><?php echo htmlspecialchars($faculty['designation']); ?></td>
                                <td><?php echo htmlspecialchars($faculty['department_name']); ?></td>
                                <td><?php echo htmlspecialchars($faculty['email']); ?></td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="faculty_management.php?edit=<?php echo $faculty['id']; ?>" class="btn btn-sm btn-primary">
                                            <i class="fas fa-edit"></i> Edit
                                        </a>
                                        <a href="faculty_management.php?action=delete&id=<?php echo $faculty['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this faculty member?')">
                                            <i class="fas fa-trash"></i> Delete
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
<?php $conn->close(); ?>