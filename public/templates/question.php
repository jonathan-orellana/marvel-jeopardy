<?php
// public/templates/question.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['score'])) {
    $_SESSION['score'] = 0;
}

if (!isset($_SESSION['answered'])) {
    $_SESSION['answered'] = [];
}
if (!isset($_SESSION['awarded'])) {
    $_SESSION['awarded'] = [];
}

$feedback    = null;
$wasAnswered = false;
$allAnswered = false;
$revealed    = false;

$category     = $question['category']      ?? 'Unknown';
$points       = (int)($question['points']  ?? 0);
$questionType = $question['question_type'] ?? 'multiple_choice';
$setId        = (int)($question['question_set_id'] ?? 0);

$answerKey = $category . ':' . $points;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!isset($_SESSION['answered'][$setId])) {
        $_SESSION['answered'][$setId] = [];
    }
    if (!isset($_SESSION['awarded'][$setId])) {
        $_SESSION['awarded'][$setId] = [];
    }
    if (!isset($_SESSION['awarded'][$setId][$answerKey])) {
        $_SESSION['awarded'][$setId][$answerKey] = false;
    }

    $gotItRight = false;

    if ($questionType === 'response') {
        $phase = $_POST['phase'] ?? 'reveal';

        if ($phase === 'reveal') {
            $revealed = true;

        } elseif ($phase === 'grade') {
            $selfReport  = $_POST['self_report'] ?? 'incorrect';
            $gotItRight  = ($selfReport === 'correct');
            $wasAnswered = true;

            if ($gotItRight) {
                $feedback = "Correct!";
                if (!$_SESSION['awarded'][$setId][$answerKey]) {
                    $_SESSION['score'] += $points;
                    $_SESSION['awarded'][$setId][$answerKey] = true;
                }
            } else {
                $feedback = "Incorrect.";
            }

            $_SESSION['answered'][$setId][$answerKey] = true;
        }

    } else {
        $wasAnswered = true;

        if ($questionType === 'multiple_choice') {
            $userIndex = isset($_POST['answer_index']) ? (int)$_POST['answer_index'] : null;
            $correct   = $question['correct_index'];

            if ($userIndex !== null && $correct !== null && $userIndex === $correct) {
                $gotItRight = true;
            }

        } elseif ($questionType === 'true_false') {
            $userVal = $_POST['answer_tf'] ?? null;
            $correct = $question['is_true'];

            if ($userVal !== null) {
                $userBool = ($userVal === 'true');
                if ($userBool === $correct) {
                    $gotItRight = true;
                }
            }
        }

        if ($gotItRight) {
            $feedback = "Correct!";
            if (!$_SESSION['awarded'][$setId][$answerKey]) {
                $_SESSION['score'] += $points;
                $_SESSION['awarded'][$setId][$answerKey] = true;
            }
        } else {
            $feedback = "Incorrect.";
        }

        $_SESSION['answered'][$setId][$answerKey] = true;
    }

    if ($wasAnswered) {
        $answeredCount = count($_SESSION['answered'][$setId]);
        if ($answeredCount >= 25) {
            $allAnswered = true;
        }
    }
}

?>
<link rel="stylesheet" href="static/styles/question-board.css">

<main class="question-page">

  <!-- Score at bottom-left -->
  <div class="score-bottom">
    <span class="score-label">Score:</span>
    <span class="score-value"><?= (int)$_SESSION['score'] ?></span>
  </div>

  <div>

  </div>
  <!-- LEFT SIDE (QUESTION) -->
  <div class="question-side">
    <div class="question-text">
      <?= nl2br(htmlspecialchars($question['text'] ?? 'No question text')) ?>
    </div>
  </div>

  <!-- RIGHT SIDE (ANSWERS) -->
  <div class="answers-side">

    <h2 class="answers-title">Choose an answer</h2>

    <?php if ($questionType === 'multiple_choice' || $questionType === 'true_false'): ?>

      <?php if (!$wasAnswered): ?>
      <form method="post" class="answers-form">

        <?php if ($questionType === 'multiple_choice'): ?>
          <?php foreach ($question['options'] as $idx => $optText): ?>
            <label class="answer-option">
              <input type="radio" name="answer_index" value="<?= (int)$idx ?>" required>
              <span><?= htmlspecialchars($optText) ?></span>
            </label>
          <?php endforeach; ?>
        <?php endif; ?>

        <?php if ($questionType === 'true_false'): ?>
          <label class="answer-option">
            <input type="radio" name="answer_tf" value="true" required>
            <span>True</span>
          </label>
          <label class="answer-option">
            <input type="radio" name="answer_tf" value="false">
            <span>False</span>
          </label>
        <?php endif; ?>

        <button type="submit" class="check-button">Check</button>
      </form>
      <?php endif; ?>

    <?php elseif ($questionType === 'response'): ?>

      <?php if (!$revealed && !$wasAnswered): ?>
        <form method="post" class="answers-form">
          <input type="hidden" name="phase" value="reveal">
          <button type="submit" class="check-button">Reveal Answer</button>
        </form>

      <?php elseif ($revealed && !$wasAnswered): ?>
        <p class="revealed-answer">
          Correct answer:
          <strong><?= htmlspecialchars($question['answer_text'] ?? '') ?></strong>
        </p>

        <form method="post" class="answers-form">
          <input type="hidden" name="phase" value="grade">
          <button name="self_report" value="correct" class="check-button">I was correct</button>
          <button name="self_report" value="incorrect" class="check-button incorrect">I was incorrect</button>
        </form>

      <?php elseif ($wasAnswered): ?>
        <p class="revealed-answer">
          Correct answer:
          <strong><?= htmlspecialchars($question['answer_text'] ?? '') ?></strong>
        </p>
      <?php endif; ?>

    <?php endif; ?>

    <!-- FEEDBACK -->
    <?php if ($wasAnswered): ?>
      <div class="feedback"><?= htmlspecialchars($feedback ?? '') ?></div>
    <?php endif; ?>

    <!-- CONTINUE BUTTON -->
    <?php if ($wasAnswered): ?>
      <div class="continue-area">
        <?php if ($allAnswered): ?>
          <a href="index.php?command=gameover&set_id=<?= $setId ?>">
            <button class="check-button continue-button">Continue</button>
          </a>
        <?php else: ?>
          <a href="index.php?command=play_board&set_id=<?= $setId ?>">
            <button class="check-button continue-button">Continue</button>
          </a>
        <?php endif; ?>
      </div>
    <?php endif; ?>

  </div>

</main>
