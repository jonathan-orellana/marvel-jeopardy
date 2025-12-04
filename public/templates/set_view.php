<?php
$errors = $errors ?? [];
$setData = $setData ?? ["id" => 0, "title" => ""];
$questions = $questions ?? [];

$grouped = [];
foreach ($questions as $q) {
  $cat = $q['category'] ?? 'uncategorized';
  $grouped[$cat][] = $q;
}

foreach ($grouped as $cat => &$qs) {
  usort($qs, function($a, $b) {
    return (int)$a['points'] <=> (int)$b['points'];
  });
}
unset($qs);
?>

<link rel="stylesheet" href="static/styles/set_view.css">

<section>
  <?php if (!empty($errors)): ?>
    <div class="error-message">
      <?php foreach ($errors as $e): ?><p><?= htmlspecialchars($e) ?></p><?php endforeach; ?>
    </div>
    <a href="index.php?command=sets">Back to your sets</a>
    <?php return; ?>
  <?php endif; ?>

  <div class="back-to-set"><a href="index.php?command=sets">Back to all sets</a></div>
  <h2 class="set-title"><?= htmlspecialchars($setData['title']) ?></h2>

  <?php foreach ($grouped as $cat => $qs): ?>
    <h3 class="category-heading">Category: <?= htmlspecialchars(ucfirst($cat)) ?></h3>

    <ul class="question-list category-block">
      <?php foreach ($qs as $q): ?>
        <?php
          $qid    = (int)$q['id'];
          $qtype  = $q['question_type'];
          $prompt = $q['text'];
          $points = (int)($q['points'] ?? 0);

          $opts = json_decode($q['options'] ?? '[]', true);
          if (!is_array($opts)) $opts = [];

          $ci = is_null($q['correct_index']) ? -1 : (int)$q['correct_index'];
          $cb = is_null($q['is_true']) ? null : (bool)$q['is_true'];
          $ct = $q['answer_text'] ?? '';
        ?>

        <li class="question-item"
            data-id="<?= $qid ?>"
            data-category="<?= htmlspecialchars($cat) ?>">

          <!-- WRAP CONTENT IN question-box TO REUSE STYLES -->
          <div class="question-box">

            <div class="type-row">
              <label class="field-label">
                Type:
                <select class="q-type question-type">
                  <option value="multipleChoice" <?= $qtype==='multipleChoice'?'selected':'' ?>>Multiple Choice</option>
                  <option value="trueFalse" <?= $qtype==='trueFalse'?'selected':'' ?>>True or False</option>
                  <option value="response" <?= $qtype==='response'?'selected':'' ?>>Response</option>
                </select>
              </label>

              <span class="points-badge question-points"><?= $points ?> pts</span>
            </div>

            <label class="field-label">
              <div>Question</div>
              <textarea type="text" class="input-full q-prompt question-text"
                     value="<?= htmlspecialchars($prompt) ?>"> <?= htmlspecialchars($prompt) ?></textarea>
            </label>

            <div class="answer-area">
              <?php if ($qtype === 'multipleChoice'): ?>
                <div class="section mc-section">
                  <?php for ($i=0; $i<4; $i++): ?>
                    <div class="mc-option">
                      <input type="radio"
                             name="correctIndex-<?= $qid ?>"
                             value="<?= $i ?>"
                             <?= $ci===$i?'checked':'' ?>>

                      <span>Option <?= $i+1 ?>:</span>

                      <input type="text"
                             class="input-option mc-opt answer-input"
                             value="<?= htmlspecialchars($opts[$i] ?? '') ?>">
                    </div>
                  <?php endfor; ?>
                </div>

              <?php elseif ($qtype === 'trueFalse'): ?>
                <!-- make this match your TF styling hooks -->
                <div class="section tf-section trueorfalse-button">
                  <label>
                    <input type="radio"
                           name="correctBool-<?= $qid ?>"
                           value="1"
                           <?= $cb===true?'checked':'' ?>>
                    True
                  </label>
                  <label>
                    <input type="radio"
                           name="correctBool-<?= $qid ?>"
                           value="0"
                           <?= $cb===false?'checked':'' ?>>
                    False
                  </label>
                </div>

              <?php elseif ($qtype === 'response'): ?>
                <div class="section resp-section">
                  <label>
                    Correct answer:
                    <input type="text"
                           class="input-full resp-text answer-input"
                           value="<?= htmlspecialchars($ct) ?>">
                  </label>
                </div>
              <?php endif; ?>
            </div>

          </div>
        </li>
      <?php endforeach; ?>
    </ul>
  <?php endforeach; ?>

  <div class="update-buttons button-container">
    <button id="save-all" class="button">Save All</button>
  </div>
</section>

<script src="static/scripts/view-set.js"></script>
