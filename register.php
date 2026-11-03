<?php
// register.php - voter registration (FR-1, FR-2, FR-3)
require_once __DIR__ . '/config.php';

// A logged-in voter does not need to register again
if (isset($_SESSION['voter_id'])) {
    header('Location: ' . BASE_URL . '/dashboard.php');
    exit;
}

$errors  = [];
$success = false;
$name    = '';
$email   = '';

// Run this block only when the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = trim($_POST['name'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm_password'] ?? '';

    // 1. Validate the input
    if ($name === '')                                 { $errors[] = 'Please enter your name.'; }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL))   { $errors[] = 'Please enter a valid email address.'; }
    if (strlen($password) < 6)                        { $errors[] = 'Password must be at least 6 characters.'; }
    if ($password !== $confirm)                       { $errors[] = 'Passwords do not match.'; }

    // 2. FR-2: check that the email is not already registered
    if (empty($errors)) {
        $stmt = $conn->prepare('SELECT voter_id FROM voters WHERE email = ?');
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $errors[] = 'This email is already registered. Please log in instead.';
        }
        $stmt->close();
    }

    // 3. FR-3: hash the password and save the voter
    if (empty($errors)) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        try {
            $stmt = $conn->prepare('INSERT INTO voters (name, email, password) VALUES (?, ?, ?)');
            $stmt->bind_param('sss', $name, $email, $hash);
            $stmt->execute();
            $stmt->close();
            $success = true;
            $name = $email = '';
        } catch (mysqli_sql_exception $e) {
            // Also catches the rare case of the UNIQUE email rule firing
            $errors[] = 'Could not register. This email may already be in use.';
        }
    }
}

$page_title = 'Register';
include __DIR__ . '/includes/header.php';
?>

<div class="card form-card">
    <h1>Create your account</h1>

    <?php if ($success): ?>
        <div class="alert alert-success">
            Registration successful. <a href="<?php echo BASE_URL; ?>/login.php">Log in now</a>.
        </div>
    <?php endif; ?>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-error">
            <ul>
                <?php foreach ($errors as $e): ?>
                    <li><?php echo htmlspecialchars($e); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="post" action="">
        <label for="name">Full name</label>
        <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($name); ?>" required>

        <label for="email">Email</label>
        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>

        <label for="password">Password</label>
        <input type="password" id="password" name="password" required>

        <label for="confirm_password">Confirm password</label>
        <input type="password" id="confirm_password" name="confirm_password" required>

        <button type="submit" class="btn btn-full">Register</button>
    </form>

    <p class="small-link">Already registered? <a href="<?php echo BASE_URL; ?>/login.php">Log in</a></p>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
