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

  <link rel="stylesheet" href="../styles/header.css" />
  <link rel="stylesheet" href="../styles/general.css" />
  <link rel="stylesheet" href="../styles/signup.css" />
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
    <a href="./login.html" class="login-link">Login</a>
  </nav>
</header>


<section class="login">
  <div class="login-form-container">
    <div class="title-form">Create an Account</div>

    <!--Show if we have some validation error or any other-->
    <?php if (!empty($errors)): ?>
      <div style="color:red; margin-bottom:8px;">
        <?php foreach ($errors as $e): ?>
          <p><?= htmlspecialchars($e) ?></p>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
    
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

      <button class="button">Sign Up</button>
    </form>
  </div>

  <div class="login-image-container">
    <img src="../assets/marvel-comic-background-3.jpg" alt="marvel-comic-background">
  </div>
</section>

</body>

</html>