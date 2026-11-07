<?php
// Home page (public).
require_once __DIR__ . '/includes/auth.php';
$page_title = 'Home';
include __DIR__ . '/includes/header.php';
?>
<div class="hero">
  <h1>Online Voting System</h1>
  <p>A simple and secure way for members of an organization to vote for their preferred candidates online.</p>
  <?php if (isset($_SESSION['voter_id'])) { ?>
    <a class="btn" href="<?php echo BASE_URL; ?>/dashboard.php">Go to dashboard</a>
  <?php } else { ?>
    <a class="btn" href="<?php echo BASE_URL; ?>/register.php">Register</a>
    <a class="btn btn-grey" href="<?php echo BASE_URL; ?>/login.php">Login</a>
  <?php } ?>
</div>

<h2>How it works</h2>
<div class="steps">
  <div class="step"><b>1. Register</b><br>Create your voter account with your name, email and password.</div>
  <div class="step"><b>2. Log in</b><br>Open your dashboard to see the elections that are running now.</div>
  <div class="step"><b>3. Vote once</b><br>Choose one candidate and submit. Your vote is secret and cannot be changed.</div>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
