<?php
// FR-15: Show the candidates of the selected election.
// FR-16: Voter selects one candidate and submits the vote.
// FR-17: Only one vote per voter per election.
// FR-18: Confirmation message after the vote is recorded.
// FR-19: The page never shows who a voter voted for.
// FR-20: No voting before the start time or after the end time.
require_once __DIR__ . '/includes/auth.php';
require_voter();
$page_title = 'Vote';

$voter_id    = (int)$_SESSION['voter_id'];
$election_id = (int)($_GET['election_id'] ?? $_POST['election_id'] ?? 0);

// Load the election
$stmt = $conn->prepare('SELECT election_id, title, start_time, end_time FROM elections WHERE election_id = ?');
$stmt->bind_param('i', $election_id);
$stmt->execute();
$election = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$election) {
    flash_set('Election not found.', 'error');
    redirect('/dashboard.php');
}

// FR-20: is voting open? (Upcoming / Active / Ended)
$status = election_status($election['start_time'], $election['end_time']);

// FR-17: has this voter already voted in this election?
$stmt = $conn->prepare('SELECT vote_id FROM votes WHERE voter_id = ? AND election_id = ?');
$stmt->bind_param('ii', $voter_id, $election_id);
$stmt->execute();
$stmt->store_result();
$already_voted = $stmt->num_rows > 0;
$stmt->close();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($status !== 'Active') {
        $error = 'Voting is not open for this election.';
    } elseif ($already_voted) {
        $error = 'You have already voted in this election.';
    } else {
        $candidate_id = (int)($_POST['candidate_id'] ?? 0);

        // The chosen candidate must belong to THIS election
        $stmt = $conn->prepare('SELECT candidate_id FROM candidates WHERE candidate_id = ? AND election_id = ?');
        $stmt->bind_param('ii', $candidate_id, $election_id);
        $stmt->execute();
        $stmt->store_result();
        $valid = $stmt->num_rows > 0;
        $stmt->close();

        if (!$valid) {
            $error = 'Please select one candidate.';
        } else {
            // voter_id comes from the session, never from the form
            $stmt = $conn->prepare('INSERT INTO votes (voter_id, election_id, candidate_id) VALUES (?, ?, ?)');
            $stmt->bind_param('iii', $voter_id, $election_id, $candidate_id);
            if ($stmt->execute()) {
                $stmt->close();
                flash_set('Your vote has been recorded successfully. Thank you for voting!');   // FR-18
                redirect('/dashboard.php');
            } else {
                // 1062 = the database rule UNIQUE (voter_id, election_id) blocked a second vote
                if ($stmt->errno === 1062) {
                    $error = 'You have already voted in this election.';
                    $already_voted = true;
                } else {
                    $error = 'Could not save your vote. Please try again.';
                }
                $stmt->close();
            }
        }
    }
}

// FR-15: candidates of this election
$candidates = [];
if ($status === 'Active' && !$already_voted) {
    $stmt = $conn->prepare('SELECT candidate_id, name, description FROM candidates WHERE election_id = ? ORDER BY name');
    $stmt->bind_param('i', $election_id);
    $stmt->execute();
    $candidates = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}

include __DIR__ . '/includes/header.php';
?>
<h1><?php echo e($election['title']); ?></h1>

<?php if ($error !== '') { ?>
  <div class="msg error"><?php echo e($error); ?></div>
<?php } ?>

<div class="card">
<?php if ($status === 'Upcoming') { ?>

  <p>Voting has not started yet. It opens on
     <strong><?php echo e(date('d M Y, h:i A', strtotime($election['start_time']))); ?></strong>.</p>

<?php } elseif ($status === 'Ended') { ?>

  <p>Voting for this election has ended.</p>

<?php } elseif ($already_voted) { ?>

  <p>You have already voted in this election. Thank you!</p>

<?php } elseif (count($candidates) === 0) { ?>

  <p>This election has no candidates yet.</p>

<?php } else { ?>

  <p>Select <strong>one</strong> candidate. Your vote is secret and cannot be changed.</p>
  <form method="post" action="" onsubmit="return confirm('Submit your vote? You cannot change it later.');">
    <input type="hidden" name="election_id" value="<?php echo (int)$election_id; ?>">
    <?php foreach ($candidates as $c) { ?>
      <label class="candidate">
        <input type="radio" name="candidate_id" value="<?php echo (int)$c['candidate_id']; ?>" required>
        <strong><?php echo e($c['name']); ?></strong>
        <?php if ($c['description'] !== null && $c['description'] !== '') { ?>
          <small><?php echo e($c['description']); ?></small>
        <?php } ?>
      </label>
    <?php } ?>
    <button class="btn btn-green" type="submit">Submit vote</button>
  </form>

<?php } ?>
</div>

<a class="btn btn-grey" href="<?php echo BASE_URL; ?>/dashboard.php">Back to dashboard</a>
<?php include __DIR__ . '/includes/footer.php'; ?>
