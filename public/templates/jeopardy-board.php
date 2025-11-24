<!DOCTYPE html>
<html lang="en">

<head>
  <meta name="contributors" content="Authors: Carlos Orellana, David Nu Nu" >
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Marvel Jeopardy — Board</title>

  <link rel="stylesheet" href="static/styles/jeopardy-board.css" />
  <link rel="stylesheet" href="static/styles/general.css" />
  <link rel="stylesheet" href="static/styles/header.css" />

  <!-- jQuery Library -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
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
    <section class="jeopardy-board">
      <div class="grid" role="table" aria-label="Jeopardy board">
        <div class="category" role="columnheader">Authors</div>
        <div class="category" role="columnheader">Characters</div>
        <div class="category" role="columnheader">Movies</div>
        <div class="category" role="columnheader">Quotes</div>
        <div class="category" role="columnheader">Event</div>

        <div class="cell">100</div>
        <div class="cell">100</div>
        <div class="cell">100</div>
        <div class="cell">100</div>
        <div class="cell">100</div>

        <div class="cell">200</div>
        <div class="cell">200</div>
        <div class="cell">200</div>
        <div class="cell">200</div>
        <div class="cell">200</div>

        <div class="cell">300</div>
        <div class="cell">300</div>
        <div class="cell">300</div>
        <div class="cell">300</div>
        <div class="cell">300</div>

        <div class="cell">400</div>
        <div class="cell">400</div>
        <div class="cell">400</div>
        <div class="cell">400</div>
        <div class="cell">400</div>

        <div class="cell">500</div>
        <div class="cell">500</div>
        <div class="cell">500</div>
        <div class="cell">500</div>
        <div class="cell">500</div>
      </div>
    </section>
  </main>
<script src="static/scripts/header.js"></script>
<script src="static/scripts/play-board.js"></script>
</body>

</html>