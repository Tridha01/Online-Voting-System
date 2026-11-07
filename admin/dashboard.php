<?php
// Admin home page: quick numbers and shortcuts.
require_once __DIR__ . '/../includes/auth.php';
require_admin();
$page_title = 'Dashboard';

// Counts a whole table. $table is always a fixed name written by us (never user input).
function count_rows($conn, $table) {
    $result = $conn->query("SELECT COUNT(*) AS c FROM $table");
    $row = $result->fetch_assoc();
    return (int)$row['c'];
}

$total_voters     = count_rows($conn, 'voters');
$total_elections  = count_rows($conn, 'elections');
$total_candidates = count_rows($conn, 'candidates');
$total_votes      = count_rows($conn, 'votes');

$result = $conn->query('SELECT COUNT(*) AS c FROM elections WHERE NOW() BETWEEN start_time AND end_time');
$active_elections = (int)$result->fetch_assoc()['c'];

include __DIR__ . '/admin_header.php';
?>
<h1>Dashboard</h1>
<p>Welcome, <?php echo e($_SESSION['admin_username'] ?? 'Admin'); ?>.</p>

<div class="cards">
  <div class="stat"><b><?php echo $total_voters; ?></b>Registered voters</div>
  <div class="stat"><b><?php echo $total_elections; ?></b>Elections</div>
  <div class="stat"><b><?php echo $active_elections; ?></b>Active now</div>
  <div class="stat"><b><?php echo $total_candidates; ?></b>Candidates</div>
  <div class="stat"><b><?php echo $total_votes; ?></b>Votes cast</div>
</div>

<div class="card">
  <h3>Quick actions</h3>
  <a class="btn" href="<?php echo BASE_URL; ?>/admin/add_election.php">Create / view elections</a>
  <a class="btn" href="<?php echo BASE_URL; ?>/admin/candidates.php">Manage candidates</a>
  <a class="btn" href="<?php echo BASE_URL; ?>/admin/voters.php">View voters</a>
  <a class="btn" href="<?php echo BASE_URL; ?>/admin/results.php">View results</a>
</div>
<?php include __DIR__ . '/admin_footer.php'; ?>
