<?php
// includes/header.php - top part of every page (HTML head + menu)
// Set $page_title in a page BEFORE including this file to change the title.
require_once __DIR__ . '/../config.php';   // starts session + gives $conn and BASE_URL

if (!isset($page_title)) {
    $page_title = 'Home';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo htmlspecialchars($page_title); ?> - Online Voting System</title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/style.css">
</head>
<body>
<header class="site-header">
    <div class="container nav">
        <a class="brand" href="<?php echo BASE_URL; ?>/index.php">Online Voting</a>
        <nav>
            <?php if (isset($_SESSION['voter_id'])): ?>
                <span class="nav-user"><?php echo htmlspecialchars($_SESSION['voter_name'] ?? 'Voter'); ?></span>
                <a href="<?php echo BASE_URL; ?>/dashboard.php">Dashboard</a>
                <a href="<?php echo BASE_URL; ?>/logout.php">Log out</a>
            <?php else: ?>
                <a href="<?php echo BASE_URL; ?>/register.php">Register</a>
                <a href="<?php echo BASE_URL; ?>/login.php">Log in</a>
            <?php endif; ?>
        </nav>
    </div>
</header>
<main class="container">
