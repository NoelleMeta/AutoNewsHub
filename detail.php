<?php
include 'includes/db.php';
include 'includes/header.php';

// Validate and sanitize the ID parameter
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = intval($_GET['id']);
    $stmt = $conn->prepare("SELECT * FROM news WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
    } else {
        echo '<div class="alert alert-danger">News article not found.</div>';
        include 'includes/footer.php';
        exit();
    }
    $stmt->close();
} else {
    echo '<div class="alert alert-danger">Invalid news ID.</div>';
    include 'includes/footer.php';
    exit();
}

// Handle comment deletion
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_comment'])) {
    if (isset($_SESSION['role']) && $_SESSION['role'] == 'admin') {
        $comment_id = intval($_POST['comment_id']);

        $stmt = $conn->prepare("DELETE FROM comments WHERE id = ?");
        $stmt->bind_param("i", $comment_id);
        if ($stmt->execute()) {
            echo '<div class="alert alert-success">Comment deleted successfully!</div>';
        } else {
            echo '<div class="alert alert-danger">Error deleting comment: ' . $conn->error . '</div>';
        }
        $stmt->close();
    } else {
        echo '<div class="alert alert-warning">You do not have permission to delete this comment.</div>';
    }
}

// Handle comment submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['comment'])) {
    if (isset($_SESSION['username'])) {
        $username = $_SESSION['username'];
        $comment = trim($_POST['comment']);
        
        // Validate comment
        if (empty($comment)) {
            echo '<div class="alert alert-warning">Comment cannot be empty.</div>';
        } elseif (strlen($comment) > 1000) {
            echo '<div class="alert alert-warning">Comment is too long. Maximum 1000 characters allowed.</div>';
        } else {
            $comment = $conn->real_escape_string($comment);
            $parent_id = isset($_POST['parent_id']) ? intval($_POST['parent_id']) : NULL;

            // Insert comment into database
            $stmt = $conn->prepare("INSERT INTO comments (news_id, username, comment, parent_id) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("issi", $id, $username, $comment, $parent_id);

            if ($stmt->execute()) {
                echo '<div class="alert alert-success">Comment added successfully!</div>';
            } else {
                echo '<div class="alert alert-danger">Error adding comment: ' . $conn->error . '</div>';
            }
            $stmt->close();
        }
    } else {
        echo '<div class="alert alert-warning">You need to login to comment.</div>';
    }
}

// Fetch comments for this news item
$stmt = $conn->prepare("SELECT * FROM comments WHERE news_id = ? AND parent_id IS NULL ORDER BY created_at DESC");
$stmt->bind_param("i", $id);
$stmt->execute();
$result_comments = $stmt->get_result();
?>

<div class="news-details">
    <?php if (!empty($row['image1'])): ?>
        <div class="news-image" style="margin-bottom:2rem;">
            <img src="uploads/<?= htmlspecialchars($row['image1']) ?>" alt="<?= htmlspecialchars($row['title']) ?>" style="width:100%;max-height:400px;object-fit:cover;border-radius:var(--radius);border:3px solid var(--accent);box-shadow:0 4px 24px #0008;">
        </div>
    <?php endif; ?>
    <div class="news-header">
        <h2 style="font-size:2.3rem;color:var(--accent);font-weight:900;letter-spacing:1px;"><?= htmlspecialchars($row['title']) ?></h2>
        <?php if (isset($row['created_at'])): ?>
        <div class="news-meta" style="margin-bottom:1rem;">
            <span class="news-date">Dipublikasikan: <?= date('d M Y', strtotime($row['created_at'])) ?></span>
            <span class="news-category" style="background:var(--blue-metal);color:#fff;padding:0.2em 0.8em;border-radius:8px;margin-left:1em;">Otomotif</span>
        </div>
        <?php endif; ?>
    </div>
    <div class="description" style="margin-bottom:2rem;">
        <div class="description-content" style="font-size:1.15rem;line-height:1.7;color:var(--white);">
            <?= nl2br(htmlspecialchars($row['description'])) ?>
        </div>
    </div>
    <?php if (!empty($row['image2']) || !empty($row['image3'])): ?>
    <div class="news-gallery" style="display:flex;gap:1.5rem;margin-bottom:2rem;">
        <?php if (!empty($row['image2'])): ?>
            <img src="uploads/<?= htmlspecialchars($row['image2']) ?>" alt="Gambar tambahan" style="width:48%;max-height:220px;object-fit:cover;border-radius:var(--radius);border:2px solid var(--blue-metal);">
        <?php endif; ?>
        <?php if (!empty($row['image3'])): ?>
            <img src="uploads/<?= htmlspecialchars($row['image3']) ?>" alt="Gambar tambahan" style="width:48%;max-height:220px;object-fit:cover;border-radius:var(--radius);border:2px solid var(--blue-metal);">
        <?php endif; ?>
    </div>
    <?php endif; ?>
    <?php if (isset($_SESSION['role']) && $_SESSION['role'] == 'admin'): ?>
        <div class="admin-controls" style="margin-bottom:2rem;">
            <a href="edit.php?id=<?= $row['id'] ?>" class="button edit-button">Edit Artikel</a>
        </div>
    <?php endif; ?>
</div>

<!-- Comments Section -->
<div class="comments-section">
    <h3>Comments & Discussion</h3>
    
    <!-- Comment Form -->
    <?php if (isset($_SESSION['username'])): ?>
        <form method="post" class="comment-form">
            <label for="comment">Share your thoughts:</label>
            <textarea name="comment" id="comment" rows="4" placeholder="What do you think about this article? Share your opinion..." required maxlength="1000"></textarea>
            <div class="form-footer">
                <span class="char-count">0/1000 characters</span>
                <input type="submit" value="Post Comment" class="button">
            </div>
        </form>
    <?php else: ?>
        <div class="login-prompt">
            <p>You need to <a href="login.php">login</a> to leave a comment and join the discussion.</p>
        </div>
    <?php endif; ?>

    <!-- Display Comments -->
    <?php if ($result_comments->num_rows > 0): ?>
        <div class="comments-list">
            <?php while ($comment_row = $result_comments->fetch_assoc()): ?>
                <div class="comment">
                    <div class="comment-header">
                        <div class="comment-author">
                            <strong><?php echo htmlspecialchars($comment_row['username']); ?></strong>
                        </div>
                        <?php if (isset($comment_row['created_at'])): ?>
                            <span class="comment-date"><?php echo date('M d, Y H:i', strtotime($comment_row['created_at'])); ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="comment-content">
                        <p><?php echo nl2br(htmlspecialchars($comment_row['comment'])); ?></p>
                    </div>
                    
                    <?php if (isset($_SESSION['role']) && $_SESSION['role'] == 'admin'): ?>
                        <div class="comment-actions">
                            <form method="post" style="display:inline;">
                                <input type="hidden" name="comment_id" value="<?php echo $comment_row['id']; ?>">
                                <input type="submit" name="delete_comment" value="Delete" class="button delete-button"
                                    onclick="return confirm('Are you sure you want to delete this comment?');">
                            </form>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Display Replies -->
                    <?php
                    $parent_id = $comment_row['id'];
                    $stmt_replies = $conn->prepare("SELECT * FROM comments WHERE parent_id = ? ORDER BY created_at ASC");
                    $stmt_replies->bind_param("i", $parent_id);
                    $stmt_replies->execute();
                    $result_replies = $stmt_replies->get_result();
                    ?>
                    <?php if ($result_replies->num_rows > 0): ?>
                        <div class="replies-section">
                            <?php while ($reply_row = $result_replies->fetch_assoc()): ?>
                                <div class="comment reply">
                                    <div class="comment-header">
                                        <div class="comment-author">
                                            <strong>↳ <?php echo htmlspecialchars($reply_row['username']); ?></strong>
                                        </div>
                                        <?php if (isset($reply_row['created_at'])): ?>
                                            <span class="comment-date"><?php echo date('M d, Y H:i', strtotime($reply_row['created_at'])); ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="comment-content">
                                        <p><?php echo nl2br(htmlspecialchars($reply_row['comment'])); ?></p>
                                    </div>
                                    <?php if (isset($_SESSION['role']) && $_SESSION['role'] == 'admin'): ?>
                                        <div class="comment-actions">
                                            <form method="post" style="display:inline;">
                                                <input type="hidden" name="comment_id" value="<?php echo $reply_row['id']; ?>">
                                                <input type="submit" name="delete_comment" value="Delete" class="button delete-button"
                                                    onclick="return confirm('Are you sure you want to delete this reply?');">
                                            </form>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    <?php endif; ?>
                    <?php $stmt_replies->close(); ?>
                    
                    <!-- Reply Form -->
                    <?php if (isset($_SESSION['username'])): ?>
                        <form method="post" class="reply-form">
                            <textarea name="comment" rows="2" placeholder="Reply to this comment..." required maxlength="1000"></textarea>
                            <input type="hidden" name="parent_id" value="<?php echo $comment_row['id']; ?>">
                            <input type="submit" value="↳ Reply" class="button">
                        </form>
                    <?php endif; ?>
                </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <div class="no-comments">
            <div class="no-comments-icon"></div>
            <h4>No comments yet</h4>
            <p>Be the first to share your thoughts on this article!</p>
        </div>
    <?php endif; ?>
</div>

<script>
// Character counter for comment forms
document.addEventListener('DOMContentLoaded', function() {
    const commentTextarea = document.getElementById('comment');
    const charCount = document.querySelector('.char-count');
    
    if (commentTextarea && charCount) {
        commentTextarea.addEventListener('input', function() {
            const remaining = 1000 - this.value.length;
            charCount.textContent = this.value.length + '/1000 characters';
            
            if (remaining < 100) {
                charCount.style.color = '#ef4444';
            } else if (remaining < 200) {
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

// Share article function
function shareArticle() {
    if (navigator.share) {
        navigator.share({
            title: document.title,
            url: window.location.href
        });
    } else {
        // Fallback: copy to clipboard
        navigator.clipboard.writeText(window.location.href).then(function() {
            alert('Link copied to clipboard!');
        });
    }
}
</script>

<?php include 'includes/footer.php'; ?>