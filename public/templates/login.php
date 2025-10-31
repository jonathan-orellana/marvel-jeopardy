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

  <link rel="stylesheet" href="../styles/header.css" />
  <link rel="stylesheet" href="../styles/general.css" />
  <link rel="stylesheet" href="../styles/login.css" />
</head>

<body>

<header class="header" role="banner">
  <a class="skip-link" href="#main">Skip to main content</a>

  <a href="./home.html" class="logo-link" aria-label="Marvel Jeopardy Home">
        <div class="logo-container logo-frame">
          <img class="logo-image" src="../assets/marvel-logo.png" alt="MARVEL logo">
          <div class="logo-text">Jeopardy</div>
        </div>
      </a>

  <div class="header-spacer" aria-hidden="true"></div>

  <input type="checkbox" id="nav-toggle" aria-label="Toggle navigation">
  <label for="nav-toggle" class="menu-icon" aria-controls="primary-nav" aria-expanded="false">
    <span class="bar"></span><span class="sr-only">Menu</span>
  </label>

  <nav id="primary-nav" class="navbar" aria-label="Primary">
    <a href="./home.html">Home</a>
    <a href="./jeopardy-board.html">Play</a>
    <a href="./about.html">About</a>
    <a href="./login.html" class="login-link active" aria-current="page">Login</a>
  </nav>
</header>


<section class="login">
  <div class="login-image-container">
    <img src="../assets/marvel-comic-background.jpg" alt="marvel-comic-background">
  </div>

  <div class="login-form-container">
    <div class="title-form">Login</div>

    <!--Show if we have some validation error or any other-->
    <?php if (!empty($errors)): ?>
      <div style="color:red; margin-bottom:8px;">
        <?php foreach ($errors as $e): ?>
          <p><?= htmlspecialchars($e) ?></p>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
    
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

      <div class="forget-password-container" style="margin-top:8px;">
        <div></div>
        <a href="index.php?command=home">Forgot Password?</a>
      </div>

      <div style="margin-top:12px;">
        <button class="button">Login</button>
      </div>
    </form>

    <div class="sign-up-message" style="margin-top:12px;">
      Don't have an account?
      <a href="index.php?command=signup"><span>Sign Up</span></a>
    </div>
  </div>
</section>

</body>

</html>