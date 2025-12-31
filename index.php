<?php
include 'includes/header.php';

if (isset($_SESSION['username'])) {
    header('Location: home.php');
    exit();
} else {
    header('Location: login.php');
    exit();
}

?>
