<link rel="stylesheet" href="static/styles/sets.css">
<script defer src="static/scripts/sets.js"></script>
<script defer src="static/scripts/sets-preview.js"></script>

<section>
  <h2 class="title">Your Question Sets</h2>

  <a href="index.php?command=create_game">
    <button class="button create-new-game-button">Create a new set</button>
  </a>

  <?php if (!$rows): ?>
    <p class="error-message">Could not load your sets right now.</p>

  <?php else: ?>
    <?php if (pg_num_rows($rows) === 0): ?>
      <p>You don’t have any sets yet. Click "Create a new set".</p>

    <?php else: ?>
      <ul>
        <?php while ($row = pg_fetch_assoc($rows)): ?>
          <li class="question-set-list">
            <strong><?= htmlspecialchars($row['title']) ?></strong>
            —
            <span><?= (int)$row['question_count'] ?> questions</span>
          
            <a href="index.php?command=view_set&id=<?= (int)$row['id'] ?>">
              <button class="button edit-button question-button">
                Edit
              </button>
            </a>
      
            <button
              type="button"
              class="preview-set-btn button question-button"
              data-set-id="<?= (int)$row['id'] ?>"
              data-set-title="<?= htmlspecialchars($row['title']) ?>"
            >
              Preview
            </button>
            <button
              type="button"
              class="delete-set-btn button question-button"
              data-set-id="<?= (int)$row['id'] ?>"
              data-set-title="<?= htmlspecialchars($row['title']) ?>"
            >
              Delete
            </button>
          </li>
        <?php endwhile; ?>
      </ul>
    <?php endif; ?>
  <?php endif; ?>
</section>

<!-- Confirm Delete Modal -->
<div id="deleteModalOverlay" class="modal-overlay hidden">
  <div class="modal">
    <h2>Delete set?</h2>
    <p id="deleteModalText">Are you sure you want to delete this set?</p>

    <div class="modal-actions">
      <button id="confirmDeleteBtn" class="button danger">Yes, delete</button>
      <button id="cancelDeleteBtn" class="button ghost">No</button>
    </div>
  </div>
</div>
