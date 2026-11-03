<?php
// dashboard.php - list of active elections (FR-14)
require_once __DIR__ . '/includes/auth.php';
require_voter();                                   // only logged-in voters can see this page

$voter_id = $_SESSION['voter_id'];                 // always from the session, never from a form

// Active = current time is between start_time and end_time (FR-20).
// The subquery tells us whether THIS voter has already voted (it does not show the candidate).
$sql = 'SELECT e.election_id, e.title, e.start_time, e.end_time,
               (SELECT COUNT(*) FROM votes v
                 WHERE v.election_id = e.election_id AND v.voter_id = ?) AS already_voted
        FROM elections e
        WHERE e.start_time <= NOW() AND e.end_time >= NOW()
        ORDER BY e.end_time ASC';
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $voter_id);
$stmt->execute();
$elections = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$page_title = 'Dashboard';
include __DIR__ . '/includes/header.php';
?>

<h1>Welcome, <?php echo htmlspecialchars($_SESSION['voter_name'] ?? 'Voter'); ?></h1>
<p class="lead">These elections are open for voting right now.</p>

<?php if (empty($elections)): ?>
    <div class="card">There are no active elections at the moment. Please check again later.</div>
<?php else: ?>
    <?php foreach ($elections as $el): ?>
        <div class="card election">
            <div>
                <h2><?php echo htmlspecialchars($el['title']); ?></h2>
                <p class="dates">
                    Voting closes <?php echo date('d M Y, h:i A', strtotime($el['end_time'])); ?>
                </p>
            </div>
            <?php if ($el['already_voted'] > 0): ?>
                <span class="badge">You have voted</span>
            <?php else: ?>
                <a class="btn" href="<?php echo BASE_URL; ?>/vote.php?election_id=<?php echo (int)$el['election_id']; ?>">Vote now</a>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

<?php include __DIR__ . '/includes/footer.php'; ?>
