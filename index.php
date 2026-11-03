<?php
// index.php - home page
$page_title = 'Home';
include __DIR__ . '/includes/header.php';
?>

<section class="hero">
    <h1>Vote online, from anywhere.</h1>
    <p class="lead">Register once, pick an active election and cast your vote. Your choice stays private.</p>

    <div class="actions">
        <?php if (isset($_SESSION['voter_id'])): ?>
            <a class="btn" href="<?php echo BASE_URL; ?>/dashboard.php">Go to dashboard</a>
        <?php else: ?>
            <a class="btn" href="<?php echo BASE_URL; ?>/register.php">Register to vote</a>
            <a class="btn btn-outline" href="<?php echo BASE_URL; ?>/login.php">Log in</a>
        <?php endif; ?>
    </div>
</section>

<section class="card">
    <h2>How it works</h2>
    <ol class="steps">
        <li>Create your voter account with your name, email and password.</li>
        <li>Log in and open the list of active elections.</li>
        <li>Choose one candidate and submit your vote.</li>
    </ol>
    <p class="lead">Each voter can vote only once in each election.</p>
</section>

<p class="small-link"><a href="<?php echo BASE_URL; ?>/admin/login.php">Admin login</a></p>

<?php include __DIR__ . '/includes/footer.php'; ?>
