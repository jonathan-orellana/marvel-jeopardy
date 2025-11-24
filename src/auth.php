<?php
function handle_signup($db) {
    $result = [
        'ok' => false, 
        'errors' => []
    ];

    // Get data from body request
    $first   = trim($_POST['first_name'] ?? '');
    $last    = trim($_POST['last_name'] ?? '');
    $email   = trim($_POST['email'] ?? '');
    $pass    = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';
    $term = $_POST['terms'] ?? null;

    // Validations

    // Empty fields
    if ($first === '' || $last === '' || $email === '' || $pass === '' || $confirm === '') {
        $result['errors'][] = "All fields are required.";
        return $result;
    }
    // Valid email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $result['errors'][] = "Please enter a valid email address.";
        return $result;
    }
    // Only latter names
    if (!preg_match('/^[A-Za-z]{2,40}$/', $first) || !preg_match('/^[A-Za-z]{2,40}$/', $last)) {
        $result['errors'][] = "Names should only contain letters.";return $result;
    }
    // Password 8+ character
    if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z0-9]).{8,}$/', $pass)) {
        $result['errors'][] = "Password must have 8+ characters, including uppercase, lowercase, number, and symbol.";
        return $result;
    }
    // Password match
    if ($pass !== $confirm) {
        $result['errors'][] = "Passwords do not match.";
        return $result;
    }
    // Accept terms and conditions
    if (!isset($term)) {
        $result['errors'][] = "Please accept the Terms and Conditions";
        return $result;
    }

    // Check if email already exists
    $check = pg_query_params($db, 'SELECT 1 FROM app_user WHERE email = $1 LIMIT 1', [$email]);
    if ($check === false) {
        $result['errors'][] = "Database error: " . pg_last_error($db);
        return $result;
    }
    if (pg_num_rows($check) > 0) {
        $result['errors'][] = "That email already exist. Please login or use a different email.";
        return $result;
    }

    // If everything looks fine, prepare to save

    // Hash the password 
    $hash = password_hash($pass, PASSWORD_DEFAULT);

    // Write a query to insert the user
    $query = 'INSERT INTO app_user (first_name, last_name, email, password_hash) VALUES ($1,$2,$3,$4)';

    // Save
    $ok = pg_query_params($db, $query, [$first, $last, $email, $hash]);

    // If something goes wrong while saving
    if (!$ok) {
        $result['errors'][] = "Database error: " . pg_last_error($db);
        return $result;
    }

    // Success
    $_SESSION['user'] = (int)$userInfo['id'];
    setcookie("last_email", $email, time() + 3600);
    $result['ok'] = true;
    return $result;
}


function handle_login($db) {
    $result = [
        'ok' => false, 
        'errors' => []
    ];

    // Get data from body request
    $email = trim($_POST['email'] ?? '');
    $pass  = $_POST['password'] ?? '';

    // Validations

    // Empty fields
    if ($email === '' || $pass === '') {
        $result['errors'][] = "Email and password are required.";
        return $result;
    }
    // Valid email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $result['errors'][] = "Please enter a valid email address.";
        return $result;
    }

    // Write a query to return the user
    $query = 'SELECT id, first_name, password_hash FROM app_user WHERE email = $1 LIMIT 1';

    // Get the user row
    $userRow = pg_query_params($db, $query, [$email]);

    if ($userRow === false) {
        $result['errors'][] = "Database error: " . pg_last_error($db);
        return $result;
    }

    // Convert the row into array
    $userInfo = pg_fetch_assoc($userRow);

    // Check the password
    if (!$userInfo || !password_verify($pass, $userInfo['password_hash'])) {
        $result['errors'][] = "Invalid email or password.";
        return $result;
    }

    // Set session + Cookie
    if (session_status() === PHP_SESSION_NONE) { 
        session_start(); 
    }

    // Session
    $_SESSION['user'] = (int)$userInfo['id'];
    setcookie("last_email", $email, time() + 3600);

    $result['ok'] = true;

    return $result;
}


