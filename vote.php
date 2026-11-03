<?php
// vote.php - show candidates and record one vote (FR-15 to FR-20)
require_once __DIR__ . '/includes/auth.php';
require_voter();                                   // must be logged in

$voter_id    = $_SESSION['voter_id'];              // never taken from the form (FR-19 / safety)
$election_id = (int)($_GET['election_id'] ?? $_POST['election_id'] ?? 0);

$error     = '';
$success   = false;
$show_form = true;

// 1. Load the election and ask MySQL if it is active right now (FR-20)
$stmt = $conn->prepare(
    'SELECT election_id, title, start_time, end_time,
            (start_time <= NOW() AND end_time >= NOW()) AS is_active,
            (start_time > NOW()) AS not_started
     FROM elections WHERE election_id = ?'
);
$stmt->bind_param('i', $election_id);
$stmt->execute();
$election = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$election) {
    $error     = 'Election not found.';
    $show_form = false;
} else {
    // 2. Has this voter already voted in this election? (FR-17, layer 1)
    $stmt = $conn->prepare('SELECT vote_id FROM votes WHERE voter_id = ? AND election_id = ?');
    $stmt->bind_param('ii', $voter_id, $election_id);
    $stmt->execute();
    $stmt->store_result();
    $already_voted = ($stmt->num_rows > 0);
    $stmt->close();

    // 3. Decide what to show, in this order
    if (!$election['is_active']) {
        $error     = $election['not_started'] ? 'This election has not started yet.' : 'This election has ended.';
        $show_form = false;
    } elseif ($already_voted) {
        $error     = 'You have already voted in this election.';
        $show_form = false;
    }

    // 4. Form submitted: save the vote
    if ($show_form && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $candidate_id = (int)($_POST['candidate_id'] ?? 0);

        // The chosen candidate must belong to THIS election
        $stmt = $conn->prepare('SELECT candidate_id FROM candidates WHERE candidate_id = ? AND election_id = ?');
        $stmt->bind_param('ii', $candidate_id, $election_id);
        $stmt->execute();
        $stmt->store_result();
        $valid_candidate = ($stmt->num_rows > 0);
        $stmt->close();

        if (!$valid_candidate) {
            $error = 'Please select one candidate.';
        } else {
            try {
                $stmt = $conn->prepare('INSERT INTO votes (voter_id, election_id, candidate_id) VALUES (?, ?, ?)');
                $stmt->bind_param('iii', $voter_id, $election_id, $candidate_id);
                $stmt->execute();
                $stmt->close();
                $success   = true;                 // FR-18
                $show_form = false;
            } catch (mysqli_sql_exception $e) {
                // FR-17, layer 2: the UNIQUE (voter_id, election_id) rule in the database
                if ($e->getCode() == 1062) {
                    $error = 'You have already voted in this election.';
                } else {
                    $error = 'Could not record your vote. Please try again.';
                }
                $show_form = false;
            }
        }
    }
}

// 5. Load candidates for the form (FR-15)
$candidates = [];
if ($show_form) {
    $stmt = $conn->prepare('SELECT candidate_id, name, description FROM candidates WHERE election_id = ? ORDER BY name');
    $stmt->bind_param('i', $election_id);
    $stmt->execute();
    $candidates = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}

$page_title = 'Vote';
include __DIR__ . '/includes/header.php';
?>

<?php if ($election): ?>
    <h1><?php echo htmlspecialchars($election['title']); ?></h1>
<?php else: ?>
    <h1>Vote</h1>
<?php endif; ?>

<?php if ($success): ?>
    <div class="alert alert-success">Your vote has been recorded. Thank you for voting.</div>
<?php endif; ?>

<?php if ($error !== ''): ?>
    <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>

<?php if ($show_form): ?>
    <?php if (empty($candidates)): ?>
        <div class="card">No candidates have been added to this election yet.</div>
    <?php else: ?>
        <p class="lead">Select one candidate and submit. You cannot change your vote afterwards.</p>
        <form method="post" action="" class="card">
            <input type="hidden" name="election_id" value="<?php echo (int)$election_id; ?>">

            <?php foreach ($candidates as $c): ?>
                <label class="candidate">
                    <input type="radio" name="candidate_id" value="<?php echo (int)$c['candidate_id']; ?>" required>
                    <span>
                        <span class="c-name"><?php echo htmlspecialchars($c['name']); ?></span><br>
                        <span class="c-desc"><?php echo htmlspecialchars($c['description'] ?? ''); ?></span>
                    </span>
                </label>
            <?php endforeach; ?>

            <button type="submit" class="btn">Submit my vote</button>
        </form>
    <?php endif; ?>
<?php endif; ?>

<p><a href="<?php echo BASE_URL; ?>/dashboard.php">&larr; Back to dashboard</a></p>

<?php include __DIR__ . '/includes/footer.php'; ?>
