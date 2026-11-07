<?php
// Top part of every admin page.
// Before including this file, set $page_title (and optionally $extra_head).
if (!isset($page_title)) { $page_title = 'Admin'; }
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?php echo e($page_title); ?> - Admin | Online Voting System</title>
<?php if (!empty($extra_head)) { echo $extra_head; } ?>
<style>
body{font-family:Arial,Helvetica,sans-serif;background:#f4f6f9;margin:0;color:#222}
.topbar{background:#1F4E79;color:#fff;padding:12px 20px;display:flex;flex-wrap:wrap;justify-content:space-between;align-items:center}
.topbar a{color:#fff;text-decoration:none;margin-left:14px}
.container{max-width:960px;margin:20px auto;padding:0 16px}
h1{font-size:24px;margin-top:0}
.card{background:#fff;border-radius:8px;padding:16px;margin-bottom:18px;box-shadow:0 1px 3px rgba(0,0,0,.12)}
.cards{display:flex;flex-wrap:wrap;gap:14px;margin-bottom:18px}
.stat{flex:1 1 150px;background:#fff;border-radius:8px;padding:16px;text-align:center;box-shadow:0 1px 3px rgba(0,0,0,.12)}
.stat b{display:block;font-size:28px;color:#1F4E79}
.table-wrap{overflow-x:auto}
table{width:100%;border-collapse:collapse;background:#fff}
th,td{border:1px solid #d0d7de;padding:8px;text-align:left;font-size:14px}
th{background:#D9E2F3}
label{display:block;margin:10px 0 4px;font-weight:bold}
input[type=text],input[type=password],input[type=datetime-local],select,textarea{width:100%;padding:8px;border:1px solid #aab;border-radius:4px;box-sizing:border-box;font-size:14px}
.btn{display:inline-block;background:#1F4E79;color:#fff;border:0;padding:8px 14px;border-radius:4px;cursor:pointer;text-decoration:none;font-size:14px;margin-top:8px}
.btn-red{background:#B3261E}
.btn-grey{background:#666}
.msg{padding:10px 14px;border-radius:4px;margin-bottom:14px}
.msg.success{background:#e3f4e6;border:1px solid #9ccfa4}
.msg.error{background:#fde7e7;border:1px solid #e0a3a3}
.badge{padding:2px 8px;border-radius:10px;font-size:12px;color:#fff}
.s-Active{background:#2e7d32}
.s-Upcoming{background:#ed8b00}
.s-Ended{background:#777}
.bar-wrap{background:#e5e9f0;border-radius:4px;height:16px;min-width:80px}
.bar{background:#1F4E79;height:16px;border-radius:4px}
.winner{font-size:18px;font-weight:bold;color:#1F4E79}
form.inline{display:inline}
</style>
</head>
<body>
<div class="topbar">
  <strong>Online Voting System - Admin</strong>
  <nav>
    <a href="<?php echo BASE_URL; ?>/admin/dashboard.php">Dashboard</a>
    <a href="<?php echo BASE_URL; ?>/admin/add_election.php">Elections</a>
    <a href="<?php echo BASE_URL; ?>/admin/candidates.php">Candidates</a>
    <a href="<?php echo BASE_URL; ?>/admin/voters.php">Voters</a>
    <a href="<?php echo BASE_URL; ?>/admin/results.php">Results</a>
    <a href="<?php echo BASE_URL; ?>/admin/logout.php">Logout (<?php echo e($_SESSION['admin_username'] ?? 'admin'); ?>)</a>
  </nav>
</div>
<div class="container">
<?php flash_show(); ?>
