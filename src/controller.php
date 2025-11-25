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
        require_once __DIR__ . '/../public/api/save_question.php';
        require_once __DIR__ . '/../public/api/delete_set.php';
        require_once __DIR__ . '/../public/api/get_set.php';
        require_once __DIR__ . '/../public/api/update_questions.php';

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
            case "view_set": return $this->viewSet();
            case 'about': return $this->showAbout();
            case 'play': return $this->showPlay();

            // Model
            case 'signup_submit': return $this->signupSubmit();
            case 'login_submit': return $this->loginSubmit();
            case 'logout': return $this->logout();

            // Api
            case 'save_question': return $this->saveQuestion();
            case "delete_set": return $this->deleteSet();
            case "get_set": return $this->getSet();
            case "update_questions":return $this->updateQuestions();


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

    private function showAbout() {
        include __DIR__ . '/../public/templates/header.php';
        include __DIR__ . '/../public/templates/about.php';
        include __DIR__ . '/../public/templates/footer.php';
    }

    private function showPlay() {
        include __DIR__ . '/../public/templates/header.php';
        include __DIR__ . '/../public/templates/jeopardy-board.php';
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

    private function getSets() { 
        if (!isset($_SESSION['user'])) {
            header('Location: index.php?command=login');
            exit;
        }

        $userID = $_SESSION['user'];

        $sql = "
            SELECT 
                qs.id,
                qs.title,
                qs.created_at,
                COUNT(q.id) AS question_count
            FROM question_set qs
            LEFT JOIN question q
                ON q.question_set_id = qs.id
            WHERE qs.user_id = $1
            GROUP BY qs.id
            ORDER BY qs.created_at DESC, qs.id DESC
        ";

        $rows = pg_query_params($this->db, $sql, [$userID]);
        return $rows;
    }


    private function saveQuestion() {
        $result = handle_saveQuestionToDatabase($this->db);

        // If fetch sent JSON, ALWAYS return JSON (even on errors)
        $isJsonRequest = (
            isset($_SERVER["CONTENT_TYPE"]) &&
            stripos($_SERVER["CONTENT_TYPE"], "application/json") !== false
        );

        if ($isJsonRequest) {
            header("Content-Type: application/json");
            echo json_encode($result);
            exit;
        }

        // Normal browser submit behavior (HTML)
        if ($result['ok']) {
            header('Location: index.php?command=home');
            exit;
        }

        $errors = $result['errors'] ?? [];
        include __DIR__ . '/../public/templates/header.php';
        include __DIR__ . '/../public/templates/create-question.php';
        include __DIR__ . '/../public/templates/footer.php';
    }

    private function deleteSet() {
        header("Content-Type: application/json");

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $user_id = $_SESSION["user"] ?? null;
        if (!$user_id) {
            echo json_encode(["ok" => false, "errors" => ["You must be logged in."]]);
            exit;
        }

        $data = json_decode(file_get_contents("php://input"), true) ?? [];
        $set_id = (int)($data["id"] ?? 0);

        $result = handle_deleteSetFromDatabase($this->db, $set_id, $user_id);

        echo json_encode($result);
        exit;
    }

    private function getSet() {
        header("Content-Type: application/json");

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $user_id = $_SESSION["user"] ?? null;
        if (!$user_id) {
            echo json_encode(["ok" => false, "errors" => ["You must be logged in."]]);
            exit;
        }

        $set_id = (int)($_GET["id"] ?? 0);
        
        $result = handle_getSetWithQuestions($this->db, $set_id, $user_id);
        
        echo json_encode($result);
        exit;
    }

    private function viewSet() {
        if (session_status() === PHP_SESSION_NONE) session_start();

        if (!isset($_SESSION['user'])) {
            header('Location: index.php?command=login');
            exit;
        }

        $userID = $_SESSION['user'];
        $set_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

        $result = handle_getSetWithQuestions($this->db, $set_id, $userID);

        if (!$result["ok"]) {
            $errors = $result["errors"];
            include __DIR__ . '/../public/templates/header.php';
            include __DIR__ . '/../public/templates/error.php'; 
            include __DIR__ . '/../public/templates/footer.php';
            return;
        }

        $setData = $result["set"];
        $questions = $result["questions"];

        include __DIR__ . '/../public/templates/header.php';
        include __DIR__ . '/../public/templates/set_view.php';
        include __DIR__ . '/../public/templates/footer.php';
    }

    private function updateQuestions() {
        header("Content-Type: application/json");
        if (session_status() === PHP_SESSION_NONE) session_start();

        $user_id = $_SESSION["user"] ?? null;
        if (!$user_id) {
            echo json_encode(["ok"=>false,"errors"=>["You must be logged in."]]);
            exit;
        }

        $data = json_decode(file_get_contents("php://input"), true) ?? [];
        $questions = $data["questions"] ?? [];

        $result = handle_updateQuestionsToDatabase($this->db, $questions, $user_id);

        echo json_encode($result);
        exit;
    }


    public function errors() { 
        return $this->errors;
    }
}
