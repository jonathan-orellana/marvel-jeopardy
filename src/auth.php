<?php
//auth model, does validation + insert user
function handle_signup($db) {
    // collect data input (signup form)
    $first = trim($_POST['first_name'] ?? '');
    $last  = trim($_POST['last_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $pass  = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';
    $errors = [];

    // validations
    if ($first == '' || $last == '' || $email == '' || $pass == '' || $confirm == '') {
        $errors[] = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Please enter a valid email address.";
    } elseif ($pass != $confirm) {
        $errors[] = "Passwords do not match.";
    }

    // check if user (email) already exists
    if (count($errors) == 0) {
        $check = pg_query_params($db, "SELECT id FROM user WHERE email = $1", [$email]);
        if (pg_num_rows($check) > 0) {
            $errors[] = "That email is already registered. Please log in.";
        }
    }

    // if errors, render signup page again
    if (count($errors) > 0) {
        include '../public/templates/header.php';
        include '../public/templates/signup.php';
        include '../public/templates/footer.php';
        return;
    }

    // hash the password (security)
    $hash = password_hash($pass, PASSWORD_DEFAULT);

    // insert user into the db
    $query = "INSERT INTO user (first_name, last_name, email, password_hash)
              VALUES ($1, $2, $3, $4)";
    $result = @pg_query_params($db, $query, [$first, $last, $email, $hash]);

    if ($result) {
        // remember user
        session_start();
        $_SESSION['user'] = $first;

        // remember some data fields for 1 hour
        setcookie("last_email", $email, time() + 3600);

        // redirect to home page
        header("Location: index.php?command=home");
        exit;
    } else {
        // render page again (try again)
        $errors[] = "Database error.";
        include '../public/templates/header.php';
        include '../public/templates/signup.php';
        include '../public/templates/footer.php';
    }
} 

function handle_login($db) {
    // read form data
    $email = trim($_POST['email'] ?? '');
    $pass  = $_POST['password'] ?? '';
    $errors = [];

    // validation
    if ($email === '' || $pass === '') {
        $errors[] = "Email and password are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Please enter a valid email address.";
    }

    if (count($errors) === 0) {
        // find user by email
        $res = pg_query_params($db, "SELECT id, first_name, password_hash FROM user WHERE email = $1", [$email]);
        $u = pg_fetch_assoc($res);

        //if user exist
        if ($u && password_verify($pass, $u['password_hash'])) {
            // start session
            if (session_status() === PHP_SESSION_NONE) { session_start(); }
            $_SESSION['user'] = $u['first_name'];

            setcookie("last_email", $email, time() + 3600);

            header("Location: index.php?command=home");
            exit;
        } else {
            $errors[] = "Invalid email or password.";
        }
    }

    // if error, try again
    include __DIR__ . '/../public/templates/header.php';
    include __DIR__ . '/../public/templates/login.php';
    include __DIR__ . '/../public/templates/footer.php';
}

