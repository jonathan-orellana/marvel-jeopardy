/**
 * Login Form Validation
 * Provides client-side validation for email and password fields
 * Authors: Carlos Orellana, David Nu Nu
 * Sprint 4: Input Validation Feature
 */

$(document).ready(() => {
  const $emailInput = $('#email-input');
  const $passwordInput = $('#password-input');
  const $form = $('.login-form');
  const $submitButton = $form.find('button');

  /**
   * Validate email format
   * @param {string} email - Email address to validate
   * @returns {boolean} - True if email is valid
   */
  const isValidEmail = (email) => {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
  };

  /**
   * Validate password length
   * @param {string} password - Password to validate
   * @returns {boolean} - True if password is at least 6 characters
   */
  const isValidPassword = (password) => {
    return password.length >= 6;
  };

  /**
   * Display error message for a field
   * @param {jQuery} $input - jQuery input element
   * @param {string} message - Error message to display
   */
  const showError = ($input, message) => {
    $input.addClass('input-error');
    let $errorDiv = $input.siblings('.error-text');
    if ($errorDiv.length === 0) {
      $errorDiv = $('<div class="error-text"></div>');
      $input.after($errorDiv);
    }
    $errorDiv.text(message);
  };

  /**
   * Clear error message for a field
   * @param {jQuery} $input - jQuery input element
   */
  const clearError = ($input) => {
    $input.removeClass('input-error');
    $input.siblings('.error-text').remove();
  };

  // Email validation on blur
  $emailInput.on('blur', function () {
    const email = $(this).val().trim();
    if (email === '') {
      showError($(this), 'Email is required');
    } else if (!isValidEmail(email)) {
      showError($(this), 'Please enter a valid email address');
    } else {
      clearError($(this));
    }
  });

  // Password validation on blur
  $passwordInput.on('blur', function () {
    const password = $(this).val();
    if (password === '') {
      showError($(this), 'Password is required');
    } else if (!isValidPassword(password)) {
      showError($(this), 'Password must be at least 6 characters');
    } else {
      clearError($(this));
    }
  });

  // Clear errors on input
  $emailInput.on('input', function () {
    clearError($(this));
  });

  $passwordInput.on('input', function () {
    clearError($(this));
  });

  // Form submission validation
  $form.on('submit', function (e) {
    let isValid = true;

    const email = $emailInput.val().trim();
    const password = $passwordInput.val();

    // Validate email
    if (email === '') {
      showError($emailInput, 'Email is required');
      isValid = false;
    } else if (!isValidEmail(email)) {
      showError($emailInput, 'Please enter a valid email address');
      isValid = false;
    } else {
      clearError($emailInput);
    }

    // Validate password
    if (password === '') {
      showError($passwordInput, 'Password is required');
      isValid = false;
    } else if (!isValidPassword(password)) {
      showError($passwordInput, 'Password must be at least 6 characters');
      isValid = false;
    } else {
      clearError($passwordInput);
    }

    // Prevent submission if invalid
    if (!isValid) {
      e.preventDefault();
    }
  });
});
