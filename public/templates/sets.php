<link rel="stylesheet" href="static/styles/sets.css">

<section>
  <h1>Your Question Sets</h1>

  <a href="index.php?command=create_game">
    <button class="button">Create a new set</button>
  </a>

  <?php if (!$rows): ?>
    <p class="error-message">Could not load your sets right now.</p>

  <?php else: ?>
    <?php if (pg_num_rows($rows) === 0): ?>
      <p>You don’t have any sets yet. Click "Create a new set".</p>

    <?php else: ?>
      <ul>
        <?php while ($row = pg_fetch_assoc($rows)): ?>
          <li class="question-set-list">
            <strong><?= htmlspecialchars($row['title']) ?></strong>
            —
            <span><?= (int)$row['question_count'] ?> questions</span>
            —
            <a href="index.php?command=view_set&id=<?= (int)$row['id'] ?>">View</a>
          </li>
        <?php endwhile; ?>
      </ul>
    <?php endif; ?>
  <?php endif; ?>
</section>
