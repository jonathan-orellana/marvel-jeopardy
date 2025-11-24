<?php
if (!isset($_SESSION['user'])) {
  header('Location: index.php?command=login');
  exit;
}

$errors = $result['errors'] ?? [];
?>

<link rel="stylesheet" href="static/styles/question.css">

<main>
  <div class="form-wizard">

    <div class="error-message">
      <?php if (!empty($errors)): ?>
        <?= htmlspecialchars(implode(", ", $errors)) ?>
      <?php endif; ?>
    </div>

    <!-- Wizard Steps -->
    <div class="steps-container">

      <!-- STEP 1: Ask for Set Title -->
      <section id="set-title-step" class="step current">
        <h2>Set Name</h2>

        <label>
          <span>Enter a name for your set:</span>
          <input type="text" id="set-title" placeholder="My Marvel Set">
        </label>

        <div class="button-container">
          <button class="button" id="go-to-category">Next</button>
        </div>
      </section>

      <!-- STEP 2: Pick Category -->
      <section id="category-step" class="step" style="display:none;">
        <h2>Pick a Category</h2>

        <div id="category-buttons"></div>

        <p id="category-picked" style="display:none;"></p>
      </section>

      <!-- STEP 3: Fill Questions -->
      <section id="questions-step" class="step" style="display:none;">
        <h2 id="category-title"></h2>

        <section id="questions"></section>

        <div class="button-container">
          <button class="button" id="next-category">Next Category</button>
          <button class="button" id="submit-all" style="display:none;">Submit All</button>
        </div>
      </section>

    </div> <!-- steps-container -->

    <script src="static/scripts/create-question.js"></script>
  </div>
</main>
