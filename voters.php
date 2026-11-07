<?php
// FR-11: Admin views the list of registered voters.
// It shows how MANY elections each voter took part in, never WHO they voted for (FR-19).
require_once __DIR__ . '/../includes/auth.php';
require_admin();
$page_title = 'Voters';

$voters = $conn->query(
    'SELECT v.voter_id, v.name, v.email, v.created_at, COUNT(vt.vote_id) AS elections_voted
     FROM voters v
     LEFT JOIN votes vt ON vt.voter_id = v.voter_id
     GROUP BY v.voter_id, v.name, v.email, v.created_at
     ORDER BY v.created_at DESC'
);

include __DIR__ . '/admin_header.php';
?>
<h1>Registered Voters</h1>

<div class="card">
  <div class="table-wrap">
  <table>
    <tr><th>ID</th><th>Name</th><th>Email</th><th>Registered on</th><th>Elections voted in</th></tr>
    <?php if ($voters && $voters->num_rows > 0) { ?>
      <?php while ($row = $voters->fetch_assoc()) { ?>
        <tr>
          <td><?php echo (int)$row['voter_id']; ?></td>
          <td><?php echo e($row['name']); ?></td>
          <td><?php echo e($row['email']); ?></td>
          <td><?php echo e(date('d M Y, h:i A', strtotime($row['created_at']))); ?></td>
          <td><?php echo (int)$row['elections_voted']; ?></td>
        </tr>
      <?php } ?>
    <?php } else { ?>
      <tr><td colspan="5">No voters have registered yet.</td></tr>
    <?php } ?>
  </table>
  </div>
</div>
<?php include __DIR__ . '/admin_footer.php'; ?>
