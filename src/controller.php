<?php
class MarvelController {
    private $db;
    private $input;   // $_GET
    private $errors = [];

    public function __construct($input) {
        $this->input = $input;

        //start session
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        //get php files (plain php)
        require_once __DIR__ . '/db.php';   
        require_once __DIR__ . '/auth.php'; 
        $this->db = $db; 
    }

    public function run() {
        //get command (command call) or set to login
        $command = isset($this->input['command']) ? $this->input['command'] : 'home';

        switch ($command) {
            //get view (render page)
            case 'home' : return $this->showHome();
            case 'login': return $this->showLogin();
            case 'signup': return $this->showSignup();
            case 'create_game' : return $this->showCreateGame();
            case 'sets': return $this->showSets();
            case 'view_set': return $this->showViewSet();

            //form submissions (post)
            case 'signup_submit': return $this->signupSubmit();
            case 'login_submit': return $this->loginSubmit();
            case 'logout': return $this->logout();

            default: return $this->showHome();
        }
    }

    //view
    private function showHome() {
        include __DIR__ . '/../public/templates/header.php';
        include __DIR__ . '/../public/templates/home.php';
        include __DIR__ . '/../public/templates/footer.php';
    }

    private function showLogin() {
        include __DIR__ . '/../public/templates/header.php';
        include __DIR__ . '/../public/templates/login.php';
        include __DIR__ . '/../public/templates/footer.php';
    }

    private function showSignup() {
        include __DIR__ . '/../public/templates/header.php';
        include __DIR__ . '/../public/templates/signup.php';
        include __DIR__ . '/../public/templates/footer.php';
    }

    private function showCreateGame() {
        include __DIR__ . '/../public/templates/header.php';
        include __DIR__ . '/../public/templates/create-question.php';
        include __DIR__ . '/../public/templates/footer.php';
    }

    private function showSets() {
        include __DIR__ . '/../public/templates/header.php';
        include __DIR__ . '/../public/templates/sets.php';
        include __DIR__ . '/../public/templates/footer.php';
    }

    private function showViewSet() {
        include __DIR__ . '/../public/templates/header.php';
        include __DIR__ . '/../public/templates/set_view.php';
        include __DIR__ . '/../public/templates/footer.php';
    }

    // Submissions 
    private function signupSubmit() {
        //call auth model
        $result = handle_signup($this->db); 
        if ($result['ok']) {
            header('Location: index.php?command=login');
            exit;
        }
        // if error, render form again with errors
        $errors = $result['errors'] ?? [];
        
        include __DIR__ . '/../public/templates/header.php';
        include __DIR__ . '/../public/templates/signup.php';
        include __DIR__ . '/../public/templates/footer.php';
    }

    private function loginSubmit() {
        $result = handle_login($this->db);
        if ($result['ok']) {
            header('Location: index.php?command=home');
            exit;
        }

        $errors = $result['errors'] ?? [];

        include __DIR__ . '/../public/templates/header.php';
        include __DIR__ . '/../public/templates/login.php';
        include __DIR__ . '/../public/templates/footer.php';
    }

    private function logout() {
        //destroy session and cookies
        session_destroy();
        header('Location: index.php');
        exit;
    }

    /* Optional: expose errors to templates */
    public function errors() { return $this->errors; }
}
