<?php
// public/templates/gameover.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$finalScore = isset($_SESSION['score']) ? (int)$_SESSION['score'] : 0;
?>
<main id="main">
  <section class="gameover-page" style="text-align:center; padding:40px 20px;">
    <h1>Game Over</h1>
    <p>Your final score:</p>
    <p style="font-size:2rem; font-weight:bold;"><?= $finalScore ?> points</p>

    <div style="margin-top:20px;">
      <a href="index.php?command=play">
        <button>Back to Play</button>
      </a>
      <a href="index.php?command=home" style="margin-left:10px;">
        <button>Home</button>
      </a>
    </div>
  </section>
</main>
