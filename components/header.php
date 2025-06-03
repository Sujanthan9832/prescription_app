<?php
// if (session_status() === PHP_SESSION_NONE) {
//     session_start();
// }
?>

<!DOCTYPE html>
<html>
<head>
    <title>User Dashboard</title>
    <link rel="stylesheet" type="text/css" href="style/globle.css">
    <link rel="stylesheet" type="text/css" href="style/header.css">
</head>
<body>

<!-- Navbar -->
<div class="navbar">
    <h2>Dashboard</h2>
    <div style="margin-left: auto;">
        Welcome, <?= htmlspecialchars($_SESSION['user_name'] ?? 'Guest') ?> |
        <a href="logout.php" style="color: #f1f1f1; text-decoration: underline;">Logout</a>
    </div>
</div>

<!-- Sidebar -->
<div class="sidebar">
    <a href="user_dashboard.php">Dashboard</a>
    <a href="upload.php">Upload Prescription</a>
    <a href="user_profile.php">Profile</a>
    <a href="logout.php">Logout</a>
</div>

<!-- Main content starts -->
<div class="main">
