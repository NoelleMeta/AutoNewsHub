<?php
include 'includes/header.php';
include 'includes/db.php';
?>
<div class="page-header">
    <h2>Berita Otomotif Terbaru</h2>
    <p>Update terkini dunia mobil, motor, dan teknologi otomotif.</p>
</div>
<form action="search.php" method="get" class="search-form" style="margin:0 0 2rem 0;display:flex;gap:0.5rem;max-width:400px;">
    <input type="text" name="query" placeholder="Cari berita..." required style="flex:1;">
    <button type="submit">Search</button>
</form>
<?php
// Ambil berita terbaru dari tabel news
$sql = "SELECT id, title, image1, description, created_at FROM news ORDER BY id DESC LIMIT 12";
$result = $conn->query($sql);
?>
<div class="news-grid">
<?php if ($result && $result->num_rows > 0):
    while($row = $result->fetch_assoc()): ?>
    <div class="news-item">
        <div class="news-image">
            <img src="uploads/<?= htmlspecialchars($row['image1']) ?>" alt="<?= htmlspecialchars($row['title']) ?>">
        </div>
        <div class="news-content">
            <h3><?= htmlspecialchars($row['title']) ?></h3>
            <p><?= mb_strimwidth(strip_tags($row['description']), 0, 100, '...') ?></p>
            <a class="read-more-btn" href="detail.php?id=<?= $row['id'] ?>">Read More</a>
        </div>
    </div>
<?php endwhile;
else: ?>
    <div class="news-item">
        <div class="news-content">
            <h3>Tidak ada berita ditemukan.</h3>
            <p>Belum ada berita otomotif yang tersedia saat ini.</p>
        </div>
    </div>
<?php endif; ?>
</div>
<?php include 'includes/footer.php'; ?>
