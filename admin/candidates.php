<?php
// FR-9: Admin adds candidates to an election.
// FR-10 (delete part): Admin deletes candidates. Editing is done in edit_candidate.php.
require_once __DIR__ . '/../includes/auth.php';
require_admin();
$page_title = 'Candidates';

$election_id = (int)($_GET['election_id'] ?? $_POST['election_id'] ?? 0);
$election = null;

// Load the selected election (if any)
if ($election_id > 0) {
    $stmt = $conn->prepare('SELECT election_id, title, start_time, end_time FROM elections WHERE election_id = ?');
    $stmt->bind_param('i', $election_id);
    $stmt->execute();
    $election = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

$errors = [];
$name = '';
$description = '';

if ($election && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add') {
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');

        if ($name === '' || strlen($name) > 100) {
            $errors[] = 'Candidate name is required (maximum 100 characters).';
        }
        if (strlen($description) > 255) {
            $errors[] = 'Description can have at most 255 characters.';
        }

        if (empty($errors)) {
            $stmt = $conn->prepare('INSERT INTO candidates (election_id, name, description) VALUES (?, ?, ?)');
            $stmt->bind_param('iss', $election_id, $name, $description);
            if ($stmt->execute()) {
                $stmt->close();
                flash_set('Candidate added successfully.');
                redirect('/admin/candidates.php?election_id=' . $election_id);
            } else {
                $errors[] = 'Could not add the candidate. Please try again.';
                $stmt->close();
            }
        }
    } elseif ($action === 'delete') {
        $candidate_id = (int)($_POST['candidate_id'] ?? 0);
        $stmt = $conn->prepare('DELETE FROM candidates WHERE candidate_id = ? AND election_id = ?');
        $stmt->bind_param('ii', $candidate_id, $election_id);
        $stmt->execute();
        $deleted = $stmt->affected_rows;
        $stmt->close();
        if ($deleted > 0) {
            flash_set('Candidate deleted.');
        } else {
            flash_set('Candidate not found.', 'error');
        }
        redirect('/admin/candidates.php?election_id=' . $election_id);
    }
}

// Data for the page
$candidates = [];
$all_elections = null;
if ($election) {
    $stmt = $conn->prepare('SELECT candidate_id, name, description FROM candidates WHERE election_id = ? ORDER BY candidate_id');
    $stmt->bind_param('i', $election_id);
    $stmt->execute();
    $candidates = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
} else {
    $all_elections = $conn->query('SELECT election_id, title, start_time, end_time FROM elections ORDER BY start_time DESC');
}

include __DIR__ . '/admin_header.php';
?>
<h1>Candidates</h1>

<?php if (!$election) { ?>

  <?php if ($election_id > 0) { ?>
    <div class="msg error">Election not found. Please choose one from the list.</div>
  <?php } ?>
  <div class="card">
    <h3>Choose an election</h3>
    <div class="table-wrap">
    <table>
      <tr><th>ID</th><th>Title</th><th>Status</th><th>Action</th></tr>
      <?php if ($all_elections && $all_elections->num_rows > 0) { ?>
        <?php while ($row = $all_elections->fetch_assoc()) { ?>
          <?php $status = election_status($row['start_time'], $row['end_time']); ?>
          <tr>
            <td><?php echo (int)$row['election_id']; ?></td>
            <td><?php echo e($row['title']); ?></td>
            <td><span class="badge s-<?php echo e($status); ?>"><?php echo e($status); ?></span></td>
            <td><a href="<?php echo BASE_URL; ?>/admin/candidates.php?election_id=<?php echo (int)$row['election_id']; ?>">Manage candidates</a></td>
          </tr>
        <?php } ?>
      <?php } else { ?>
        <tr><td colspan="4">No elections yet. <a href="<?php echo BASE_URL; ?>/admin/add_election.php">Create one first</a>.</td></tr>
      <?php } ?>
    </table>
    </div>
  </div>

<?php } else { ?>

  <p>Election: <strong><?php echo e($election['title']); ?></strong>
     (<?php echo e(election_status($election['start_time'], $election['end_time'])); ?>)
     - <a href="<?php echo BASE_URL; ?>/admin/candidates.php">change election</a></p>

  <div class="card">
    <h3>Add a candidate</h3>
    <?php foreach ($errors as $err) { ?>
      <div class="msg error"><?php echo e($err); ?></div>
    <?php } ?>
    <form method="post" action="">
      <input type="hidden" name="action" value="add">
      <input type="hidden" name="election_id" value="<?php echo $election_id; ?>">

      <label for="name">Candidate name</label>
      <input type="text" id="name" name="name" maxlength="100" value="<?php echo e($name); ?>" required>

      <label for="description">Party or short description</label>
      <input type="text" id="description" name="description" maxlength="255" value="<?php echo e($description); ?>">

      <button class="btn" type="submit">Add candidate</button>
    </form>
  </div>

  <div class="card">
    <h3>Candidates in this election (<?php echo count($candidates); ?>)</h3>
    <div class="table-wrap">
    <table>
      <tr><th>ID</th><th>Name</th><th>Description</th><th>Actions</th></tr>
      <?php if (count($candidates) > 0) { ?>
        <?php foreach ($candidates as $c) { ?>
          <tr>
            <td><?php echo (int)$c['candidate_id']; ?></td>
            <td><?php echo e($c['name']); ?></td>
            <td><?php echo e($c['description']); ?></td>
            <td>
              <a class="btn btn-grey" href="<?php echo BASE_URL; ?>/admin/edit_candidate.php?id=<?php echo (int)$c['candidate_id']; ?>">Edit</a>
              <form class="inline" method="post" action=""
                    onsubmit="return confirm('Delete this candidate? Any votes for this candidate will also be deleted.');">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="election_id" value="<?php echo $election_id; ?>">
                <input type="hidden" name="candidate_id" value="<?php echo (int)$c['candidate_id']; ?>">
                <button class="btn btn-red" type="submit">Delete</button>
              </form>
            </td>
          </tr>
        <?php } ?>
      <?php } else { ?>
        <tr><td colspan="4">No candidates yet. Add the first one above.</td></tr>
      <?php } ?>
    </table>
    </div>
  </div>

<?php } ?>
<?php include __DIR__ . '/admin_footer.php'; ?>
