/**
 * Play Board Interaction Script
 * Handles Jeopardy board cell interactions - marking questions as answered
 * Authors: Carlos Orellana, David Nu Nu, Help from copilot/codex/claude to structure and debug
 * Sprint 4: Dynamic Behavior + JavaScript Object
 */

// Question object for each Jeopardy cell on the board
// Stores id, category, points, and whether it's been answered
const Question = function(id, category, points, answered = false) {
  this.id = id;
  this.category = category;
  this.points = points;
  this.answered = answered;
};

$(document).ready(() => {
  // Store all questions on the board
  const questions = [];
  let questionId = 1;

  // Display labels in the header are in the HTML.
  // These are the DB category keys that match your "question.category" column.
  const dbCategories = ['author', 'character', 'movies', 'quotes', 'event'];

  const pointValues = [100, 200, 300, 400, 500];

  // Read answered questions from data-answered (array of "category:points")
  const $board = $('.jeopardy-board');
  let answeredMap = {};

  try {
    const raw = $board.data('answered'); // jQuery may parse JSON automatically
    let arr = raw;

    if (typeof raw === 'string') {
      arr = JSON.parse(raw);
    }

    if (Array.isArray(arr)) {
      arr.forEach(key => {
        answeredMap[key] = true;
      });
    }
  } catch (e) {
    console.warn('Could not parse answered list:', e);
  }

  // Initialize all question objects: create a Question for each non-header cell
  const initializeQuestions = () => {
    // Only the numeric cells; headers use class "category"
    const $cells = $('.grid .cell');

    $cells.each(function (index) {
      const $cell = $(this);
      const cellText = $cell.text().trim();
      const points = parseInt(cellText, 10) || 0;

      // Column index: 0–4 (Authors, Characters, Movies, Quotes, Event)
      const columnIndex = index % dbCategories.length;
      const categoryKey = dbCategories[columnIndex] || 'unknown';

      // Create Question object
      const question = new Question(questionId++, categoryKey, points);
      questions.push(question);

      // Store question data on the element for later use
      $cell.data('questionId', question.id);
      $cell.data('category', categoryKey);
      $cell.data('points', points);

      // If this cell is already answered (from the session), mark it
      const answerKey = `${categoryKey}:${points}`;
      if (answeredMap[answerKey]) {
        question.answered = true;
        $cell.addClass('answered');
        $cell.attr('aria-pressed', 'true');
      }
    });
  };

  // Toggle answered state for a question cell
  // Marks the question as answered and highlights the cell
  const toggleAnswered = ($cell, questionId) => {
    // Find the question object
    const question = questions.find(q => q.id === questionId);
    
    if (question) {
      // Toggle answered state
      question.answered = !question.answered;
      
      // Toggle visual state
      if (question.answered) {
        $cell.addClass('answered');
        $cell.attr('aria-pressed', 'true');
      } else {
        $cell.removeClass('answered');
        $cell.attr('aria-pressed', 'false');
      }
    }
  };

  // Initialize all questions
  initializeQuestions();

  // Handle cell clicks:
  // 1) if already answered, do nothing
  // 2) otherwise navigate to the PHP question page with set_id, category, points
  $(document).on('click', '.grid .cell:not(.category)', function () {
    const $cell = $(this);

    // Don't allow clicking an already-answered cell
    if ($cell.hasClass('answered')) {
      return;
    }

    const questionId = $cell.data('questionId');
    const category   = $cell.data('category');
    const points     = $cell.data('points');

    // Only act if it's a valid question cell
    if (questionId === undefined || !category || !points) {
      return;
    }

    // Mark visually answered immediately (optional, the session will enforce it too)
    toggleAnswered($cell, questionId);

    // Read set_id from the board container
    const setId = $('.jeopardy-board').data('setId') || 1;

    const url = `index.php?command=play_question`
      + `&set_id=${encodeURIComponent(setId)}`
      + `&category=${encodeURIComponent(category)}`
      + `&points=${encodeURIComponent(points)}`;

    window.location.href = url;
  });

  // Log initialization (anonymous IIFE for demonstration)
  (function () {
    console.log(`Play board initialized with ${questions.length} questions`);
    console.log('Click any cell to open the question (already-answered cells are disabled).');
  })();
});
