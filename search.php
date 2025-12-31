<?php
include 'includes/db.php';
include 'includes/header.php';

if (isset($_GET['query']) && !empty(trim($_GET['query']))) {
    $query = trim($_GET['query']);
    
    // Use prepared statement to prevent SQL injection
    $stmt = $conn->prepare("SELECT * FROM news WHERE title LIKE ? OR description LIKE ? ORDER BY created_at DESC");
    $search_term = "%$query%";
    $stmt->bind_param("ss", $search_term, $search_term);
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();
} else {
    header('Location: home.php');
    exit();
}
?>

<div class="page-header">
    <h2>Hasil Pencarian</h2>
    <p>Menampilkan hasil untuk: <strong style="color:var(--accent);">"<?= htmlspecialchars($query) ?>"</strong></p>
</div>

<?php if ($result->num_rows > 0): ?>
    <div class="search-stats" style="margin-bottom:1.5rem;text-align:center;color:var(--gray);">
        <span><?= $result->num_rows ?> hasil ditemukan</span>
    </div>
    <div class="news-grid">
        <?php while($row = $result->fetch_assoc()): ?>
            <div class="news-item">
                <div class="news-image">
                    <img src="uploads/<?= htmlspecialchars($row['image1']) ?>" alt="<?= htmlspecialchars($row['title']) ?>">
                </div>
                <div class="news-content">
                    <h3><?= htmlspecialchars($row['title']) ?></h3>
                    <p><?= mb_strimwidth(strip_tags($row['description']), 0, 100, '...') ?></p>
                    <div class="news-meta" style="margin-bottom:0.7rem;">
                        <span class="news-date" style="color:var(--gray);font-size:0.95em;"><?= date('d M Y', strtotime($row['created_at'])) ?></span>
                    </div>
                    <a href="detail.php?id=<?= $row['id'] ?>" class="read-more-btn">Read More</a>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
<?php else: ?>
    <div class="news-item" style="background:#18191d;border:2.5px solid var(--accent);text-align:center;max-width:500px;margin:2rem auto;">
        <div class="news-content">
            <h3 style="color:var(--accent);">Tidak ada hasil ditemukan</h3>
            <p style="color:var(--gray);">Maaf, tidak ada artikel yang cocok dengan kata kunci <strong>"<?= htmlspecialchars($query) ?>"</strong></p>
            <div class="search-suggestions" style="margin:1.2rem 0;">
                <h4 style="color:var(--blue-metal);margin-bottom:0.5rem;">Coba tips berikut:</h4>
                <ul style="color:var(--gray);text-align:left;max-width:350px;margin:0 auto;">
                    <li>Periksa ejaan kata kunci</li>
                    <li>Coba kata kunci lain</li>
                    <li>Gunakan istilah yang lebih umum</li>
                    <li><a href="home.php" style="color:var(--accent);font-weight:600;">Lihat berita terbaru</a></li>
                </ul>
            </div>
            <a href="home.php" class="button" style="background:var(--blue-metal);color:#fff;">Kembali ke Home</a>
        </div>
    </div>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>
