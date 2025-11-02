<?php
class MarvelController {
    private $db; // Database object (Interact with db)
    private $input;   // $_GET
    private $errors = []; // To store errors

    public function __construct($input) {
        $this->input = $input;

        // Start session
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Bring in the db.php and auth.php
        require_once __DIR__ . '/db.php';   
        require_once __DIR__ . '/auth.php'; 
        $this->db = $db; 
    }

    public function run() {
        // Get command from query
        $command = isset($this->input['command']) ? $this->input['command'] : 'home';

        switch ($command) {
            // Get view
            case 'home' : return $this->showHome();
            case 'login': return $this->showLogin();
            case 'signup': return $this->showSignup();
            case 'create_game' : return $this->showCreateGame();
            case 'sets': return $this->showSets();
            case 'view_set': return $this->showViewSet();

            // Model
            case 'signup_submit': return $this->signupSubmit();
            case 'login_submit': return $this->loginSubmit();
            case 'logout': return $this->logout();

            default: return $this->showHome();
        }
    }

    // View
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
        $rows = $this->getSets();

        include __DIR__ . '/../public/templates/header.php';
        include __DIR__ . '/../public/templates/sets.php';
        include __DIR__ . '/../public/templates/footer.php';
    }

    private function showViewSet() {
        include __DIR__ . '/../public/templates/header.php';
        include __DIR__ . '/../public/templates/set_view.php';
        include __DIR__ . '/../public/templates/footer.php';
    }

    // Model 
    private function signupSubmit() {
        $result = handle_signup($this->db); 

        // Redirect
        if ($result['ok']) {
            header('Location: index.php?command=login');
            exit;
        }

        // If error, populate $errors with error
        $errors = $result['errors'] ?? [];

        // Show page again
        include __DIR__ . '/../public/templates/header.php';
        include __DIR__ . '/../public/templates/signup.php';
        include __DIR__ . '/../public/templates/footer.php';
    }

    private function loginSubmit() {
        $result = handle_login($this->db);

        // Redirect
        if ($result['ok']) {
            header('Location: index.php?command=home');
            exit;
        }

        // If error, populate $errors with error
        $errors = $result['errors'] ?? [];
        
        // Show page again
        include __DIR__ . '/../public/templates/header.php';
        include __DIR__ . '/../public/templates/login.php';
        include __DIR__ . '/../public/templates/footer.php';
    }

    private function logout() {
        //destroy session and cookies
        session_destroy();
        // Redirect
        header('Location: index.php?command=login');
        exit;
    }

    // Need test (experimental)
    private function getSets() { 
        if (!isset($_SESSION['user'])) {
        header('Location: index.php?command=login');
        exit;
        }

        $userID = $_SESSION['user'];

        $rows = pg_query_params($this->db,
        "SELECT id, title, created_at FROM question_set WHERE user_id = $1 ORDER BY id DESC",
        [$userID]
        );

        return $rows;
    }

    public function errors() { 
        return $this->errors;
    }
}
