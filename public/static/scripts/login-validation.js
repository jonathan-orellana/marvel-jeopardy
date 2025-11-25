/**
 * Login Form Validation
 * Provides client-side validation for email and password fields
 * Authors: Carlos Orellana, David Nu Nu, Help from copilot/codex/claude to structure and debug
 * Sprint 4: Input Validation Feature
 */

$(document).ready(() => {
  const $emailInput = $('#email-input');
  const $passwordInput = $('#password-input');
  const $form = $('.login-form');
  const $submitButton = $form.find('button');

  // Check if email is valid
  // basic pattern: something@something.domain
  const isValidEmail = (email) => {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
  };

  // Basic password rule: True if password is at least 6 characters long
  const isValidPassword = (password) => {
    return password.length >= 6;
  };

  // Show an error on a given input: adds error class and message under the field
  const showError = ($input, message) => {
    $input.addClass('input-error');
    let $errorDiv = $input.siblings('.error-text');
    if ($errorDiv.length === 0) {
      $errorDiv = $('<div class="error-text"></div>');
      $input.after($errorDiv);
    }
    $errorDiv.text(message);
  };

  // Clear error state for an input: removes the class and the message under the field.
  const clearError = ($input) => {
    $input.removeClass('input-error');
    $input.siblings('.error-text').remove();
  };

  // Email validation when the field loses focus
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

  // Password validation when the field loses focus
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

  // Clear errors while the user is typing (email)
  $emailInput.on('input', function () {
    clearError($(this));
  });

  // Clear errors while the user is typing (password)
  $passwordInput.on('input', function () {
    clearError($(this));
  });

  // Final validation on submit
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
