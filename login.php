<?php
// login.php - voter login (FR-4, FR-5)
require_once __DIR__ . '/config.php';

// Already logged in? Go to the dashboard.
if (isset($_SESSION['voter_id'])) {
    header('Location: ' . BASE_URL . '/dashboard.php');
    exit;
}

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
        $result = $stmt->get_result();
        $voter  = $result->fetch_assoc();
        $stmt->close();

        // Compare the typed password with the stored hash
        if ($voter && password_verify($password, $voter['password'])) {
            session_regenerate_id(true);               // new session id after login
            $_SESSION['voter_id']   = $voter['voter_id'];
            $_SESSION['voter_name'] = $voter['name'];
            header('Location: ' . BASE_URL . '/dashboard.php');
            exit;
        } else {
            // Same message for wrong email and wrong password
            $error = 'Invalid email or password.';
        }
    }
}

$page_title = 'Log in';
include __DIR__ . '/includes/header.php';
?>

<div class="card form-card">
    <h1>Log in</h1>

    <?php if ($error !== ''): ?>
        <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form method="post" action="">
        <label for="email">Email</label>
        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>

        <label for="password">Password</label>
        <input type="password" id="password" name="password" required>

        <button type="submit" class="btn btn-full">Log in</button>
    </form>

    <p class="small-link">New voter? <a href="<?php echo BASE_URL; ?>/register.php">Create an account</a></p>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
