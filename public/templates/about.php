<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="contributors" content="Authors: Carlos Orellana, David Nu Nu" >
  <title>About — Marvel Jeopardy</title>
  <link rel="stylesheet" href="../styles/header.css" />
  <link rel="stylesheet" href="../styles/general.css" />
  <link rel="stylesheet" href="../styles/about.css" />
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

  <main id="main">
    <section class="about-hero" role="img" aria-label="Marvel collage background with characters"></section>

    <section class="about-panel" aria-labelledby="about-title">
      <div class="container">
        <h1 id="about-title" class="title-xl">ABOUT MARVEL JEOPARDY</h1>

        <article class="section-block" aria-labelledby="who-heading" tabindex="0">
          <h2 id="who-heading" class="eyebrow">TEAM</h2>
          <h3 class="heading">Who Made the Game</h3>
          <p class="dev-names"><strong>DAVID NU NU</strong> &nbsp;•&nbsp; <strong>CARLOS QUINTANILLA</strong></p>
        </article>

        <article class="section-block" aria-labelledby="how-heading" tabindex="0">
          <h2 id="how-heading" class="eyebrow">HOW TO PLAY</h2>
          <h3 class="heading">Quick Guide</h3>
          <ol class="steps">
            <li><strong>Teams:</strong> Two teams. Team A starts; turns alternate.</li>
            <li><strong>Timer:</strong> 30 seconds per question.</li>
            <li><strong>Pick:</strong> On your turn, select a <em>category</em> and <em>value</em> (100–500).</li>
            <li><strong>Answer:</strong> Submit the Multiple Choice or True/False response.</li>
            <li><strong>Score & Lock:</strong> Apply the result, the tile locks, switch turns.</li>
          </ol>
        </article>

        <article class="section-block" aria-labelledby="points-heading" tabindex="0">
          <h2 id="points-heading" class="eyebrow">SCORING</h2>
          <h3 class="heading">Points</h3>
          <ul class="tight">
            <li><strong>Correct:</strong> +points</li>
            <li><strong>Wrong:</strong> −points</li>
            <li><strong>Timeout / Skip:</strong> −½ of the tile’s value</li>
            <li><strong>Tiers:</strong> 100, 200, 300, 400, 500</li>
          </ul>
        </article>

        <article class="section-block" aria-labelledby="timer-heading" tabindex="0">
          <h2 id="timer-heading" class="eyebrow">TIMER</h2>
          <h3 class="heading">Pace of Play</h3>
          <ul class="tight">
            <li><strong>Turn Order:</strong> Team A starts; alternate after each answer or timeout.</li>
            <li><strong>Duration:</strong> 30 seconds per question.</li>
            <li><strong>Timeout:</strong> Deduct ½ of the tile’s value and switch the turn.</li>
          </ul>
        </article>

        <article class="section-block" aria-labelledby="license-heading" tabindex="0">
          <h2 id="license-heading" class="eyebrow">LICENSE</h2>
          <h3 class="heading">MIT License</h3>
          <p>© 2025 The Marvel Jeopardy Team. Released under the <strong>MIT License</strong>. Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files to deal in the software without restriction, subject to the license notice and copyright disclaimer.</p>
        </article>
      </div>
    </section>
  </main>
  <script src="static/scripts/header.js"></script>
</body>
</html>
