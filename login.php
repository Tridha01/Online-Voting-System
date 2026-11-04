<?php
// FR-7: Admin logs in through a separate admin login page.
require_once __DIR__ . '/../includes/auth.php';

// Already logged in? Go to the dashboard.
if (isset($_SESSION['admin_id'])) {
    redirect('/admin/dashboard.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $error = 'Please enter username and password.';
    } else {
        // Find the admin by username (prepared statement)
        $stmt = $conn->prepare('SELECT admin_id, username, password FROM admins WHERE username = ?');
        $stmt->bind_param('s', $username);
        $stmt->execute();
        $admin = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        // Compare the typed password with the stored hash
        if ($admin && password_verify($password, $admin['password'])) {
            session_regenerate_id(true);
            $_SESSION['admin_id'] = $admin['admin_id'];
            $_SESSION['admin_username'] = $admin['username'];
            redirect('/admin/dashboard.php');
        } else {
            $error = 'Invalid username or password.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Admin Login | Online Voting System</title>
<style>
body{font-family:Arial,Helvetica,sans-serif;background:#f4f6f9;margin:0;display:flex;justify-content:center;align-items:center;min-height:100vh}
.box{background:#fff;padding:28px;border-radius:8px;width:100%;max-width:360px;box-shadow:0 1px 6px rgba(0,0,0,.15)}
h1{font-size:22px;margin-top:0;color:#1F4E79}
label{display:block;margin:12px 0 4px;font-weight:bold}
input{width:100%;padding:9px;border:1px solid #aab;border-radius:4px;box-sizing:border-box;font-size:14px}
button{margin-top:16px;width:100%;background:#1F4E79;color:#fff;border:0;padding:10px;border-radius:4px;font-size:15px;cursor:pointer}
.msg{padding:10px 14px;border-radius:4px;margin-bottom:10px;background:#fde7e7;border:1px solid #e0a3a3}
</style>
</head>
<body>
<div class="box">
  <h1>Admin Login</h1>
  <?php if ($error !== '') { ?>
    <div class="msg"><?php echo e($error); ?></div>
  <?php } ?>
  <form method="post" action="">
    <label for="username">Username</label>
    <input type="text" id="username" name="username" value="<?php echo e($_POST['username'] ?? ''); ?>" required>
    <label for="password">Password</label>
    <input type="password" id="password" name="password" required>
    <button type="submit">Login</button>
  </form>
</div>
</body>
</html>
