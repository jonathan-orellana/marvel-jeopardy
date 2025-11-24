/**
 * Play Board Interaction Script
 * Handles Jeopardy board cell interactions - marking questions as answered
 * Authors: Carlos Orellana, David Nu Nu
 * Sprint 4: Dynamic Behavior + JavaScript Object
 */

/**
 * Question Object
 * Represents a Jeopardy question on the board
 * @constructor
 * @param {number} id - Unique question identifier
 * @param {string} category - Question category name
 * @param {number} points - Point value (100, 200, 300, 400, 500)
 * @param {boolean} answered - Whether the question has been answered
 */
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

  const categories = ['Authors', 'Characters', 'Movies', 'Quotes', 'Event'];
  const pointValues = [100, 200, 300, 400, 500];

  /**
   * Initialize all question objects
   * Creates a Question object for each cell on the board
   */
  const initializeQuestions = () => {
    const $cells = $('.grid .cell');
    
    $cells.each(function () {
      const $cell = $(this);
      const cellText = $cell.text().trim();
      
      // Skip category headers
      if (categories.includes(cellText)) {
        return;
      }

      // Determine category from grid position
      const index = $cells.index($cell);
      const categoryIndex = (index - 5) % 5; // Offset by 5 category headers
      const category = categories[categoryIndex] || 'Unknown';
      const points = parseInt(cellText) || 0;

      // Create Question object
      const question = new Question(questionId++, category, points);
      questions.push(question);

      // Store question ID on the element
      $cell.data('questionId', question.id);
    });
  };

  /**
   * Toggle answered state for a question cell
   * Marks the question as answered and highlights the cell
   * @param {jQuery} $cell - The clicked cell element
   * @param {number} questionId - The question ID to toggle
   */
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

  // Handle cell clicks to mark as answered
  $(document).on('click', '.grid .cell:not(.category)', function () {
    const $cell = $(this);
    const questionId = $cell.data('questionId');
    
    // Only toggle if it's a valid question cell
    if (questionId !== undefined) {
      toggleAnswered($cell, questionId);
    }
  });

  // Log initialization (anonymous IIFE for demonstration)
  (function () {
    console.log(`\u2705 Play board initialized with ${questions.length} questions`);
    console.log('Click any cell to mark it as answered!');
  })();
});
