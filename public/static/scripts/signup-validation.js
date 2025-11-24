/**
 * Signup Form Validation
 * Provides client-side validation for signup form fields
 * Authors: Carlos Orellana, David Nu Nu
 * Sprint 4: Input Validation Feature
 */

$(document).ready(() => {
  const $form = $('.sign-up-form');
  const $firstNameInput = $form.find('input[name="first_name"]');
  const $lastNameInput = $form.find('input[name="last_name"]');
  const $emailInput = $form.find('input[name="email"]');
  const $passwordInput = $form.find('input[name="password"]');
  const $confirmPasswordInput = $form.find('input[name="confirm_password"]');
  const $termsCheckbox = $form.find('#terms');

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
   * Validate password strength (minimum 6 characters)
   * @param {string} password - Password to validate
   * @returns {boolean} - True if password meets requirements
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

  // First name validation on blur
  $firstNameInput.on('blur', function () {
    const value = $(this).val().trim();
    if (value === '') {
      showError($(this), 'First name is required');
    } else {
      clearError($(this));
    }
  });

  // Last name validation on blur
  $lastNameInput.on('blur', function () {
    const value = $(this).val().trim();
    if (value === '') {
      showError($(this), 'Last name is required');
    } else {
      clearError($(this));
    }
  });

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

  // Confirm password validation on blur
  $confirmPasswordInput.on('blur', function () {
    const password = $passwordInput.val();
    const confirmPassword = $(this).val();
    if (confirmPassword === '') {
      showError($(this), 'Please confirm your password');
    } else if (password !== confirmPassword) {
      showError($(this), 'Passwords do not match');
    } else {
      clearError($(this));
    }
  });

  // Clear errors on input
  $firstNameInput.on('input', function () {
    clearError($(this));
  });

  $lastNameInput.on('input', function () {
    clearError($(this));
  });

  $emailInput.on('input', function () {
    clearError($(this));
  });

  $passwordInput.on('input', function () {
    clearError($(this));
  });

  $confirmPasswordInput.on('input', function () {
    clearError($(this));
  });

  // Form submission validation
  $form.on('submit', function (e) {
    let isValid = true;

    const firstName = $firstNameInput.val().trim();
    const lastName = $lastNameInput.val().trim();
    const email = $emailInput.val().trim();
    const password = $passwordInput.val();
    const confirmPassword = $confirmPasswordInput.val();
    const termsChecked = $termsCheckbox.prop('checked');

    // Validate first name
    if (firstName === '') {
      showError($firstNameInput, 'First name is required');
      isValid = false;
    } else {
      clearError($firstNameInput);
    }

    // Validate last name
    if (lastName === '') {
      showError($lastNameInput, 'Last name is required');
      isValid = false;
    } else {
      clearError($lastNameInput);
    }

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

    // Validate confirm password
    if (confirmPassword === '') {
      showError($confirmPasswordInput, 'Please confirm your password');
      isValid = false;
    } else if (password !== confirmPassword) {
      showError($confirmPasswordInput, 'Passwords do not match');
      isValid = false;
    } else {
      clearError($confirmPasswordInput);
    }

    // Validate terms checkbox
    if (!termsChecked) {
      $termsCheckbox.addClass('checkbox-error');
      isValid = false;
    } else {
      $termsCheckbox.removeClass('checkbox-error');
    }

    // Prevent submission if invalid
    if (!isValid) {
      e.preventDefault();
    }
  });

  // Clear checkbox error on change
  $termsCheckbox.on('change', function () {
    $(this).removeClass('checkbox-error');
  });
});
