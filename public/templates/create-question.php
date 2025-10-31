<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dynamic Question Builder</title>
  <link rel="stylesheet" href="static/styles/question.css">
</head>
<body>
  <div class="set-meta">
    <label>Set Title
      <input type="text" id="set-title" placeholder="e.g., Marvel Basics">
    </label>
  </div>

  <section id="questions"></section>

  <div class="button-container">
    <button id="add-question">Add Question</button>
    <button id="submit-questions">Submit</button>
  </div>

  <script src="static/scripts/create-question.js"></script>
</body>
</html>
