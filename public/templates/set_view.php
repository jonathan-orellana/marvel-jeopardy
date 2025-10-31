<?php
// /public/templates/set_view.php
require __DIR__ . '/../../src/db.php';
if (!$db) { echo "<p>DB connection not available.</p>"; return; }

if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
if (!isset($_SESSION['user_id'])) { echo "<p>Please log in.</p>"; return; }

$uid = $_SESSION['user_id'];
$set_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($set_id <= 0) { echo "<p>Invalid set id.</p>"; return; }

$set_res = pg_query_params($db,
  "SELECT id, title, created_at FROM question_set WHERE id = $1 AND user_id = $2",
  [$set_id, $uid]
);
if (!$set_res || pg_num_rows($set_res) === 0) { echo "<p>Set not found.</p>"; return; }
$set = pg_fetch_assoc($set_res);

$q_res = pg_query_params($db,
  "SELECT id, type, prompt, options, correct_index, correct_bool, correct_text
   FROM question WHERE set_id = $1 AND user_id = $2 ORDER BY id ASC",
  [$set_id, $uid]
);
?>

<h1>Set: <?= htmlspecialchars($set['title']) ?></h1>
<p><a href="index.php?command=sets">Back to all sets</a></p>

<ul class="question-list" id="question-list">
<?php while ($q = pg_fetch_assoc($q_res)): ?>
  <?php
    $qid   = (int)$q['id'];
    $qtype = $q['type'];
    $opts  = $q['options'] ? json_decode($q['options'], true) : ["","","",""];
    if (!is_array($opts)) { $opts = ["","","",""]; }
    $ci = is_null($q['correct_index']) ? -1 : (int)$q['correct_index'];
    $cb = is_null($q['correct_bool']) ? null : (bool)$q['correct_bool'];
    $ct = $q['correct_text'] ?? '';
  ?>
  <li class="question-item" data-id="<?= $qid ?>" data-type="<?= htmlspecialchars($qtype) ?>">
    <div class="question-header"><strong>#<?= $qid ?></strong> — <?= htmlspecialchars($qtype) ?></div>

    <label class="field-label">
      Prompt:
      <input type="text" class="input-full q-prompt" value="<?= htmlspecialchars($q['prompt']) ?>">
    </label>

    <?php if ($qtype === 'Multiple Choice'): ?>
      <div class="section mc-section">
        <?php for ($i=0; $i<4; $i++): ?>
          <div class="mc-option">
            <input type="radio" name="correctIndex-<?= $qid ?>" value="<?= $i ?>" <?= $ci===$i ? 'checked' : '' ?>>
            <span>Option <?= $i+1 ?>:</span>
            <input type="text" class="input-option mc-opt" value="<?= htmlspecialchars($opts[$i] ?? '') ?>">
          </div>
        <?php endfor; ?>
      </div>
    <?php elseif ($qtype === 'True or False'): ?>
      <div class="section tf-section">
        <label><input type="radio" name="correctBool-<?= $qid ?>" value="1" <?= $cb===true ? 'checked' : '' ?>> True</label>
        <label><input type="radio" name="correctBool-<?= $qid ?>" value="0" <?= $cb===false ? 'checked' : '' ?>> False</label>
      </div>
    <?php elseif ($qtype === 'Response'): ?>
      <div class="section resp-section">
        <label>
          Correct answer:
          <input type="text" class="input-full resp-text" value="<?= htmlspecialchars($ct) ?>">
        </label>
      </div>
    <?php endif; ?>

    <form action="api/questions.php" method="POST" class="delete-form">
      <input type="hidden" name="action" value="delete_question">
      <input type="hidden" name="id" value="<?= $qid ?>">
      <button type="submit" class="btn-delete" onclick="return confirm('Delete this question?')">Delete</button>
    </form>
  </li>
<?php endwhile; ?>
</ul>

<button id="save-all" class="btn-save">Save All</button>

<form action="api/questions.php" method="POST" class="delete-set-form">
  <input type="hidden" name="action" value="delete_set">
  <input type="hidden" name="set_id" value="<?= (int)$set['id'] ?>">
  <button type="submit" class="btn-delete-set" onclick="return confirm('Delete the whole set? This cannot be undone.')">
    Delete This Set
  </button>
</form>

<script>
document.getElementById('save-all').addEventListener('click', async () => {
  const items = Array.from(document.querySelectorAll('.question-item'));
  const updates = items.map(item => {
    const id = parseInt(item.dataset.id, 10);
    const type = item.dataset.type;
    const prompt = item.querySelector('.q-prompt')?.value?.trim() || '';

    const upd = { id, type, prompt };

    if (type === 'Multiple Choice') {
      const opts = Array.from(item.querySelectorAll('.mc-opt')).map(i => i.value);
      const checked = item.querySelector('input[name="correctIndex-' + id + '"]:checked');
      upd.options = opts;
      upd.correctIndex = checked ? parseInt(checked.value, 10) : null;
    } else if (type === 'True or False') {
      const checked = item.querySelector('input[name="correctBool-' + id + '"]:checked');
      upd.correctBool = checked ? (checked.value === '1') : null;
    } else if (type === 'Response') {
      upd.correctText = item.querySelector('.resp-text')?.value || '';
    }

    return upd;
  });

  try {
    const res = await fetch('api/questions.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ updates })
    });

    if (!res.ok) {
      const t = await res.text();
      throw new Error(`HTTP ${res.status}: ${t}`);
    }

    const ct = res.headers.get('content-type') || '';
    if (!ct.includes('application/json')) {
      const t = await res.text();
      throw new Error(`Expected JSON, got: ${t.substring(0, 200)}…`);
    }

    const data = await res.json();
    if (!data.ok) {
      console.error('Bulk update error:', data);
      alert('Save failed: ' + (data.error || 'Unknown'));
      return;
    }

    // Success: go back to sets
    window.location.href = 'index.php?command=sets';
  } catch (err) {
    console.error(err);
    alert('Save failed. See console for details.');
  }
});
</script>
