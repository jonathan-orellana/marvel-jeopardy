<!-- /public/templates/signup.php -->

<!DOCTYPE html>
<html lang="en">

<head>
  <meta name="contributors" content="Authors: Carlos Orellana, David Nu Nu" >
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Create an Account</title>

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;800&display=swap" rel="stylesheet">

  <link rel="stylesheet" href="static/styles/header.css" />
  <link rel="stylesheet" href="static/styles/general.css" />
  <link rel="stylesheet" href="static/styles/signup.css" />
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
  <div class="login-form-container">
    <div class="title-form">Create an Account</div>
    
    <!--action: Go to index.php, command field signup_submit-->
    <form class="sign-up-form" method="post" action="index.php?command=signup_submit" novalidate>
      <div class="name-input">
        <input type="text" name="first_name" placeholder="First name" required>
        <input type="text" name="last_name" placeholder="Last Name" required>
      </div>

      <!--Show email if cookie exist-->
      <input type="email" name="email"
             placeholder="Email"
             required
             value="<?= htmlspecialchars($_COOKIE['last_email'] ?? '') ?>">

      <input type="password" name="password" placeholder="Password" required>
      <input type="password" name="confirm_password" id="password-input" placeholder="Confirm Password" required>

      <div class="sign-up-message">
        <input type="checkbox" class="terms-and-condition-checkbox" id="terms" name="terms">
        <label for="terms">
          By checking this box, you confirm that you have read and agree to the <span>Terms and Conditions.</span>
        </label>
      </div>

      <!--Show if we have some validation error or any other-->
      <?php if (!empty($errors)): ?>
        <div class="error-message">
          <?php foreach ($errors as $e): ?>
            <p style="color:red; margin-bottom:1rem; font-size:14px;"><?= htmlspecialchars($e) ?></p>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

      <button class="button sign-up-button">Sign Up</button>
    </form>
  </div>

  <div class="login-image-container">
    <img src="static/assets/marvel-comic-background-3.jpg" alt="marvel-comic-background">
  </div>
</section>
<script src="static/scripts/header.js"></script>

</body>

</html>