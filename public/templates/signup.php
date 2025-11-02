<!-- signup.php -->

<link rel="stylesheet" href="static/styles/signup.css" />

<section class="login">
  <div class="login-form-container">
    <div class="title-form">Create an Account</div>
    
    <!-- POST -->
    <form class="sign-up-form" method="post" action="index.php?command=signup_submit" novalidate>
      <div class="name-input">
        <input type="text" name="first_name" placeholder="First name" required>
        <input type="text" name="last_name" placeholder="Last Name" required>
      </div>

      <!-- Load cookie: htmlspecialchars($_COOKIE['last_email'] ?? '')  
      -->
      <input type="email" name="email" placeholder="Email" required value="<?= htmlspecialchars($_COOKIE['last_email'] ?? '') ?>">

      <input type="password" name="password" placeholder="Password" required>
      <input type="password" name="confirm_password" id="password-input" placeholder="Confirm Password" required>

      <div class="sign-up-message">
        <input type="checkbox" class="terms-and-condition-checkbox" id="terms" name="terms">
        <label for="terms">
          By checking this box, you confirm that you have read and agree to the <span>Terms and Conditions.</span>
        </label>
      </div>

      <!--Show validation error or any other-->
      <?php if (!empty($errors)): ?>
        <div class="error-message">
          <?php foreach ($errors as $e): ?>
            <span class="error-message-text">
              <?= htmlspecialchars($e) ?>
            </span>
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

<!-- Scripts -->
<script src="static/scripts/header.js"></script>
