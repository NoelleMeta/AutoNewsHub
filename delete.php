<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header('Location: home.php');
    exit();
}

include 'includes/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['id'])) {
    $id = intval($_POST['id']);
    
    // Redirect to edit page with delete action
    header("Location: edit.php?id=$id");
    exit();
} else {
    header('Location: home.php');
    exit();
}
?>
