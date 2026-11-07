<?php
// FR-12: Admin views the results of an election (vote count for each candidate).
// FR-13: The candidate with the highest votes is shown as the winner.
// FR-19: Only totals are shown, never which voter chose which candidate.
require_once __DIR__ . '/../includes/auth.php';
require_admin();
$page_title = 'Results';

$election_id = (int)($_GET['election_id'] ?? 0);
$election = null;
$rows = [];
$total_votes = 0;
$status = '';
$winner_text = '';

if ($election_id > 0) {
    $stmt = $conn->prepare('SELECT election_id, title, start_time, end_time FROM elections WHERE election_id = ?');
    $stmt->bind_param('i', $election_id);
    $stmt->execute();
    $election = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($election) {
        // Count the votes of every candidate (candidates with 0 votes are included)
        $stmt = $conn->prepare(
            'SELECT c.candidate_id, c.name, c.description, COUNT(v.vote_id) AS total
             FROM candidates c
             LEFT JOIN votes v ON v.candidate_id = c.candidate_id
             WHERE c.election_id = ?
             GROUP BY c.candidate_id, c.name, c.description
             ORDER BY total DESC, c.name ASC'
        );
        $stmt->bind_param('i', $election_id);
        $stmt->execute();
        $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        foreach ($rows as $r) {
            $total_votes += (int)$r['total'];
        }

        $status = election_status($election['start_time'], $election['end_time']);

        // Rows are sorted by votes, so the first row has the highest count
        if ($total_votes === 0) {
            $winner_text = 'No votes have been cast yet.';
        } else {
            $max = (int)$rows[0]['total'];
            $leaders = [];
            foreach ($rows as $r) {
                if ((int)$r['total'] === $max) {
                    $leaders[] = $r['name'];
                }
            }
            if (count($leaders) > 1) {
                $label = 'Tie between';
            } elseif ($status === 'Ended') {
                $label = 'Winner';
            } else {
                $label = 'Current leader';
            }
            $winner_text = $label . ': ' . implode(', ', $leaders) . ' (' . $max . ' votes)';
        }
    }
}

$auto_refresh = isset($_GET['refresh']);
$extra_head = ($election && $auto_refresh) ? '<meta http-equiv="refresh" content="10">' : '';

$all_elections = null;
if (!$election) {
    $all_elections = $conn->query('SELECT election_id, title, start_time, end_time FROM elections ORDER BY start_time DESC');
}

include __DIR__ . '/admin_header.php';
?>
<h1>Results</h1>

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
          <?php $st = election_status($row['start_time'], $row['end_time']); ?>
          <tr>
            <td><?php echo (int)$row['election_id']; ?></td>
            <td><?php echo e($row['title']); ?></td>
            <td><span class="badge s-<?php echo e($st); ?>"><?php echo e($st); ?></span></td>
            <td><a href="<?php echo BASE_URL; ?>/admin/results.php?election_id=<?php echo (int)$row['election_id']; ?>">View results</a></td>
          </tr>
        <?php } ?>
      <?php } else { ?>
        <tr><td colspan="4">No elections yet.</td></tr>
      <?php } ?>
    </table>
    </div>
  </div>

<?php } else { ?>

  <p>Election: <strong><?php echo e($election['title']); ?></strong>
     <span class="badge s-<?php echo e($status); ?>"><?php echo e($status); ?></span>
     - <a href="<?php echo BASE_URL; ?>/admin/results.php">change election</a></p>
  <?php if ($status !== 'Ended') { ?>
    <p>This election has not ended yet, so these are the current counts.</p>
  <?php } ?>

  <div class="card">
    <p class="winner"><?php echo e($winner_text); ?></p>
    <p>Total votes cast: <strong><?php echo $total_votes; ?></strong></p>

    <div class="table-wrap">
    <table>
      <tr><th>Rank</th><th>Candidate</th><th>Description</th><th>Votes</th><th>Share</th></tr>
      <?php if (count($rows) > 0) { ?>
        <?php foreach ($rows as $i => $r) { ?>
          <?php $percent = $total_votes > 0 ? round(((int)$r['total'] / $total_votes) * 100, 1) : 0; ?>
          <tr>
            <td><?php echo $i + 1; ?></td>
            <td><?php echo e($r['name']); ?></td>
            <td><?php echo e($r['description']); ?></td>
            <td><?php echo (int)$r['total']; ?></td>
            <td>
              <div class="bar-wrap"><div class="bar" style="width:<?php echo $percent; ?>%"></div></div>
              <?php echo $percent; ?>%
            </td>
          </tr>
        <?php } ?>
      <?php } else { ?>
        <tr><td colspan="5">This election has no candidates yet.</td></tr>
      <?php } ?>
    </table>
    </div>

    <?php if ($auto_refresh) { ?>
      <a class="btn btn-grey" href="<?php echo BASE_URL; ?>/admin/results.php?election_id=<?php echo $election_id; ?>">Stop auto-refresh</a>
    <?php } else { ?>
      <a class="btn" href="<?php echo BASE_URL; ?>/admin/results.php?election_id=<?php echo $election_id; ?>&refresh=1">Auto-refresh every 10 seconds</a>
    <?php } ?>
  </div>

<?php } ?>
<?php include __DIR__ . '/admin_footer.php'; ?>
