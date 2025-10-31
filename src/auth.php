<?php
//auth model, does validation + insert user
function handle_signup($db) {
    // always return this shape
    $out = ['ok' => false, 'errors' => []];

    // collect data input (signup form)
    $first   = trim($_POST['first_name'] ?? '');
    $last    = trim($_POST['last_name'] ?? '');
    $email   = trim($_POST['email'] ?? '');
    $pass    = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';
    $term = $_POST['terms'] ?? null;

    // validations
    if ($first === '' || $last === '' || $email === '' || $pass === '' || $confirm === '') {
        $out['errors'][] = "All fields are required.";
        return $out;
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $out['errors'][] = "Please enter a valid email address.";
        return $out;
    }
    if ($pass !== $confirm) {
        $out['errors'][] = "Passwords do not match.";
        return $out;
    }
    if (!isset($term)) {
        $out['errors'][] = "Please accept the Terms and Conditions";
        return $out;
    }


    // check if user (email) already exists
    $check = pg_query_params($db, 'SELECT 1 FROM app_user WHERE email = $1 LIMIT 1', [$email]);
    if ($check === false) {
        $out['errors'][] = "Database error: " . pg_last_error($db);
        return $out;
    }
    if (pg_num_rows($check) > 0) {
        $out['errors'][] = "That email is already registered.";
        return $out;
    }

    // hash the password (security)
    $hash = password_hash($pass, PASSWORD_DEFAULT);

    // insert user into the db
    $q = 'INSERT INTO app_user (first_name, last_name, email, password_hash) VALUES ($1,$2,$3,$4)';
    $ok = pg_query_params($db, $q, [$first, $last, $email, $hash]);

    if (!$ok) {
        $out['errors'][] = "Database error: " . pg_last_error($db);
        return $out;
    }
}



function handle_login($db) {
    // Always return this shape
    $out = ['ok' => false, 'errors' => []];

    $email = trim($_POST['email'] ?? '');
    $pass  = $_POST['password'] ?? '';

    // basic validation
    if ($email === '' || $pass === '') {
        $out['errors'][] = "Email and password are required.";
        return $out;
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $out['errors'][] = "Please enter a valid email address.";
        return $out;
    }

    // query user (use your actual table name: app_user or "user")
    $sql = 'SELECT id, first_name, password_hash FROM app_user WHERE email = $1 LIMIT 1';
    $res = pg_query_params($db, $sql, [$email]);
    if ($res === false) {
        $out['errors'][] = "Database error: " . pg_last_error($db);
        return $out;
    }

    $u = pg_fetch_assoc($res);
    if (!$u || !password_verify($pass, $u['password_hash'])) {
        $out['errors'][] = "Invalid email or password.";
        return $out;
    }

    // set session + cookie
    if (session_status() === PHP_SESSION_NONE) { session_start(); }
    $_SESSION['user_id']    = (int)$u['id'];
    $_SESSION['first_name'] = $u['first_name'];
    $_SESSION['user']       = $u['first_name']; 
    setcookie("last_email", $email, time() + 3600);

    $out['ok'] = true;
    return $out;
}


