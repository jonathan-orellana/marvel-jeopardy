<!--login.php-->

<link rel="stylesheet" href="static/styles/login.css">

<section class="login">
  <div class="login-image-container">
    <img src="static/assets/marvel-comic-background.jpg" alt="marvel-comic-background">
  </div>

  <div class="login-form-container">
    <div class="title-form">
      Login
    </div>
    
    <!--POST-->
    <form class="login-form" method="post" action="index.php?command=login_submit" novalidate>
      <label for="email-input">
        Email
      </label>
      <!-- Load cookie: htmlspecialchars($_COOKIE['last_email'] ?? '')  
      -->
      <input type="email" id="email-input" name="email" required value="<?= htmlspecialchars($_COOKIE['last_email'] ?? '') ?>"
      >

      <label for="password-input">
        Password
      </label>
      <input id="password-input" type="password" name="password" required>

      <!--Show validation error or any other-->
      <?php if (!empty($errors)): ?>
        <div>
          <?php foreach ($errors as $e): ?>
            <p style="color:red; margin-bottom:8px; font-size:14px;"><?= htmlspecialchars($e) ?></p>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

      <div class="forget-password-container">
        <div></div>
        <a href="index.php?command=home">
          Forgot Password?
        </a>
      </div>
      <div class="login-button-container">
        <button class="button">
          Login
        </button>
      </div>
    </form>

    <div class="sign-up-message">
      Don't have an account?
      <a href="index.php?command=signup">
        <span>Sign Up</span>
      </a>
    </div>
  </div>
</section>

<!-- Scripts -->
<script src="static/scripts/header.js"></script>
<script src="static/scripts/login-validation.js"></script>