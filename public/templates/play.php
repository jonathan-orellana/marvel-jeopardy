<link rel="stylesheet" href="static/styles/sets.css">
<link rel="stylesheet" href="static/styles/play.css">
<script defer src="static/scripts/sets.js"></script>

<section>
  <h2 class="title">Available Games</h2>

  <a href="index.php?command=create_game">
    <button class="button">Create a new game</button>
  </a>

  <?php if (!$rows): ?>
    <p class="error-message">Could not load available games right now.</p>

  <?php else: ?>
    <?php if (pg_num_rows($rows) === 0): ?>
      <p>No games available. Click “Create a new game”.</p>

    <?php else: ?>
      <ul>
        <?php while ($row = pg_fetch_assoc($rows)): ?>
          <li class="question-set-list">
            <strong><?= htmlspecialchars($row['title']) ?></strong>
            —
            <span><?= (int)$row['question_count'] ?> questions</span>
            
            <a 
              href="index.php?command=play_board&set_id=<?= (int)$row['id'] ?>&reset=1"
              class="play-game-btn"
            >
            <button class="button play-button">
              Play
            </button>
            </a>
          </li>
        <?php endwhile; ?>
      </ul>
    <?php endif; ?>
  <?php endif; ?>
</section>
