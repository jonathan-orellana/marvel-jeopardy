/**
 * Sets Preview AJAX Script
 * Loads question set data via AJAX and renders it to the DOM
 * Authors: Carlos Orellana, David Nu Nu
 * Sprint 4: AJAX JSON Consumption + DOM Rendering
 */

$(document).ready(() => {
  /**
   * Load and display set preview via AJAX
   * Fetches set questions from server and renders in modal
   * @param {number} setId - The question set ID to preview
   * @param {string} setTitle - The title of the set
   */
  const loadSetPreview = (setId, setTitle) => {
    // Create modal if it doesn't exist
    let $previewModal = $('#set-preview-modal');
    if ($previewModal.length === 0) {
      $previewModal = $(`
        <div id="set-preview-modal" class="modal-overlay" style="display:none;">
          <div class="modal-content">
            <div class="modal-header">
              <h2 id="preview-title"></h2>
              <button class="modal-close" aria-label="Close">&times;</button>
            </div>
            <div id="preview-questions" class="preview-questions"></div>
            <div class="modal-footer">
              <button class="button modal-close-btn">Close</button>
            </div>
          </div>
        </div>
      `);
      $('body').append($previewModal);

      // Close button handlers
      $previewModal.on('click', '.modal-close, .modal-close-btn, .modal-overlay', function(e) {
        if (e.target === this || e.target.classList.contains('modal-close')) {
          $previewModal.fadeOut(300, function() {
            $previewModal.remove();
          });
        }
      });
    }

    // Show loading state
    $('#preview-title').text(`Loading ${setTitle}...`);
    $('#preview-questions').html('<p class="loading">Loading questions...</p>');
    $previewModal.fadeIn(300);

    // Fetch set data via AJAX
    $.ajax({
      url: 'index.php?command=get_set',
      type: 'GET',
      dataType: 'json',
      data: { id: setId },
      success: (response) => {
        if (response.ok && response.questions) {
          // Update title
          $('#preview-title').text(`${setTitle} (${response.questions.length} questions)`);

          // Render questions to DOM
          const questionsHtml = response.questions.map((q, idx) => `
            <div class="question-preview" data-question-id="${q.id}">
              <div class="question-header">
                <span class="question-number">#${idx + 1}</span>
                <span class="question-category">${q.category}</span>
                <span class="question-points">${q.points} pts</span>
              </div>
              <div class="question-text">${q.text}</div>
              <div class="question-type">Type: ${q.question_type}</div>
            </div>
          `).join('');

          $('#preview-questions').html(questionsHtml);
        } else {
          $('#preview-questions').html('<p class="error">Failed to load questions.</p>');
        }
      },
      error: (xhr, status, error) => {
        $('#preview-questions').html('<p class="error">Error loading questions.</p>');
        console.error('AJAX Error:', error);
      }
    });
  };

  // Attach preview handlers to view buttons
  $(document).on('click', '.preview-set-btn', function() {
    const setId = $(this).data('set-id');
    const setTitle = $(this).data('set-title');
    loadSetPreview(setId, setTitle);
  });
});
