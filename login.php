<?php
include 'includes/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    // Validate input
    if (empty($username) || empty($password)) {
        $error = "Please fill in all fields.";
    } else {
        // Use prepared statement to prevent SQL injection
        $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            if (password_verify($password, $row['password'])) {
                session_start();
                $_SESSION['username'] = $username;
                $_SESSION['role'] = $row['role'];
                header('Location: home.php');
                exit();
            } else {
                $error = "Invalid password. Please try again.";
            }
        } else {
            $error = "No user found with that username.";
        }
        $stmt->close();
    }
}

include 'includes/header.php';
?>

<div class="page-header">
    <h2>Login ke AutoNewsHub</h2>
    <p>Masuk untuk akses penuh ke berita dan komunitas otomotif.</p>
</div>

<div class="auth-container" style="max-width:400px;margin:2rem auto;">
    <form method="post" class="auth-form" style="background:#18191d;padding:2rem 2.2rem;border-radius:var(--radius);box-shadow:var(--shadow);border:2.5px solid var(--accent);">
        <?php if (isset($error)): ?>
            <div class="alert alert-danger">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
        
        <div class="form-group" style="margin-bottom:1.2rem;">
            <label for="username">Username</label>
            <input type="text" id="username" name="username" value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>" placeholder="Username" required style="width:100%;padding:0.7rem 1rem;background:#23252b;color:var(--white);border:1.5px solid var(--gray);border-radius:var(--radius);font-size:1.1rem;">
        </div>
        
        <div class="form-group" style="margin-bottom:1.2rem;">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" placeholder="Password" required style="width:100%;padding:0.7rem 1rem;background:#23252b;color:var(--white);border:1.5px solid var(--gray);border-radius:var(--radius);font-size:1.1rem;">
        </div>
        
        <div class="form-actions" style="margin-bottom:1.2rem;">
            <input type="submit" value="Sign In" class="button auth-button" style="width:100%;background:var(--accent);color:#fff;font-weight:700;font-size:1.1rem;padding:0.8rem 0;border-radius:var(--radius);border:none;">
        </div>
        
        <div class="auth-links" style="text-align:center;">
            <p>Belum punya akun? <a href="register.php" style="color:var(--blue-metal);font-weight:600;">Daftar di sini</a></p>
            <p><a href="home.php" style="color:var(--gray);">&larr; Kembali ke Home</a></p>
        </div>
    </form>
</div>

<?php include 'includes/footer.php'; ?>