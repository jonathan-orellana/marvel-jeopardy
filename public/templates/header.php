<!DOCTYPE html>
<html lang="en">

<head>
  <meta name="contributors" content="Authors: Carlos Orellana, David Nu Nu">
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <title>Marvel Jeopardy</title>

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link
    href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap"
    rel="stylesheet">

  <!-- Styles -->
  <link rel="stylesheet" href="static/styles/general.css">
  <link rel="stylesheet" href="static/styles/header.css">
  <link rel="stylesheet" href="static/styles/footer.css">
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

      <!--If session-->
      <?php if (isset($_SESSION['user'])): ?>
        <a href="index.php?command=logout" class="login-link">
          Logout
        </a>
      <?php else: ?>
        <a href="index.php?command=login" class="login-link">
          Login
        </a>
      <?php endif; ?>
    </nav>
  </header>
