<!--If no session login-->
<?php
  if (!isset($_SESSION['user'])) {
    header('Location: index.php?command=login');
    exit;
  }
  ?>

<!--Styles-->
<link rel="stylesheet" href="static/styles/question.css">

<main>
  <div class="set-meta">
    <label>
      <span>Set Title</span>
      <input type="text" id="set-title" placeholder="My set">
    </label>
  </div>

  <section id="questions">
    
  </section>

  <div class="button-container">
    <button class="button" id="add-question">Add Question</button>
    <button class="button" id="submit-questions">Submit</button>
  </div>

  <!--Scripts-->
  <script src="static/scripts/create-question.js"></script>
</main>

<!--example-->
