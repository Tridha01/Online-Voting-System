<?php
// FR-4: Registered voter logs in with email and password.
// FR-5: Wrong details show an error message.
require_once __DIR__ . '/includes/auth.php';

if (isset($_SESSION['voter_id'])) {
    redirect('/dashboard.php');
}

$page_title = 'Login';
$error = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        $error = 'Please enter your email and password.';
    } else {
        // Find the voter by email
        $stmt = $conn->prepare('SELECT voter_id, name, password FROM voters WHERE email = ?');
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $voter = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        // Compare the typed password with the stored hash
        if ($voter && password_verify($password, $voter['password'])) {
            session_regenerate_id(true);
            $_SESSION['voter_id']   = $voter['voter_id'];
            $_SESSION['voter_name'] = $voter['name'];
            redirect('/dashboard.php');
        } else {
            $error = 'Invalid email or password.';   // FR-5
        }
    }
}

include __DIR__ . '/includes/header.php';
?>
<div class="card form-card">
  <h1>Voter Login</h1>
  <?php if ($error !== '') { ?>
    <div class="msg error"><?php echo e($error); ?></div>
  <?php } ?>
  <form method="post" action="">
    <label for="email">Email</label>
    <input type="email" id="email" name="email" value="<?php echo e($email); ?>" required>

    <label for="password">Password</label>
    <input type="password" id="password" name="password" required>

    <button class="btn" type="submit">Login</button>
  </form>
  <p>New here? <a href="<?php echo BASE_URL; ?>/register.php">Create an account</a></p>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
