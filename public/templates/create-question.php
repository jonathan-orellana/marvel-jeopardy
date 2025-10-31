<?php
  if (!isset($_SESSION['user_id'])) {
    header('Location: index.php?command=login');
    exit;
  }
  ?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dynamic Question Builder</title>
  <link rel="stylesheet" href="static/styles/header.css">
  <link rel="stylesheet" href="static/styles/general.css">
  <link rel="stylesheet" href="static/styles/question.css">
</head>
<body>
  <?php
  if (!isset($_SESSION['user_id'])) {
    header('Location: index.php?command=login');
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

  <div class="set-meta">
    <label>
      <span>Set Title</span>
      <input type="text" id="set-title" placeholder="My set">
    </label>
  </div>

  <section id="questions"></section>

  <div class="button-container">
    <button class="button" id="add-question">Add Question</button>
    <button class="button" id="submit-questions">Submit</button>
  </div>

  <script src="static/scripts/create-question.js"></script>
</body>
</html>
