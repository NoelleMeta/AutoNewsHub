<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header('Location: home.php');
    exit();
}

include 'includes/db.php';

// Validate and sanitize the ID parameter
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: home.php');
    exit();
}

$id = intval($_GET['id']);

// Handle delete request
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'delete') {
    // Get current images to delete from server
    $stmt = $conn->prepare("SELECT image1, image2, image3 FROM news WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        
        // Delete images from server
        if (!empty($row['image1']) && file_exists("uploads/" . $row['image1'])) {
            unlink("uploads/" . $row['image1']);
        }
        if (!empty($row['image2']) && file_exists("uploads/" . $row['image2'])) {
            unlink("uploads/" . $row['image2']);
        }
        if (!empty($row['image3']) && file_exists("uploads/" . $row['image3'])) {
            unlink("uploads/" . $row['image3']);
        }
        
        // Delete from database
        $stmt = $conn->prepare("DELETE FROM news WHERE id = ?");
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            $success = "Article deleted successfully!";
            header("Location: home.php");
            exit();
        } else {
            $error = "Error deleting article: " . $conn->error;
        }
        $stmt->close();
    }
}

// Fetch article data
$stmt = $conn->prepare("SELECT * FROM news WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    header('Location: home.php');
    exit();
}

$row = $result->fetch_assoc();
$stmt->close();

// Handle edit request
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'edit') {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    
    // Define target image dimensions
    $target_width = 800; // Max width
    $target_height = 600; // Max height

    // Function to resize image
    function resizeImage($file_path, $target_width, $target_height, $quality = 90) {
        list($width, $height, $type) = getimagesize($file_path);

        if ($width <= $target_width && $height <= $target_height) {
            return true; // No resizing needed if image is already smaller than or equal to target
        }

        $new_width = $width;
        $new_height = $height;

        // Calculate new dimensions while maintaining aspect ratio
        if ($width > $target_width) {
            $new_width = $target_width;
            $new_height = intval(($target_width / $width) * $height);
        }

        if ($new_height > $target_height) {
            $new_height = $target_height;
            $new_width = intval(($target_height / $height) * $width);
        }
        $new_width = intval($new_width);
        $new_height = intval($new_height);
        
        // Create new image from file
        switch ($type) {
            case IMAGETYPE_JPEG:
                $source_image = imagecreatefromjpeg($file_path);
                break;
            case IMAGETYPE_PNG:
                $source_image = imagecreatefrompng($file_path);
                imagealphablending($source_image, true);
                imagesavealpha($source_image, true);
                break;
            case IMAGETYPE_GIF:
                $source_image = imagecreatefromgif($file_path);
                break;
            default:
                return false; // Unsupported image type
        }

        $resized_image = imagecreatetruecolor($new_width, $new_height);
        
        // Preserve transparency for PNG and GIF
        if ($type == IMAGETYPE_PNG || $type == IMAGETYPE_GIF) {
            imagealphablending($resized_image, false);
            imagesavealpha($resized_image, true);
            $transparent = imagecolorallocatealpha($resized_image, 255, 255, 255, 127);
            imagefilledrectangle($resized_image, 0, 0, $new_width, $new_height, $transparent);
        }

        imagecopyresampled($resized_image, $source_image, 0, 0, 0, 0, $new_width, $new_height, intval($width), intval($height));

        // Save the resized image
        switch ($type) {
            case IMAGETYPE_JPEG:
                imagejpeg($resized_image, $file_path, $quality);
                break;
            case IMAGETYPE_PNG:
                imagepng($resized_image, $file_path, 9); // PNG quality is 0-9
                break;
            case IMAGETYPE_GIF:
                imagegif($resized_image, $file_path);
                break;
        }

        imagedestroy($source_image);
        imagedestroy($resized_image);

        return true;
    }

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
        $max_size = 5 * 1024 * 1024; // 5MB
        for ($i = 1; $i <= 3; $i++) {
            $file_key = "image$i";
            if (isset($_FILES[$file_key]) && $_FILES[$file_key]['error'] == 0) {
                $file = $_FILES[$file_key];
                // Pastikan file adalah gambar
                $image_info = @getimagesize($file['tmp_name']);
                if ($image_info === false) {
                    $error = "File $i bukan gambar yang valid.";
                    $upload_success = false;
                    break;
                }
                if ($file['size'] > $max_size) {
                    $error = "Image $i must be less than 5MB.";
                    $upload_success = false;
                    break;
                }
                // Ekstensi file asli
                $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                // Tentukan ekstensi output (jpg/png)
                $output_ext = in_array($image_info[2], [IMAGETYPE_PNG, IMAGETYPE_GIF]) ? 'png' : 'jpg';
                $filename = uniqid() . '_' . time() . '.' . $output_ext;
                $destination_path = "uploads/$filename";
                // Jika format didukung GD (jpg, png, gif, webp), proses biasa
                if (in_array($image_info[2], [IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_GIF, IMAGETYPE_WEBP])) {
                    if (move_uploaded_file($file['tmp_name'], $destination_path)) {
                        // Resize dan konversi jika perlu
                        if (!resizeImage($destination_path, $target_width, $target_height)) {
                            $error = "Failed to resize image $i.";
                            unlink($destination_path);
                            $upload_success = false;
                            break;
                        }
                        $uploaded_files[$file_key] = $filename;
                        // Hapus gambar lama jika ada
                        $old_image = $row[$file_key];
                        if (!empty($old_image) && file_exists("uploads/" . $old_image)) {
                            unlink("uploads/" . $old_image);
                        }
                    } else {
                        $error = "Failed to upload image $i.";
                        $upload_success = false;
                        break;
                    }
                } else {
                    // Format tidak didukung GD, konversi manual (gunakan imagecreatefromstring)
                    $img_data = file_get_contents($file['tmp_name']);
                    $img = @imagecreatefromstring($img_data);
                    if ($img === false) {
                        $error = "File $i tidak bisa dikonversi ke gambar.";
                        $upload_success = false;
                        break;
                    }
                    // Simpan sebagai jpg
                    if (imagejpeg($img, $destination_path, 90)) {
                        imagedestroy($img);
                        // Resize jika perlu
                        if (!resizeImage($destination_path, $target_width, $target_height)) {
                            $error = "Failed to resize image $i.";
                            unlink($destination_path);
                            $upload_success = false;
                            break;
                        }
                        $uploaded_files[$file_key] = $filename;
                        $old_image = $row[$file_key];
                        if (!empty($old_image) && file_exists("uploads/" . $old_image)) {
                            unlink("uploads/" . $old_image);
                        }
                    } else {
                        imagedestroy($img);
                        $error = "Gagal menyimpan gambar hasil konversi $i.";
                        $upload_success = false;
                        break;
                    }
                }
            }
        }
        
        if ($upload_success) {
            // Update database
            $image1 = $uploaded_files['image1'] ?? $row['image1'];
            $image2 = $uploaded_files['image2'] ?? $row['image2'];
            $image3 = $uploaded_files['image3'] ?? $row['image3'];
            
            $stmt = $conn->prepare("UPDATE news SET title = ?, description = ?, image1 = ?, image2 = ?, image3 = ? WHERE id = ?");
            $stmt->bind_param("sssssi", $title, $description, $image1, $image2, $image3, $id);
            
            if ($stmt->execute()) {
                $success = "Article updated successfully!";
                // Refresh row data
                $row['title'] = $title;
                $row['description'] = $description;
                $row['image1'] = $image1;
                $row['image2'] = $image2;
                $row['image3'] = $image3;
            } else {
                $error = "Error updating article: " . $conn->error;
            }
            $stmt->close();
        }
    }
}

include 'includes/header.php';
?>

<div class="page-header">
    <h2>Edit Berita Otomotif</h2>
    <p>Perbarui berita dengan info dan gambar terbaru.</p>
</div>

<div class="admin-container" style="max-width:900px;margin:2rem auto;">
    <div class="article-management" style="display:flex;flex-wrap:wrap;gap:2.5rem;">
        <!-- Article Preview -->
        <div class="article-preview" style="flex:1 1 320px;min-width:320px;background:#18191d;border-radius:var(--radius);border:2.5px solid var(--blue-metal);box-shadow:var(--shadow);padding:1.5rem;">
            <h3 style="color:var(--blue-metal);font-weight:800;">Preview</h3>
            <div class="preview-card" style="display:flex;gap:1.2rem;align-items:center;">
                <div class="preview-image">
                    <?php if (!empty($row['image1'])): ?>
                        <img src="uploads/<?php echo htmlspecialchars($row['image1']); ?>" alt="Article Image" style="width:110px;height:80px;object-fit:cover;border-radius:8px;border:2px solid var(--accent);">
                    <?php else: ?>
                        <div class="no-image" style="width:110px;height:80px;background:#23252b;border-radius:8px;display:flex;align-items:center;justify-content:center;color:var(--gray);">No Image</div>
                    <?php endif; ?>
                </div>
                <div class="preview-content">
                    <h4 style="color:var(--accent);margin-bottom:0.3rem;"><?php echo htmlspecialchars($row['title']); ?></h4>
                    <p style="color:var(--gray);font-size:0.98em;"><?php echo htmlspecialchars(substr($row['description'], 0, 100)); ?>...</p>
                    <div class="preview-meta" style="color:var(--blue-metal);font-size:0.95em;">
                        <span class="preview-date"><?php echo date('d M Y', strtotime($row['created_at'])); ?></span>
                        <span class="preview-id" style="margin-left:1em;">ID: <?php echo $row['id']; ?></span>
                    </div>
                </div>
            </div>
        </div>
        <!-- Edit Form -->
        <form method="post" enctype="multipart/form-data" class="admin-form" style="flex:2 1 400px;min-width:320px;background:#18191d;border-radius:var(--radius);border:2.5px solid var(--accent);box-shadow:var(--shadow);padding:2rem;">
            <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            <?php if (isset($success)): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>
            <div class="form-group" style="margin-bottom:1.2rem;">
                <label for="title">Judul Berita</label>
                <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($row['title']); ?>" placeholder="Judul menarik..." required style="width:100%;padding:0.7rem 1rem;background:#23252b;color:var(--white);border:1.5px solid var(--gray);border-radius:var(--radius);font-size:1.1rem;">
            </div>
            <div class="form-group" style="margin-bottom:1.2rem;">
                <label for="description">Isi Berita</label>
                <textarea id="description" name="description" rows="8" placeholder="Tulis isi berita di sini..." required style="width:100%;padding:0.7rem 1rem;background:#23252b;color:var(--white);border:1.5px solid var(--gray);border-radius:var(--radius);font-size:1.1rem;"><?php echo htmlspecialchars($row['description']); ?></textarea>
                <div class="char-count"><?php echo strlen($row['description']); ?> karakter</div>
            </div>
            <div class="images-section" style="margin-bottom:1.2rem;">
                <h4 style="color:var(--blue-metal);font-weight:700;">Gambar Berita</h4>
                <div class="image-upload-group" style="margin-bottom:1rem;">
                    <label for="image1">Gambar Utama (Wajib)</label>
                    <div class="current-image" style="margin-bottom:0.5rem;">
                        <?php if (!empty($row['image1'])): ?>
                            <img src="uploads/<?php echo htmlspecialchars($row['image1']); ?>" alt="Current Image 1" style="width:90px;height:60px;object-fit:cover;border-radius:6px;border:2px solid var(--accent);">
                        <?php endif; ?>
                    </div>
                    <input type="file" id="image1" name="image1" accept="image/*" style="width:100%;background:#23252b;color:var(--white);border:1.5px solid var(--gray);border-radius:var(--radius);">
                    <small class="file-help">Upload gambar berkualitas tinggi (JPEG, PNG, GIF, max 5MB)</small>
                </div>
                <div class="image-upload-group" style="margin-bottom:1rem;">
                    <label for="image2">Gambar Tambahan 2 (Opsional)</label>
                    <div class="current-image" style="margin-bottom:0.5rem;">
                        <?php if (!empty($row['image2'])): ?>
                            <img src="uploads/<?php echo htmlspecialchars($row['image2']); ?>" alt="Current Image 2" style="width:90px;height:60px;object-fit:cover;border-radius:6px;border:2px solid var(--blue-metal);">
                        <?php endif; ?>
                    </div>
                    <input type="file" id="image2" name="image2" accept="image/*" style="width:100%;background:#23252b;color:var(--white);border:1.5px solid var(--gray);border-radius:var(--radius);">
                </div>
                <div class="image-upload-group" style="margin-bottom:1rem;">
                    <label for="image3">Gambar Tambahan 3 (Opsional)</label>
                    <div class="current-image" style="margin-bottom:0.5rem;">
                        <?php if (!empty($row['image3'])): ?>
                            <img src="uploads/<?php echo htmlspecialchars($row['image3']); ?>" alt="Current Image 3" style="width:90px;height:60px;object-fit:cover;border-radius:6px;border:2px solid var(--blue-metal);">
                        <?php endif; ?>
                    </div>
                    <input type="file" id="image3" name="image3" accept="image/*" style="width:100%;background:#23252b;color:var(--white);border:1.5px solid var(--gray);border-radius:var(--radius);">
                </div>
            </div>
            <div class="form-actions" style="margin-bottom:1.2rem;display:flex;gap:1rem;">
                <input type="hidden" name="action" value="edit">
                <input type="submit" value="Update Article" class="button admin-button" style="flex:1;background:var(--accent);color:#fff;font-weight:700;font-size:1.1rem;padding:0.8rem 0;border-radius:var(--radius);border:none;">
                <a href="detail.php?id=<?php echo $id; ?>" class="button secondary-button" style="flex:1;background:var(--blue-metal);color:#fff;font-weight:700;font-size:1.1rem;padding:0.8rem 0;border-radius:var(--radius);border:none;text-align:center;text-decoration:none;">Lihat Berita</a>
                <a href="home.php" class="button secondary-button" style="flex:1;background:var(--gray);color:#18191d;font-weight:700;font-size:1.1rem;padding:0.8rem 0;border-radius:var(--radius);border:none;text-align:center;text-decoration:none;">Kembali ke Home</a>
            </div>
        </form>
        <!-- Delete Section -->
        <div class="delete-section" style="flex:1 1 320px;min-width:320px;background:#18191d;border-radius:var(--radius);border:2.5px solid var(--accent);box-shadow:var(--shadow);padding:1.5rem;margin-top:2rem;">
            <h3 style="color:var(--accent);font-weight:800;">Hapus Berita</h3>
            <div class="delete-warning" style="display:flex;align-items:center;gap:1rem;margin-bottom:1rem;">
                <div class="warning-icon" style="font-size:2em;color:var(--accent);">&#9888;</div>
                <div class="warning-content">
                    <h4 style="margin:0;color:var(--accent);">Zona Bahaya</h4>
                    <p style="color:var(--gray);margin:0;">Aksi ini tidak bisa dibatalkan. Berita dan gambar akan dihapus permanen.</p>
                </div>
            </div>
            <form method="post" class="delete-form" onsubmit="return confirm('Yakin ingin menghapus berita ini? Tindakan ini tidak bisa dibatalkan.');">
                <input type="hidden" name="action" value="delete">
                <input type="submit" value="Hapus Berita Permanen" class="button delete-button" style="width:100%;background:var(--accent);color:#fff;font-weight:700;font-size:1.1rem;padding:0.8rem 0;border-radius:var(--radius);border:none;">
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const descriptionTextarea = document.getElementById('description');
    const charCount = document.querySelector('.char-count');
    if (descriptionTextarea && charCount) {
        descriptionTextarea.addEventListener('input', function() {
            const count = this.value.length;
            charCount.textContent = count + ' karakter';
            if (count < 20) {
                charCount.style.color = '#ef4444';
            } else if (count < 100) {
                charCount.style.color = '#f59e0b';
            } else {
                charCount.style.color = 'var(--gray)';
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
