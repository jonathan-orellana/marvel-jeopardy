<link rel="stylesheet" href="static/styles/sets.css">

<section>
  <h1>Your Question Sets</h1>
  <a href="index.php?command=create_game">
    <button class="button">
      Create a new set
    </button>
  </a>
  <ul>
  <?php while ($row = pg_fetch_assoc($rows)): ?>
    <li class="question-set-list">
      <strong>
        <?= htmlspecialchars($row['title']) ?>
      </strong>
      — 

      <!--Query set id is URL-->
      <a href="index.php?command=view_set&id=<?= (int)$row['id'] ?>">
        View
      </a>
    </li>
  <?php endwhile; ?>
  </ul>
</section>

