<!DOCTYPE html>
<html lang="en">

<head>
  <meta name="contributors" content="Authors: Carlos Orellana, David Nu Nu" >
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Login</title>

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;800&display=swap" rel="stylesheet">

  <link rel="stylesheet" href="static/styles/header.css" />
  <link rel="stylesheet" href="static/styles/general.css" />
  <link rel="stylesheet" href="static/styles/login.css" />
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


<section class="login">
  <div class="login-image-container">
    <img src="static/assets/marvel-comic-background.jpg" alt="marvel-comic-background">
  </div>

  <div class="login-form-container">
    <div class="title-form">Login</div>
    
    <!--action: Go to index.php, command field login_submit-->
    <form class="login-form" method="post" action="index.php?command=login_submit" novalidate>
      <label for="email-input">Email</label>
      <input
        type="email"
        id="email-input"
        name="email"
        required
        value="<?= htmlspecialchars($_COOKIE['last_email'] ?? '') ?>"
      >

      <label for="password-input">Password</label>
      <input id="password-input" type="password" name="password" required>

      <!--Show if we have some validation error or any other-->
      <?php if (!empty($errors)): ?>
        <div>
          <?php foreach ($errors as $e): ?>
            <p style="color:red; margin-bottom:8px; font-size:14px;"><?= htmlspecialchars($e) ?></p>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

      <div class="forget-password-container">
        <div></div>
        <a href="index.php?command=home">Forgot Password?</a>
      </div>

      <div class="login-button-container">
        <button class="button">Login</button>
      </div>
    </form>

    <div class="sign-up-message">
      Don't have an account?
      <a href="index.php?command=signup"><span>Sign Up</span></a>
    </div>
  </div>
</section>

<script src="static/scripts/header.js"></script>
</body>

</html>