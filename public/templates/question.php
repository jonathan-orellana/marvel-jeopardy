<?php
// public/templates/question.php
// Expects $question (array) from controller.

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

$feedback      = null;
$wasAnswered   = false;  // means fully graded (points decided)
$allAnswered   = false;
$revealed      = false;  // for response-type “reveal answer” phase

$category      = $question['category']      ?? 'Unknown';
$points        = (int)($question['points']  ?? 0);
$questionType  = $question['question_type'] ?? 'multiple_choice';
$setId         = (int)($question['question_set_id'] ?? 0);

// Key to track this specific question (per set) in the session
$answerKey = $category . ':' . $points;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // make sure sub-arrays exist
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
        // Two-phase: reveal, then self-report
        $phase = $_POST['phase'] ?? 'reveal';

        if ($phase === 'reveal') {
            // Just show the correct answer, no scoring yet
            $revealed = true;
            // no points, no answered, no feedback yet

        } elseif ($phase === 'grade') {
            // User clicked "I was correct" or "I was incorrect"
            $selfReport = $_POST['self_report'] ?? 'incorrect';
            $gotItRight = ($selfReport === 'correct');
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

            // Mark question finished
            $_SESSION['answered'][$setId][$answerKey] = true;
        }

    } else {
        // Single-phase grading for multiple_choice / true_false
        $wasAnswered = true;

        if ($questionType === 'multiple_choice') {
            $userIndex = isset($_POST['answer_index']) ? (int)$_POST['answer_index'] : null;
            $correct   = $question['correct_index'];

            if ($userIndex !== null && $correct !== null && $userIndex === $correct) {
                $gotItRight = true;
            }

        } elseif ($questionType === 'true_false') {
            $userVal = $_POST['answer_tf'] ?? null;  // 'true' or 'false'
            $correct = $question['is_true'];         // bool

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

        // Mark question finished
        $_SESSION['answered'][$setId][$answerKey] = true;
    }

    // Check if game is over (25 questions)
    if ($wasAnswered) {
        $answeredCount = count($_SESSION['answered'][$setId]);
        $TOTAL_QUESTIONS_IN_SET = 25;
        if ($answeredCount >= $TOTAL_QUESTIONS_IN_SET) {
            $allAnswered = true;
        }
    }
}
?>
<main id="main">
  <section class="question-page">
    <!-- Score at top -->
    <div class="score-bar" style="font-weight:bold; font-size:1.1rem; margin:20px 0 10px;">
      Total Score: <?= (int)$_SESSION['score'] ?> points
    </div>

    <div class="question-meta" style="margin-bottom:15px; color:#555;">
      Category:
      <strong><?= htmlspecialchars(ucfirst($category)) ?></strong>
      &nbsp;|&nbsp;
      Value:
      <strong><?= htmlspecialchars($points) ?></strong> points
      &nbsp;|&nbsp;
      Type:
      <strong><?= htmlspecialchars($questionType) ?></strong>
    </div>

    <!-- Question + controls on the left -->
    <div class="question-box" style="border:1px solid #ccc; padding:15px; box-sizing:border-box; max-width:700px;">
      <div class="question-text" style="font-size:1.1rem; margin-bottom:15px;">
        <?= nl2br(htmlspecialchars($question['text'] ?? 'No question text')) ?>
      </div>

      <?php if ($questionType === 'multiple_choice' || $questionType === 'true_false'): ?>

        <?php if (!$wasAnswered): ?>
          <!-- Show options only BEFORE grading -->
          <form method="post">
            <?php if ($questionType === 'multiple_choice'): ?>
              <?php foreach ($question['options'] as $idx => $optText): ?>
                <label style="display:block; margin-bottom:8px;">
                  <input
                    type="radio"
                    name="answer_index"
                    value="<?= (int)$idx ?>"
                    required
                  >
                  <?= htmlspecialchars($optText) ?>
                </label>
              <?php endforeach; ?>

            <?php elseif ($questionType === 'true_false'): ?>
              <label style="display:block; margin-bottom:8px;">
                <input type="radio" name="answer_tf" value="true" required> True
              </label>
              <label style="display:block; margin-bottom:8px;">
                <input type="radio" name="answer_tf" value="false"> False
              </label>
            <?php endif; ?>

            <br>
            <button type="submit">Submit</button>
          </form>
        <?php endif; ?>

      <?php elseif ($questionType === 'response'): ?>

        <?php if (!$revealed && !$wasAnswered): ?>
          <!-- Phase 1: Reveal answer button -->
          <form method="post">
            <input type="hidden" name="phase" value="reveal">
            <button type="submit">Reveal Answer</button>
          </form>

        <?php elseif ($revealed && !$wasAnswered): ?>
          <!-- Phase 2: show answer + self-report buttons -->
          <p style="margin-top:10px;">
            Correct answer:
            <strong><?= htmlspecialchars($question['answer_text'] ?? '') ?></strong>
          </p>

          <form method="post" style="margin-top:10px;">
            <input type="hidden" name="phase" value="grade">
            <button type="submit" name="self_report" value="correct">I was correct</button>
            <button type="submit" name="self_report" value="incorrect" style="margin-left:10px;">I was incorrect</button>
          </form>

        <?php elseif ($wasAnswered): ?>
          <!-- After grading: show answer, no buttons -->
          <p style="margin-top:10px;">
            Correct answer:
            <strong><?= htmlspecialchars($question['answer_text'] ?? '') ?></strong>
          </p>
        <?php endif; ?>

      <?php endif; ?>

      <?php if ($wasAnswered): ?>
        <div class="feedback" style="font-weight:bold; margin-top:10px;">
          <?= htmlspecialchars($feedback ?? '') ?>
        </div>
      <?php endif; ?>
    </div>

    <!-- Continue button: back to board or to Game Over -->
    <?php if ($wasAnswered): ?>
      <div class="back-link" style="margin-top:20px;">
        <?php if ($allAnswered): ?>
          <a href="index.php?command=gameover&set_id=<?= htmlspecialchars($setId) ?>">
            <button>Continue</button>
          </a>
        <?php else: ?>
          <a href="index.php?command=play_board&set_id=<?= htmlspecialchars($setId) ?>">
            <button>Continue</button>
          </a>
        <?php endif; ?>
      </div>
    <?php endif; ?>
  </section>
</main>
