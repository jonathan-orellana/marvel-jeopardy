<?php
require __DIR__ . '/../../src/db.php';
if (!$db) { echo "<p>DB connection not available.</p>"; return; }

if (!isset($_SESSION['user_id'])) {
  header('Location: ../index.php?command=login');
  exit;
}

$uid = $_SESSION['user_id'];
$set_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($set_id <= 0) { echo "<p>Invalid set id.</p>"; return; }

$set_res = pg_query_params($db,
  "SELECT id, title, created_at FROM question_set WHERE id = $1 AND user_id = $2",
  [$set_id, $uid]
);
if (!$set_res || pg_num_rows($set_res) === 0) { echo "<p>Set not found.</p>"; return; }
$set = pg_fetch_assoc($set_res);

$q_res = pg_query_params($db,
  "SELECT id, type, prompt, options, correct_index, correct_bool, correct_text
   FROM question WHERE set_id = $1 AND user_id = $2 ORDER BY id ASC",
  [$set_id, $uid]
);
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dynamic Question Builder</title>
    <link rel="stylesheet" href="static/styles/header.css">
    <link rel="stylesheet" href="static/styles/general.css">
    <link rel="stylesheet" href="static/styles/set_view.css">
  </head>
  <body>
    <?php
    if (!isset($_SESSION['user_id'])) {
      header('Location: ../index.php?command=login');
      exit;
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
      <h1>Set: <?= htmlspecialchars($set['title']) ?></h1>

      <div class="back-to-set"><a href="index.php?command=sets">Back to all sets</a></div>

      <ul class="question-list" id="question-list">
      <?php while ($q = pg_fetch_assoc($q_res)): ?>
        <?php
          $qid   = (int)$q['id'];
          $qtype = $q['type'];
          $opts  = $q['options'] ? json_decode($q['options'], true) : ["","","",""];
          if (!is_array($opts)) { $opts = ["","","",""]; }
          $ci = is_null($q['correct_index']) ? -1 : (int)$q['correct_index'];
          $cb = is_null($q['correct_bool']) ? null : (bool)$q['correct_bool'];
          $ct = $q['correct_text'] ?? '';
        ?>
        <li class="question-item" data-id="<?= $qid ?>" data-type="<?= htmlspecialchars($qtype) ?>">
          <div class="question-header"><?= htmlspecialchars($qtype) ?></div>

          <label class="field-label">
            Question:
            <input type="text" class="input-full q-prompt" value="<?= htmlspecialchars($q['prompt']) ?>">
          </label>

          <?php if ($qtype === 'Multiple Choice'): ?>
            <div class="section mc-section">
              <?php for ($i=0; $i<4; $i++): ?>
                <div class="mc-option">
                  <input type="radio" name="correctIndex-<?= $qid ?>" value="<?= $i ?>" <?= $ci===$i ? 'checked' : '' ?>>
                  <span>Option <?= $i+1 ?>:</span>
                  <input type="text" class="input-option mc-opt" value="<?= htmlspecialchars($opts[$i] ?? '') ?>">
                </div>
              <?php endfor; ?>
            </div>
          <?php elseif ($qtype === 'True or False'): ?>
            <div class="section tf-section">
              <label><input type="radio" name="correctBool-<?= $qid ?>" value="1" <?= $cb===true ? 'checked' : '' ?>> True</label>
              <label><input type="radio" name="correctBool-<?= $qid ?>" value="0" <?= $cb===false ? 'checked' : '' ?>> False</label>
            </div>
          <?php elseif ($qtype === 'Response'): ?>
            <div class="section resp-section">
              <label>
                Correct answer:
                <input type="text" class="input-full resp-text" value="<?= htmlspecialchars($ct) ?>">
              </label>
            </div>
          <?php endif; ?>

          <form action="api/questions.php" method="POST" class="delete-form">
            <input type="hidden" name="action" value="delete_question">
            <input type="hidden" name="id" value="<?= $qid ?>">
            <input type="hidden" name="redirect" value="../index.php?command=view_set&id=<?= (int)$set['id'] ?>">
            <button type="submit" class="btn-delete" onclick="return confirm('Delete this question?')">Delete</button>
          </form>
        </li>
      <?php endwhile; ?>
      </ul>

      <div class="update-buttons">
        <button id="save-all" class="btn-save">Save All</button>

        <form action="api/questions.php" method="POST" class="delete-set-form">
          <input type="hidden" name="action" value="delete_set">
          <input type="hidden" name="set_id" value="<?= (int)$set['id'] ?>">
          <input type="hidden" name="redirect" value="../index.php?command=sets">
          <button type="submit" class="btn-delete-set" onclick="return confirm('Delete the whole set? This cannot be undone.')">
            Delete This Set
          </button>
        </form>
      </div>
    </section>
    <script src="static/scripts/view-set.js"></script>
  </body>
</html>
