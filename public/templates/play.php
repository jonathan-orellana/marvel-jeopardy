<?php
// public/templates/play.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<main id="main">
  <section class="sets-list">
    <h1>Your Question Sets</h1>

    <?php if (!isset($rows) || pg_num_rows($rows) === 0): ?>
      <p>You don't have any sets yet. Create one first, then come back to play!</p>
      <p><a href="index.php?command=create_game">Create a new set</a></p>
    <?php else: ?>
      <table>
        <thead>
          <tr>
            <th>Title</th>
            <th>Questions</th>
            <th>Created</th>
            <th>Play</th>
          </tr>
        </thead>
        <tbody>
          <?php while ($row = pg_fetch_assoc($rows)): ?>
            <tr>
              <td><?= htmlspecialchars($row['title']) ?></td>
              <td><?= htmlspecialchars($row['question_count']) ?></td>
              <td><?= htmlspecialchars($row['created_at']) ?></td>
              <td>
              <a
                href="index.php?command=play_board&set_id=<?= (int)$row['id'] ?>&reset=1"
                class="btn-play-set"
              >
                Play this set
              </a>
              </td>
            </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    <?php endif; ?>
  </section>
</main>
