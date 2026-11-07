<?php
// FR-8: Admin creates an election with a title, start date and end date.
// The page also lists all elections with their status.
require_once __DIR__ . '/../includes/auth.php';
require_admin();
$page_title = 'Elections';

$errors = [];
$title = '';
$start = '';
$end = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $start = $_POST['start_time'] ?? '';
    $end   = $_POST['end_time'] ?? '';
    $start_ts = strtotime($start);
    $end_ts   = strtotime($end);

    if ($title === '' || strlen($title) > 150) {
        $errors[] = 'Title is required (maximum 150 characters).';
    }
    if (!$start_ts || !$end_ts) {
        $errors[] = 'Please choose a valid start and end date and time.';
    } elseif ($end_ts <= $start_ts) {
        $errors[] = 'End time must be after the start time.';
    }

    if (empty($errors)) {
        $start_sql = date('Y-m-d H:i:s', $start_ts);
        $end_sql   = date('Y-m-d H:i:s', $end_ts);
        $admin_id  = $_SESSION['admin_id'];

        $stmt = $conn->prepare('INSERT INTO elections (title, start_time, end_time, admin_id) VALUES (?, ?, ?, ?)');
        $stmt->bind_param('sssi', $title, $start_sql, $end_sql, $admin_id);
        if ($stmt->execute()) {
            $stmt->close();
            flash_set('Election created successfully.');
            redirect('/admin/add_election.php');
        } else {
            $errors[] = 'Could not save the election. Please try again.';
            $stmt->close();
        }
    }
}

// All elections, newest first, with number of candidates and votes
$elections = $conn->query(
    'SELECT el.election_id, el.title, el.start_time, el.end_time,
            (SELECT COUNT(*) FROM candidates c WHERE c.election_id = el.election_id) AS cand_count,
            (SELECT COUNT(*) FROM votes v WHERE v.election_id = el.election_id) AS vote_count
     FROM elections el
     ORDER BY el.start_time DESC'
);

include __DIR__ . '/admin_header.php';
?>
<h1>Elections</h1>

<div class="card">
  <h3>Create a new election</h3>
  <?php foreach ($errors as $err) { ?>
    <div class="msg error"><?php echo e($err); ?></div>
  <?php } ?>
  <form method="post" action="">
    <label for="title">Election title</label>
    <input type="text" id="title" name="title" maxlength="150" value="<?php echo e($title); ?>" required>

    <label for="start_time">Start date and time</label>
    <input type="datetime-local" id="start_time" name="start_time" value="<?php echo e($start); ?>" required>

    <label for="end_time">End date and time</label>
    <input type="datetime-local" id="end_time" name="end_time" value="<?php echo e($end); ?>" required>

    <button class="btn" type="submit">Create election</button>
  </form>
</div>

<div class="card">
  <h3>All elections</h3>
  <div class="table-wrap">
  <table>
    <tr>
      <th>ID</th><th>Title</th><th>Start</th><th>End</th><th>Status</th>
      <th>Candidates</th><th>Votes</th><th>Actions</th>
    </tr>
    <?php if ($elections && $elections->num_rows > 0) { ?>
      <?php while ($row = $elections->fetch_assoc()) { ?>
        <?php $status = election_status($row['start_time'], $row['end_time']); ?>
        <tr>
          <td><?php echo (int)$row['election_id']; ?></td>
          <td><?php echo e($row['title']); ?></td>
          <td><?php echo e(date('d M Y, h:i A', strtotime($row['start_time']))); ?></td>
          <td><?php echo e(date('d M Y, h:i A', strtotime($row['end_time']))); ?></td>
          <td><span class="badge s-<?php echo e($status); ?>"><?php echo e($status); ?></span></td>
          <td><?php echo (int)$row['cand_count']; ?></td>
          <td><?php echo (int)$row['vote_count']; ?></td>
          <td>
            <a href="<?php echo BASE_URL; ?>/admin/candidates.php?election_id=<?php echo (int)$row['election_id']; ?>">Candidates</a> |
            <a href="<?php echo BASE_URL; ?>/admin/results.php?election_id=<?php echo (int)$row['election_id']; ?>">Results</a>
          </td>
        </tr>
      <?php } ?>
    <?php } else { ?>
      <tr><td colspan="8">No elections yet. Create the first one above.</td></tr>
    <?php } ?>
  </table>
  </div>
</div>
<?php include __DIR__ . '/admin_footer.php'; ?>
