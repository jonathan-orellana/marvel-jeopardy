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

<section>
  <?php if (!empty($errors)): ?>
    <div class="error-message">
      <?php foreach ($errors as $e): ?><p><?= htmlspecialchars($e) ?></p><?php endforeach; ?>
    </div>
    <a href="index.php?command=sets">Back to your sets</a>
    <?php return; ?>
  <?php endif; ?>

  <h1>Set: <?= htmlspecialchars($setData['title']) ?></h1>
  <div class="back-to-set"><a href="index.php?command=sets">Back to all sets</a></div>

  <?php foreach ($grouped as $cat => $qs): ?>
    <h2 class="category-heading">Category: <?= htmlspecialchars(ucfirst($cat)) ?></h2>

    <ul class="question-list category-block">
      <?php foreach ($qs as $q): ?>
        <?php
          $qid = (int)$q['id'];
          $qtype = $q['question_type'];
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

          <div class="type-row">
            <label class="field-label">
              Type:
              <select class="q-type">
                <option value="multipleChoice" <?= $qtype==='multipleChoice'?'selected':'' ?>>Multiple Choice</option>
                <option value="trueFalse" <?= $qtype==='trueFalse'?'selected':'' ?>>True or False</option>
                <option value="response" <?= $qtype==='response'?'selected':'' ?>>Response</option>
              </select>
            </label>

            <span class="points-badge"><?= $points ?> pts</span>
          </div>

          <label class="field-label">
            Question:
            <input type="text" class="input-full q-prompt" value="<?= htmlspecialchars($prompt) ?>">
          </label>

          <div class="answer-area">
            <?php if ($qtype === 'multipleChoice'): ?>
              <div class="section mc-section">
                <?php for ($i=0; $i<4; $i++): ?>
                  <div class="mc-option">
                    <input type="radio" name="correctIndex-<?= $qid ?>" value="<?= $i ?>" <?= $ci===$i?'checked':'' ?>>
                    <span>Option <?= $i+1 ?>:</span>
                    <input type="text" class="input-option mc-opt" value="<?= htmlspecialchars($opts[$i] ?? '') ?>">
                  </div>
                <?php endfor; ?>
              </div>

            <?php elseif ($qtype === 'trueFalse'): ?>
              <div class="section tf-section">
                <label><input type="radio" name="correctBool-<?= $qid ?>" value="1" <?= $cb===true?'checked':'' ?>> True</label>
                <label><input type="radio" name="correctBool-<?= $qid ?>" value="0" <?= $cb===false?'checked':'' ?>> False</label>
              </div>

            <?php elseif ($qtype === 'response'): ?>
              <div class="section resp-section">
                <label>
                  Correct answer:
                  <input type="text" class="input-full resp-text" value="<?= htmlspecialchars($ct) ?>">
                </label>
              </div>
            <?php endif; ?>
          </div>
        </li>
      <?php endforeach; ?>
    </ul>
  <?php endforeach; ?>

  <div class="update-buttons">
    <button id="save-all" class="btn-save">Save All</button>
  </div>
</section>

<script src="static/scripts/view-set.js"></script>
