<?php
// FR-1: Voter registers with name, email and password.
// FR-2: The email must not be registered already.
// FR-3: The password is stored hashed with password_hash().
require_once __DIR__ . '/includes/auth.php';

if (isset($_SESSION['voter_id'])) {
    redirect('/dashboard.php');
}

$page_title = 'Register';
$errors = [];
$name = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = trim($_POST['name'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm_password'] ?? '';

    if ($name === '' || strlen($name) > 100) {
        $errors[] = 'Name is required (maximum 100 characters).';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 100) {
        $errors[] = 'Please enter a valid email address.';
    }
    if (strlen($password) < 6) {
        $errors[] = 'Password must be at least 6 characters.';
    } elseif ($password !== $confirm) {
        $errors[] = 'Password and confirm password do not match.';
    }

    if (empty($errors)) {
        // FR-2: is this email already registered?
        $stmt = $conn->prepare('SELECT voter_id FROM voters WHERE email = ?');
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $stmt->store_result();
        $exists = $stmt->num_rows > 0;
        $stmt->close();

        if ($exists) {
            $errors[] = 'This email is already registered. Please log in.';
        } else {
            // FR-3: hash the password before saving
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare('INSERT INTO voters (name, email, password) VALUES (?, ?, ?)');
            $stmt->bind_param('sss', $name, $email, $hash);
            if ($stmt->execute()) {
                $stmt->close();
                flash_set('Registration successful. Please log in.');
                redirect('/login.php');
            } else {
                // 1062 = duplicate email (two people registering at the same moment)
                if ($stmt->errno === 1062) {
                    $errors[] = 'This email is already registered. Please log in.';
                } else {
                    $errors[] = 'Could not register. Please try again.';
                }
                $stmt->close();
            }
        }
    }
}

include __DIR__ . '/includes/header.php';
?>
<div class="card form-card">
  <h1>Voter Registration</h1>
  <?php foreach ($errors as $err) { ?>
    <div class="msg error"><?php echo e($err); ?></div>
  <?php } ?>
  <form method="post" action="">
    <label for="name">Full name</label>
    <input type="text" id="name" name="name" maxlength="100" value="<?php echo e($name); ?>" required>

    <label for="email">Email</label>
    <input type="email" id="email" name="email" maxlength="100" value="<?php echo e($email); ?>" required>

    <label for="password">Password</label>
    <input type="password" id="password" name="password" required>
    <div class="hint">At least 6 characters.</div>

    <label for="confirm_password">Confirm password</label>
    <input type="password" id="confirm_password" name="confirm_password" required>

    <button class="btn" type="submit">Register</button>
  </form>
  <p>Already registered? <a href="<?php echo BASE_URL; ?>/login.php">Log in</a></p>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
