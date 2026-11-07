<?php
// FR-10 (edit part): Admin edits a candidate's name and description.
require_once __DIR__ . '/../includes/auth.php';
require_admin();
$page_title = 'Edit Candidate';

$candidate_id = (int)($_GET['id'] ?? $_POST['candidate_id'] ?? 0);

// Load the candidate together with its election title
$stmt = $conn->prepare(
    'SELECT c.candidate_id, c.election_id, c.name, c.description, el.title AS election_title
     FROM candidates c
     JOIN elections el ON el.election_id = c.election_id
     WHERE c.candidate_id = ?'
);
$stmt->bind_param('i', $candidate_id);
$stmt->execute();
$candidate = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$candidate) {
    flash_set('Candidate not found.', 'error');
    redirect('/admin/candidates.php');
}

$errors = [];
$name = $candidate['name'];
$description = $candidate['description'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');

    if ($name === '' || strlen($name) > 100) {
        $errors[] = 'Candidate name is required (maximum 100 characters).';
    }
    if (strlen($description) > 255) {
        $errors[] = 'Description can have at most 255 characters.';
    }

    if (empty($errors)) {
        $stmt = $conn->prepare('UPDATE candidates SET name = ?, description = ? WHERE candidate_id = ?');
        $stmt->bind_param('ssi', $name, $description, $candidate_id);
        if ($stmt->execute()) {
            $stmt->close();
            flash_set('Candidate updated successfully.');
            redirect('/admin/candidates.php?election_id=' . (int)$candidate['election_id']);
        } else {
            $errors[] = 'Could not update the candidate. Please try again.';
            $stmt->close();
        }
    }
}

include __DIR__ . '/admin_header.php';
?>
<h1>Edit Candidate</h1>
<p>Election: <strong><?php echo e($candidate['election_title']); ?></strong></p>

<div class="card">
  <?php foreach ($errors as $err) { ?>
    <div class="msg error"><?php echo e($err); ?></div>
  <?php } ?>
  <form method="post" action="">
    <input type="hidden" name="candidate_id" value="<?php echo (int)$candidate['candidate_id']; ?>">

    <label for="name">Candidate name</label>
    <input type="text" id="name" name="name" maxlength="100" value="<?php echo e($name); ?>" required>

    <label for="description">Party or short description</label>
    <input type="text" id="description" name="description" maxlength="255" value="<?php echo e($description); ?>">

    <button class="btn" type="submit">Save changes</button>
    <a class="btn btn-grey" href="<?php echo BASE_URL; ?>/admin/candidates.php?election_id=<?php echo (int)$candidate['election_id']; ?>">Cancel</a>
  </form>
</div>
<?php include __DIR__ . '/admin_footer.php'; ?>
