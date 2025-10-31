<?php
// /public/templates/sets.php
require __DIR__ . '/../../src/db.php';   // was require_once
if (!$db) { echo "<p>DB connection not available.</p>"; return; }

if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
if (!isset($_SESSION['user_id'])) { echo "<p>Please log in.</p>"; return; }
$uid = $_SESSION['user_id'];

$res = pg_query_params($db,
  "SELECT id, title, created_at FROM question_set WHERE user_id = $1 ORDER BY id DESC",
  [$uid]
);
?>
<h1>Your Question Sets</h1>
<p><a href="index.php?command=add_question">Create a new set</a></p>
<ul>
<?php while ($row = pg_fetch_assoc($res)): ?>
  <li>
    <strong><?= htmlspecialchars($row['title']) ?></strong>
    (ID: <?= (int)$row['id'] ?>)
    — <a href="index.php?command=view_set&id=<?= (int)$row['id'] ?>">View</a>
  </li>
<?php endwhile; ?>
</ul>
