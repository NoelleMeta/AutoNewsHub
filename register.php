<?php
include 'includes/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Validate input
    if (empty($username) || empty($password) || empty($confirm_password)) {
        $error = "Please fill in all fields.";
    } elseif (strlen($username) < 3) {
        $error = "Username must be at least 3 characters long.";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters long.";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } else {
        // Check if username already exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $error = "Username already exists. Please choose a different one.";
        } else {
            // Hash password and insert user
            $hashed_password = password_hash($password, PASSWORD_BCRYPT);
            
            $stmt = $conn->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, 'user')");
            $stmt->bind_param("ss", $username, $hashed_password);
            
            if ($stmt->execute()) {
                $success = "Registration successful! You can now login.";
            } else {
                $error = "Error creating account: " . $conn->error;
            }
        }
        $stmt->close();
    }
}

include 'includes/header.php';
?>

<div class="page-header">
    <h2>Daftar ke AutoNewsHub</h2>
    <p>Buat akun untuk akses penuh ke berita, komentar, dan komunitas otomotif.</p>
</div>

<div class="auth-container" style="max-width:400px;margin:2rem auto;">
    <form method="post" class="auth-form" style="background:#18191d;padding:2rem 2.2rem;border-radius:var(--radius);box-shadow:var(--shadow);border:2.5px solid var(--accent);">
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
        
        <div class="form-group" style="margin-bottom:1.2rem;">
            <label for="username">Username</label>
            <input type="text" id="username" name="username" value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>" placeholder="Username (min 3 karakter)" required style="width:100%;padding:0.7rem 1rem;background:#23252b;color:var(--white);border:1.5px solid var(--gray);border-radius:var(--radius);font-size:1.1rem;">
        </div>
        
        <div class="form-group" style="margin-bottom:1.2rem;">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" placeholder="Password (min 6 karakter)" required style="width:100%;padding:0.7rem 1rem;background:#23252b;color:var(--white);border:1.5px solid var(--gray);border-radius:var(--radius);font-size:1.1rem;">
        </div>
        
        <div class="form-group" style="margin-bottom:1.2rem;">
            <label for="confirm_password">Konfirmasi Password</label>
            <input type="password" id="confirm_password" name="confirm_password" placeholder="Ulangi password" required style="width:100%;padding:0.7rem 1rem;background:#23252b;color:var(--white);border:1.5px solid var(--gray);border-radius:var(--radius);font-size:1.1rem;">
        </div>
        
        <div class="form-actions" style="margin-bottom:1.2rem;">
            <input type="submit" value="Daftar" class="button auth-button" style="width:100%;background:var(--accent);color:#fff;font-weight:700;font-size:1.1rem;padding:0.8rem 0;border-radius:var(--radius);border:none;">
        </div>
        
        <div class="auth-links" style="text-align:center;">
            <p>Sudah punya akun? <a href="login.php" style="color:var(--blue-metal);font-weight:600;">Login di sini</a></p>
            <p><a href="home.php" style="color:var(--gray);">&larr; Kembali ke Home</a></p>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const password = document.getElementById('password');
    const confirmPassword = document.getElementById('confirm_password');
    
    function validatePassword() {
        if (password.value !== confirmPassword.value) {
            confirmPassword.setCustomValidity("Passwords don't match");
        } else {
            confirmPassword.setCustomValidity('');
        }
    }
    
    password.addEventListener('change', validatePassword);
    confirmPassword.addEventListener('keyup', validatePassword);
});
</script>

<?php include 'includes/footer.php'; ?>