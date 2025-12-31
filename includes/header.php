<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@700;900&family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <title>AutoNewsHub</title>
</head>

<body>
    <header>
        <div class="container">
            <h1 style="display:flex;align-items:center;gap:0.5rem;">
                <span style="font-size:2.2rem;">&#128663;</span> <!-- Ikon mobil -->
                AutoNewsHub
            </h1>
            <nav>
                <a href="home.php" class="<?= basename($_SERVER['PHP_SELF'])=='home.php'?'active':'' ?>">Home</a>
                <a href="about.php" class="<?= basename($_SERVER['PHP_SELF'])=='about.php'?'active':'' ?>">About Us</a>
                <?php if (isset($_SESSION['role']) && $_SESSION['role'] == 'admin'): ?>
                    <a href="crud.php" class="<?= basename($_SERVER['PHP_SELF'])=='crud.php'?'active':'' ?>">Manage News</a>
                <?php endif; ?>
                <?php if (isset($_SESSION['username'])): ?>
                    <a href="logout.php">Logout</a>
                <?php else: ?>
                    <a href="login.php" class="<?= basename($_SERVER['PHP_SELF'])=='login.php'?'active':'' ?>">Login</a>
                    <a href="register.php" class="<?= basename($_SERVER['PHP_SELF'])=='register.php'?'active':'' ?>">Register</a>
                <?php endif; ?>
            </nav>
        </div>
    </header>
    <div class="container">
        <div class="main-content">
<script>
    // Theme toggle logic
    function setTheme(theme) {
        document.documentElement.setAttribute('data-theme', theme);
        localStorage.setItem('theme', theme);
        document.getElementById('theme-toggle').textContent = theme === 'light' ? '🌞' : '🌙';
    }
    function toggleTheme() {
        const current = document.documentElement.getAttribute('data-theme') || 'dark';
        setTheme(current === 'dark' ? 'light' : 'dark');
    }
    document.addEventListener('DOMContentLoaded', function() {
        const saved = localStorage.getItem('theme');
        setTheme(saved === 'light' ? 'light' : 'dark');
        document.getElementById('theme-toggle').addEventListener('click', toggleTheme);
    });
    </script>