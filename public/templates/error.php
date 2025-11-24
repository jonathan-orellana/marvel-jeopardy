<?php if (!empty($errors)): ?>
  <div class="error-message">
    <?php foreach ($errors as $e): ?>
      <p><?= htmlspecialchars($e) ?></p>
    <?php endforeach; ?>
  </div>
<?php endif; ?>

<a href="index.php?command=sets">Back to your sets</a>
