<?php
// FR-14: A logged-in voter sees the list of active elections.
require_once __DIR__ . '/includes/auth.php';
require_voter();
$page_title = 'Dashboard';

$voter_id = (int)$_SESSION['voter_id'];

// Elections that are running right now, and whether this voter already voted in each
$stmt = $conn->prepare(
    'SELECT el.election_id, el.title, el.start_time, el.end_time,
            (SELECT COUNT(*) FROM votes v WHERE v.election_id = el.election_id AND v.voter_id = ?) AS has_voted
     FROM elections el
     WHERE NOW() BETWEEN el.start_time AND el.end_time
     ORDER BY el.end_time ASC'
);
$stmt->bind_param('i', $voter_id);
$stmt->execute();
$elections = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

include __DIR__ . '/includes/header.php';
?>
<h1>Welcome, <?php echo e($_SESSION['voter_name'] ?? 'Voter'); ?></h1>

<div class="card">
  <h2>Active elections</h2>
  <?php if (count($elections) === 0) { ?>
    <p>There are no active elections right now. Please check again later.</p>
  <?php } else { ?>
    <div class="table-wrap">
    <table>
      <tr><th>Election</th><th>Ends on</th><th>Your status</th><th>Action</th></tr>
      <?php foreach ($elections as $el) { ?>
        <tr>
          <td><?php echo e($el['title']); ?></td>
          <td><?php echo e(date('d M Y, h:i A', strtotime($el['end_time']))); ?></td>
          <td>
            <?php if ((int)$el['has_voted'] > 0) { ?>
              <span class="badge badge-green">Voted</span>
            <?php } else { ?>
              <span class="badge badge-orange">Not voted yet</span>
            <?php } ?>
          </td>
          <td>
            <?php if ((int)$el['has_voted'] > 0) { ?>
              -
            <?php } else { ?>
              <a class="btn btn-green" href="<?php echo BASE_URL; ?>/vote.php?election_id=<?php echo (int)$el['election_id']; ?>">Vote now</a>
            <?php } ?>
          </td>
        </tr>
      <?php } ?>
    </table>
    </div>
  <?php } ?>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
