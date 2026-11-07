<?php
// Top part of every voter-side page.
// Before including this file: require auth.php and set $page_title.
if (!isset($page_title)) { $page_title = 'Online Voting System'; }
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?php echo e($page_title); ?> | Online Voting System</title>
<link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/style.css">
</head>
<body>
<div class="topbar">
  <a class="brand" href="<?php echo BASE_URL; ?>/index.php">Online Voting System</a>
  <nav>
    <a href="<?php echo BASE_URL; ?>/index.php">Home</a>
    <?php if (isset($_SESSION['voter_id'])) { ?>
      <a href="<?php echo BASE_URL; ?>/dashboard.php">Dashboard</a>
      <a href="<?php echo BASE_URL; ?>/logout.php">Logout (<?php echo e($_SESSION['voter_name'] ?? 'voter'); ?>)</a>
    <?php } else { ?>
      <a href="<?php echo BASE_URL; ?>/register.php">Register</a>
      <a href="<?php echo BASE_URL; ?>/login.php">Login</a>
    <?php } ?>
  </nav>
</div>
<div class="container">
<?php flash_show(); ?>
