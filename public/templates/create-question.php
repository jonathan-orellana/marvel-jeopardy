<!DOCTYPE html>
<html lang="en">

<head>
  <meta name="contributors" content="Authors: Carlos Orellana, David Nu Nu" >
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Create Question</title>

  <link rel="stylesheet" href="static/styles/general.css" />
  <link rel="stylesheet" href="static/styles/question.css" />
  <link rel="stylesheet" href="static/styles/header.css">

  <style>
    .sr-only {
      position:absolute; width:1px; height:1px; padding:0; margin:-1px;
      overflow:hidden; clip:rect(0,0,0,0); white-space:nowrap; border:0;
    }
  </style>
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
  <section class="question-container">
    <div class="question-box">
      <h2 id="q1-heading">Question 1</h2>

      <h3 id="qtype-heading">Question Type</h3>
      <div class="dropdown">
        <button
          class="dropdown-btn"
          type="button"
          id="qtype-button"
          aria-haspopup="listbox"
          aria-expanded="false"
          aria-labelledby="qtype-heading qtype-button"
          aria-controls="qtype-list"
        >
          <span id="qtype-selected">Select one</span>
          <svg class="dropdown-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640" aria-hidden="true" focusable="false">
            <path fill="#c8c8c8"
              d="M300.3 440.8C312.9 451 331.4 450.3 343.1 438.6L471.1 310.6C480.3 301.4 483 287.7 478 275.7C473 263.7 461.4 256 448.5 256L192.5 256C179.6 256 167.9 263.8 162.9 275.8C157.9 287.8 160.7 301.5 169.9 310.6L297.9 438.6L300.3 440.8z" />
          </svg>
        </button>

        <!-- Options list with roles -->
        <div class="dropdown-content" id="qtype-list" role="listbox" aria-labelledby="qtype-heading">
          <div class="option" role="option" tabindex="0" aria-selected="false">Multiple Choice</div>
          <div class="option" role="option" tabindex="0" aria-selected="false">True or False</div>
          <div class="option" role="option" tabindex="0" aria-selected="false">Response</div>
        </div>
      </div>

      <h3 id="question-heading">Question</h3>
      <label class="sr-only" for="questionText">Question text</label>
      <textarea id="questionText" class="question-input" name="questionText"></textarea>

      <h3>Options</h3>

      <!-- Option 1 -->
      <div class="option-input">
        <label class="circle-check" for="opt1-correct" aria-label="Mark option 1 as correct">
          <input id="opt1-correct" type="checkbox" />
          <span class="circle" aria-hidden="true"></span>
        </label>
        <label class="sr-only" for="opt1-text">Option 1 text</label>
        <input id="opt1-text" class="text-input" type="text" name="opt1-text" />
      </div>

      <!-- Option 2 -->
      <div class="option-input">
        <label class="circle-check" for="opt2-correct" aria-label="Mark option 2 as correct">
          <input id="opt2-correct" type="checkbox" />
          <span class="circle" aria-hidden="true"></span>
        </label>
        <label class="sr-only" for="opt2-text">Option 2 text</label>
        <input id="opt2-text" class="text-input" type="text" name="opt2-text" />
      </div>

      <!-- Option 3 -->
      <div class="option-input">
        <label class="circle-check" for="opt3-correct" aria-label="Mark option 3 as correct">
          <input id="opt3-correct" type="checkbox" />
          <span class="circle" aria-hidden="true"></span>
        </label>
        <label class="sr-only" for="opt3-text">Option 3 text</label>
        <input id="opt3-text" class="text-input" type="text" name="opt3-text" />
      </div>

      <!-- Option 4 -->
      <div class="option-input">
        <label class="circle-check" for="opt4-correct" aria-label="Mark option 4 as correct">
          <input id="opt4-correct" type="checkbox" />
          <span class="circle" aria-hidden="true"></span>
        </label>
        <label class="sr-only" for="opt4-text">Option 4 text</label>
        <input id="opt4-text" class="text-input" type="text" name="opt4-text" />
      </div>

      <div class="add-option">
        <button type="button" class="add-option-btn" aria-label="Add another option">
          <svg class="plus-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640" aria-hidden="true" focusable="false">
            <path fill="#c8c8c8"
              d="M352 128C352 110.3 337.7 96 320 96C302.3 96 288 110.3 288 128L288 288L128 288C110.3 288 96 302.3 96 320C96 337.7 110.3 352 128 352L288 352L288 512C288 529.7 302.3 544 320 544C337.7 544 352 529.7 352 512L352 352L512 352C529.7 352 544 337.7 544 320C544 302.3 529.7 288 512 288L352 288L352 128z" />
          </svg>
          <span>Add another option</span>
        </button>
      </div>
    </div>

    <div class="add-question-btn">
      <button type="button" class="add-question" aria-label="Add another question">
        <svg class="plus-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640" aria-hidden="true" focusable="false">
          <path fill="#c8c8c8"
            d="M352 128C352 110.3 337.7 96 320 96C302.3 96 288 110.3 288 128L288 288L128 288C110.3 288 96 302.3 96 320C96 337.7 110.3 352 128 352L288 352L288 512C288 529.7 302.3 544 320 544C337.7 544 352 529.7 352 512L352 352L512 352C529.7 352 544 337.7 544 320C544 302.3 529.7 288 512 288L352 288L352 128z" />
        </svg>
        <span>Add another question</span>
      </button>
    </div>
  </section>

  <script src="static/scripts/header.js"></script>
</body>
</html>
