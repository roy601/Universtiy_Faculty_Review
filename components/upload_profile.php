<?php
session_start();
include "../DBconnect.php";

// Check if user is logged in
if (!isset($_SESSION['username']) || !isset($_SESSION['id'])) {
    header("Location: ../index.php");
    exit();
}

$user_id = $_SESSION['id'];
$current_picture = null;

// Get current profile picture
$stmt = $conn->prepare("SELECT profile_picture FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
if ($row = $result->fetch_assoc()) {
    $current_picture = $row['profile_picture'];
}
?>

<div class="upload-section">
    <h3><i class="fas fa-camera"></i> Profile Picture</h3>
    
    <div class="current-picture">
        <?php if ($current_picture): ?>
            <img src="../img/uploads/<?php echo htmlspecialchars($current_picture); ?>" 
                 alt="Current Profile Picture" class="profile-preview">
        <?php else: ?>
            <div class="no-picture">
                <i class="fas fa-user-circle"></i>
                <p>No profile picture set</p>
            </div>
        <?php endif; ?>
    </div>
    
    <form id="uploadForm" enctype="multipart/form-data">
        <div class="form-group">
            <label for="profile_picture">Select new profile picture:</label>
            <input type="file" id="profile_picture" name="profile_picture" 
                   accept="image/jpeg, image/png, image/gif, image/webp" required>
            <small>Max file size: 5MB. Allowed formats: JPG, PNG, GIF, WebP</small>
        </div>
        
        <button type="submit" class="btn btn-primary">
            <i class="fas fa-upload"></i> Upload Picture
        </button>
    </form>
    
    <div id="uploadMessage" class="message"></div>
</div>

<script>
document.getElementById('uploadForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const messageDiv = document.getElementById('uploadMessage');
    
    fetch('../upload.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            messageDiv.innerHTML = `<div class="alert alert-success">${data.message}</div>`;
            // Reload the page after a short delay to show the new picture
            setTimeout(() => location.reload(), 1500);
        } else {
            messageDiv.innerHTML = `<div class="alert alert-danger">${data.message}</div>`;
        }
    })
    .catch(error => {
        messageDiv.innerHTML = `<div class="alert alert-danger">Upload failed: ${error}</div>`;
    });
});
</script>