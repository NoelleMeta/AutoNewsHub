<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header('Location: home.php');
    exit();
}

include 'includes/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    
    // Validate input
    if (empty($title) || empty($description)) {
        $error = "Please fill in all required fields.";
    } elseif (strlen($title) < 5) {
        $error = "Title must be at least 5 characters long.";
    } elseif (strlen($description) < 20) {
        $error = "Description must be at least 20 characters long.";
    } else {
        $upload_success = true;
        $uploaded_files = [];
        
        // Handle file uploads
        $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
        $max_size = 5 * 1024 * 1024; // 5MB
        
        for ($i = 1; $i <= 3; $i++) {
            $file_key = "image$i";
            if (isset($_FILES[$file_key]) && $_FILES[$file_key]['error'] == 0) {
                $file = $_FILES[$file_key];
                
                // Validate file type and size
                if (!in_array($file['type'], $allowed_types)) {
                    $error = "Image $i must be a valid image file (JPEG, PNG, GIF).";
                    $upload_success = false;
                    break;
                }
                
                if ($file['size'] > $max_size) {
                    $error = "Image $i must be less than 5MB.";
                    $upload_success = false;
                    break;
                }
                
                // Generate unique filename
                $file_extension = pathinfo($file['name'], PATHINFO_EXTENSION);
                $filename = uniqid() . '_' . time() . '.' . $file_extension;
                
                if (move_uploaded_file($file['tmp_name'], "uploads/$filename")) {
                    $uploaded_files[$file_key] = $filename;
                } else {
                    $error = "Failed to upload image $i.";
                    $upload_success = false;
                    break;
                }
            }
        }
        
        if ($upload_success && !empty($uploaded_files)) {
            // Insert into database
            $stmt = $conn->prepare("INSERT INTO news (title, description, image1, image2, image3) VALUES (?, ?, ?, ?, ?)");
            $image1 = $uploaded_files['image1'] ?? '';
            $image2 = $uploaded_files['image2'] ?? '';
            $image3 = $uploaded_files['image3'] ?? '';
            $stmt->bind_param("sssss", $title, $description, $image1, $image2, $image3);
            
            if ($stmt->execute()) {
                $success = "News article added successfully!";
                // Clear form data
                $_POST = array();
            } else {
                $error = "Error adding news article: " . $conn->error;
            }
            $stmt->close();
        }
    }
}

include 'includes/header.php';
?>

<div class="page-header">
    <h2>Manage News Articles</h2>
    <p>Add new automotive news articles to keep your readers informed and engaged</p>
</div>

<div class="admin-container">
    <form method="post" enctype="multipart/form-data" class="admin-form">
        <?php if (isset($error)): ?>
            <div class="alert alert-danger">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($success)): ?>
            <div class="alert alert-success">
                <?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>
        
        <div class="form-group">
            <label for="title">Article Title</label>
            <input type="text" id="title" name="title" 
                   value="<?php echo isset($_POST['title']) ? htmlspecialchars($_POST['title']) : ''; ?>"
                   placeholder="Enter a compelling title for your article" required>
        </div>
        
        <div class="form-group">
            <label for="description">Article Content</label>
            <textarea id="description" name="description" rows="8" 
                      placeholder="Write your article content here..." required><?php echo isset($_POST['description']) ? htmlspecialchars($_POST['description']) : ''; ?></textarea>
            <div class="char-count">0 characters</div>
        </div>
        
        <div class="form-group">
            <label for="image1">Main Image (Required)</label>
            <input type="file" id="image1" name="image1" accept="image/*" required>
            <small class="file-help">Upload a high-quality image (JPEG, PNG, GIF, max 5MB)</small>
        </div>
        
        <div class="form-group">
            <label for="image2">Additional Image 2 (Optional)</label>
            <input type="file" id="image2" name="image2" accept="image/*">
            <small class="file-help">Upload a high-quality image (JPEG, PNG, GIF, max 5MB)</small>
        </div>
        
        <div class="form-group">
            <label for="image3">Additional Image 3 (Optional)</label>
            <input type="file" id="image3" name="image3" accept="image/*">
            <small class="file-help">Upload a high-quality image (JPEG, PNG, GIF, max 5MB)</small>
        </div>
        
        <div class="form-actions">
            <input type="submit" value="Publish Article" class="button admin-button">
            <a href="home.php" class="button secondary-button">Back to Home</a>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const descriptionTextarea = document.getElementById('description');
    const charCount = document.querySelector('.char-count');
    
    if (descriptionTextarea && charCount) {
        descriptionTextarea.addEventListener('input', function() {
            const count = this.value.length;
            charCount.textContent = count + ' characters';
            
            if (count < 20) {
                charCount.style.color = '#ef4444';
            } else if (count < 100) {
                charCount.style.color = '#f59e0b';
            } else {
                charCount.style.color = 'var(--text-light)';
            }
        });
    }
    
    // Auto-hide alerts after 5 seconds
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(function(alert) {
        setTimeout(function() {
            alert.style.opacity = '0';
            setTimeout(function() {
                alert.remove();
            }, 300);
        }, 5000);
    });
});
</script>

<?php include 'includes/footer.php'; ?>