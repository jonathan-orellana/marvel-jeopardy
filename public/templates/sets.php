<?php
require __DIR__ . '/../../src/db.php'; 
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

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Questions</title>
  <link rel="stylesheet" href="static/styles/header.css">
  <link rel="stylesheet" href="static/styles/general.css">
  <link rel="stylesheet" href="static/styles/sets.css">
</head>
<body>
  <?php
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    ?>
    <header class="header" role="banner">
      <a href="index.php?command=home" class="logo-link" aria-label="Marvel Jeopardy Home">
        <div class="logo-container">
          <img class="logo-image" src="static/assets/marvel-logo.png" alt="MARVEL logo">
          <div class="logo-text">Jeopardy</div>
        </div>
      </a>

      <div class="header-spacer" aria-hidden="true"></div>

      <img class="menu-icon" src="static/assets/icons/menu.svg" alt="">

      <nav id="primary-nav" class="navbar" aria-label="Primary">
        <a href="index.php?command=home" class="active" aria-current="page">Home</a>
        <a href="index.php?command=sets">My Sets</a>
        <a href="index.php?command=play">Play</a>
        <a href="index.php?command=about">About</a>

        <!--if user login-->
        <?php if (isset($_SESSION['user'])): ?>
          <a href="index.php?command=logout" class="login-link">Logout</a>
        <?php else: ?>
          <a href="index.php?command=login" class="login-link">Login</a>
        <?php endif; ?>
      </nav>
    </header>

    <section>
      <h1>Your Question Sets</h1>
      <a href="index.php?command=create_game">
        <button class="button">
         Create a new set
        </button>
      </a>
      <ul>
      <?php while ($row = pg_fetch_assoc($res)): ?>
        <li class="question-set-list">
          <strong><?= htmlspecialchars($row['title']) ?></strong>
          — <a href="index.php?command=view_set&id=<?= (int)$row['id'] ?>">View</a>
        </li>
      <?php endwhile; ?>
      </ul>
    </section>

</body>
</html>

