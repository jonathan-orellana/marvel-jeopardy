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
            case 'play_question': return $this->showQuestion();
            case 'play': return $this->showPlay();             
            case 'play_board': return $this->showPlayBoard();
            case 'gameover': return $this->showGameOver();   

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

    private function showGameOver() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        include __DIR__ . '/../public/templates/header.php';
        include __DIR__ . '/../public/templates/gameover.php';
        include __DIR__ . '/../public/templates/footer.php';
    }


    private function showPlay() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    
        if (!isset($_SESSION['user'])) {
            header('Location: index.php?command=login');
            exit;
        }
    
        $userID = $_SESSION['user'];
    
        $this->ensureDefaultSetForUser($userID);
    
        $rows = $this->getSets();
    
        include __DIR__ . '/../public/templates/header.php';
        include __DIR__ . '/../public/templates/play.php';
        include __DIR__ . '/../public/templates/footer.php';
    }
    
    private function showPlayBoard() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    
        if (!isset($_SESSION['user'])) {
            header('Location: index.php?command=login');
            exit;
        }
    
        $userID = $_SESSION['user'];
        $setId  = isset($this->input['set_id']) ? (int)$this->input['set_id'] : 0;
        $reset  = isset($this->input['reset']) && $this->input['reset'] === '1';
    
        if (!$setId) {
            $errors = ["No set selected to play."];
            include __DIR__ . '/../public/templates/header.php';
            include __DIR__ . '/../public/templates/error.php';
            include __DIR__ . '/../public/templates/footer.php';
            return;
        }
    
        // Optional: verify set belongs to this user (you already had this)
        $sql = "SELECT id FROM question_set WHERE id = $1 AND user_id = $2 LIMIT 1";
        $res = pg_query_params($this->db, $sql, [$setId, $userID]);
    
        if (!$res || pg_num_rows($res) === 0) {
            $errors = ["You do not have access to this set."];
            include __DIR__ . '/../public/templates/header.php';
            include __DIR__ . '/../public/templates/error.php';
            include __DIR__ . '/../public/templates/footer.php';
            return;
        }
    
        // If reset is requested, start a fresh game for this set
        if ($reset) {
            if (isset($_SESSION['answered'][$setId])) {
                unset($_SESSION['answered'][$setId]);
            }
            if (isset($_SESSION['awarded'][$setId])) {
                unset($_SESSION['awarded'][$setId]);
            }
            $_SESSION['score'] = 0;
        }
    
        $currentSetId = $setId;
    
        include __DIR__ . '/../public/templates/header.php';
        include __DIR__ . '/../public/templates/jeopardy-board.php';
        include __DIR__ . '/../public/templates/footer.php';
    }
    
    private function showQuestion() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    
        $setId   = isset($this->input['set_id'])   ? (int)$this->input['set_id']   : 0;
        $category = $this->input['category'] ?? null;
        $points   = isset($this->input['points'])  ? (int)$this->input['points']   : 0;
    
        if (!$setId || !$category || !$points) {
            $errors = ["Missing set, category, or points to load a question."];
            include __DIR__ . '/../public/templates/header.php';
            include __DIR__ . '/../public/templates/error.php';
            include __DIR__ . '/../public/templates/footer.php';
            return;
        }
    
        // 1) Get the base question
        $sqlQuestion = "
            SELECT 
                q.id,
                q.question_set_id,
                q.user_id,
                q.category,
                q.points,
                q.question_type,
                q.text,
                q.created_at
            FROM question q
            WHERE q.question_set_id = $1
              AND q.category = $2
              AND q.points = $3
            ORDER BY q.id
            LIMIT 1
        ";
    
        $resultQ = pg_query_params($this->db, $sqlQuestion, [$setId, $category, $points]);
    
        if (!$resultQ || pg_num_rows($resultQ) === 0) {
            $errors = ["No question found for $category / $points in this set."];
            include __DIR__ . '/../public/templates/header.php';
            include __DIR__ . '/../public/templates/error.php';
            include __DIR__ . '/../public/templates/footer.php';
            return;
        }
    
        $qRow = pg_fetch_assoc($resultQ);
        $questionId = (int)$qRow['id'];
    
        // Prepare a structured array to send to the template
        $question = [
            'id'              => $questionId,
            'question_set_id' => (int)$qRow['question_set_id'],
            'user_id'         => (int)$qRow['user_id'],
            'category'        => $qRow['category'],
            'points'          => (int)$qRow['points'],
            'question_type'   => $qRow['question_type'],
            'text'            => $qRow['text'],
            'created_at'      => $qRow['created_at'],
            // these will be filled depending on type:
            'options'         => [],      // for multiple_choice
            'correct_index'   => null,    // for multiple_choice
            'is_true'         => null,    // for true_false
            'answer_text'     => null,    // for response
        ];
    
        // 2) Load extra data depending on question_type
        if ($question['question_type'] === 'multiple_choice') {
            // Options
            $sqlOpts = "
                SELECT option_index, option_text
                FROM multiple_choice_option
                WHERE question_id = $1
                ORDER BY option_index
            ";
            $resOpts = pg_query_params($this->db, $sqlOpts, [$questionId]);
    
            $options = [];
            if ($resOpts) {
                while ($row = pg_fetch_assoc($resOpts)) {
                    $idx = (int)$row['option_index'];
                    $options[$idx] = $row['option_text'];
                }
            }
    
            // Correct index
            $sqlAns = "
                SELECT correct_index
                FROM multiple_choice_answer
                WHERE question_id = $1
                LIMIT 1
            ";
            $resAns = pg_query_params($this->db, $sqlAns, [$questionId]);
    
            $correctIndex = null;
            if ($resAns && pg_num_rows($resAns) > 0) {
                $correctRow = pg_fetch_assoc($resAns);
                $correctIndex = (int)$correctRow['correct_index'];
            }
    
            $question['options']       = $options;
            $question['correct_index'] = $correctIndex;
    
        } elseif ($question['question_type'] === 'true_false') {
            $sqlTF = "
                SELECT is_true
                FROM true_false_answer
                WHERE question_id = $1
                LIMIT 1
            ";
            $resTF = pg_query_params($this->db, $sqlTF, [$questionId]);
    
            if ($resTF && pg_num_rows($resTF) > 0) {
                $rowTF = pg_fetch_assoc($resTF);
                $question['is_true'] = ($rowTF['is_true'] === 't'); // PostgreSQL bool
            }
    
        } elseif ($question['question_type'] === 'response') {
            $sqlResp = "
                SELECT answer_text
                FROM response_answer
                WHERE question_id = $1
                LIMIT 1
            ";
            $resResp = pg_query_params($this->db, $sqlResp, [$questionId]);
    
            if ($resResp && pg_num_rows($resResp) > 0) {
                $rowResp = pg_fetch_assoc($resResp);
                $question['answer_text'] = $rowResp['answer_text'];
            }
        }
    
        // 3) Render the template
        include __DIR__ . '/../public/templates/header.php';
        include __DIR__ . '/../public/templates/question.php';
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

    // Creates a default Marvel starter set for this user if it doesn't already exist
    private function ensureDefaultSetForUser($userId) {
        // 1) Check if they already have this set
        $checkSql = "
            SELECT id
            FROM question_set
            WHERE user_id = $1 AND title = $2
            LIMIT 1
        ";
        $checkRes = pg_query_params($this->db, $checkSql, [$userId, 'Marvel Starter Set']);

        if ($checkRes && pg_num_rows($checkRes) > 0) {
            // Already has the starter set
            return;
        }

        // 2) Create the question_set
        $insertSetSql = "
            INSERT INTO question_set (user_id, title)
            VALUES ($1, $2)
            RETURNING id
        ";
        $setRes = pg_query_params($this->db, $insertSetSql, [$userId, 'Marvel Starter Set']);
        if (!$setRes || pg_num_rows($setRes) === 0) {
            return;
        }

        $setRow = pg_fetch_assoc($setRes);
        $setId  = (int)$setRow['id'];

        // 3) Define questions in PHP arrays
        // NOTE: option_index is 0,1,2,3 and correct_index matches that.
        $questions = [
            // ==== AUTHOR (Comics Writers) ====
            [
                'category'      => 'author',
                'points'        => 100,
                'type'          => 'multiple_choice',
                'text'          => 'Who co-created Spider-Man?',
                'options'       => ['Jack Kirby', 'Stan Lee', 'Steve Ditko', 'Todd McFarlane'],
                'correct_index' => 1, // B) Stan Lee
            ],
            [
                'category' => 'author',
                'points'   => 200,
                'type'     => 'true_false',
                'text'     => 'True or False: Stan Lee created the X-Men.',
                'is_true'  => true,
            ],
            [
                'category'      => 'author',
                'points'        => 300,
                'type'          => 'multiple_choice',
                'text'          => 'Which writer is best known for the 2007 “Civil War” comic storyline?',
                'options'       => ['Mark Millar', 'Frank Miller', 'Brian Michael Bendis', 'Jonathan Hickman'],
                'correct_index' => 0, // A) Mark Millar
            ],
            [
                'category'    => 'author',
                'points'      => 400,
                'type'        => 'response',
                'text'        => 'Name the writer who created Miles Morales.',
                'answer_text' => 'Brian Michael Bendis',
            ],
            [
                'category'      => 'author',
                'points'        => 500,
                'type'          => 'multiple_choice',
                'text'          => 'Which comic writer is responsible for the long, influential “Infinity Gauntlet” storyline?',
                'options'       => ['Jim Starlin', 'Chris Claremont', 'Ed Brubaker', 'Jason Aaron'],
                'correct_index' => 0, // A) Jim Starlin
            ],

            // ==== CHARACTER ====
            [
                'category'      => 'character',
                'points'        => 100,
                'type'          => 'multiple_choice',
                'text'          => 'What is Captain America’s real name?',
                'options'       => ['Steve Rogers', 'John Walker', 'Sam Wilson', 'James Barnes'],
                'correct_index' => 0, // A
            ],
            [
                'category' => 'character',
                'points'   => 200,
                'type'     => 'true_false',
                'text'     => 'True or False: Thor’s hammer is named Gungnir.',
                'is_true'  => false,
            ],
            [
                'category'      => 'character',
                'points'        => 300,
                'type'          => 'multiple_choice',
                'text'          => 'Which hero is known as the “Sorcerer Supreme”?',
                'options'       => ['Loki', 'Scarlet Witch', 'Doctor Strange', 'The Ancient One'],
                'correct_index' => 2, // C
            ],
            [
                'category'    => 'character',
                'points'      => 400,
                'type'        => 'response',
                'text'        => 'What metal is bonded to Wolverine’s skeleton?',
                'answer_text' => 'Adamantium',
            ],
            [
                'category'      => 'character',
                'points'        => 500,
                'type'          => 'multiple_choice',
                'text'          => 'Which character first appeared in “Fantastic Four #52” (1966)?',
                'options'       => ['Nightcrawler', 'Black Panther', 'Vision', 'Quicksilver'],
                'correct_index' => 1, // B
            ],

            // ==== MOVIES (MCU) ====
            [
                'category'      => 'movies',
                'points'        => 100,
                'type'          => 'multiple_choice',
                'text'          => 'Who was the villain in the first Iron Man movie (2008)?',
                'options'       => ['Whiplash', 'Vulture', 'Obadiah Stane', 'Ultron'],
                'correct_index' => 2, // C
            ],
            [
                'category' => 'movies',
                'points'   => 200,
                'type'     => 'true_false',
                'text'     => 'True or False: “Avengers: Endgame” was released before “Avengers: Infinity War.”',
                'is_true'  => false,
            ],
            [
                'category'      => 'movies',
                'points'        => 300,
                'type'          => 'multiple_choice',
                'text'          => 'In “Black Panther,” what is the name of the metal that powers Wakanda?',
                'options'       => ['Vibranium', 'Uru', 'Adamantium', 'Titanium'],
                'correct_index' => 0, // A
            ],
            [
                'category'    => 'movies',
                'points'      => 400,
                'type'        => 'response',
                'text'        => 'Name the director of “Thor: Ragnarok.”',
                'answer_text' => 'Taika Waititi',
            ],
            [
                'category'      => 'movies',
                'points'        => 500,
                'type'          => 'multiple_choice',
                'text'          => 'Which movie introduced the concept of the Multiverse into the MCU first?',
                'options'       => ['Loki (series)', 'Doctor Strange', 'Ant-Man', 'Spider-Man: Far From Home'],
                'correct_index' => 2, // C (Ant-Man – Quantum Realm groundwork)
            ],

            // ==== QUOTES ====
            [
                'category'      => 'quotes',
                'points'        => 100,
                'type'          => 'multiple_choice',
                'text'          => '“I am Iron Man.” – Who said it?',
                'options'       => ['War Machine', 'Tony Stark', 'Nick Fury', 'Loki'],
                'correct_index' => 1, // B
            ],
            [
                'category' => 'quotes',
                'points'   => 200,
                'type'     => 'true_false',
                'text'     => 'True or False: “On your left” is a famous quote said by Falcon.',
                'is_true'  => true,
            ],
            [
                'category'      => 'quotes',
                'points'        => 300,
                'type'          => 'multiple_choice',
                'text'          => 'Who said: “We are Groot”?',
                'options'       => ['Rocket', 'Groot', 'Star-Lord', 'Gamora'],
                'correct_index' => 1, // B (Groot)
            ],
            [
                'category'    => 'quotes',
                'points'      => 400,
                'type'        => 'response',
                'text'        => 'Which villain said, “Perfectly balanced, as all things should be”?',
                'answer_text' => 'Thanos',
            ],
            [
                'category'      => 'quotes',
                'points'        => 500,
                'type'          => 'multiple_choice',
                'text'          => 'Which character said: “What is grief, if not love persevering?”',
                'options'       => ['Wanda', 'Vision', 'Doctor Strange', 'Hawkeye'],
                'correct_index' => 1, // B (Vision)
            ],

            // ==== EVENT ====
            [
                'category'      => 'event',
                'points'        => 100,
                'type'          => 'multiple_choice',
                'text'          => 'What Marvel event revolves around a superhero registration law?',
                'options'       => ['Secret Invasion', 'Civil War', 'House of M', 'Secret Wars'],
                'correct_index' => 1, // B
            ],
            [
                'category' => 'event',
                'points'   => 200,
                'type'     => 'true_false',
                'text'     => 'True or False: “Secret Invasion” features Skrulls replacing heroes.',
                'is_true'  => true,
            ],
            [
                'category'      => 'event',
                'points'        => 300,
                'type'          => 'multiple_choice',
                'text'          => 'Which event wiped out almost all mutants?',
                'options'       => ['Age of Apocalypse', 'House of M', 'Infinity', 'Dark Reign'],
                'correct_index' => 1, // B
            ],
            [
                'category'    => 'event',
                'points'      => 400,
                'type'        => 'response',
                'text'        => 'Name the event where Thanos collects the six Infinity Stones.',
                'answer_text' => 'Infinity Gauntlet',
            ],
            [
                'category'      => 'event',
                'points'        => 500,
                'type'          => 'multiple_choice',
                'text'          => 'Which event involved multiple universes collapsing into one “Battleworld”?',
                'options'       => ['Annihilation', 'Siege', 'Secret Wars (2015)', 'Fear Itself'],
                'correct_index' => 2, // C
            ],
        ];

        // 4) Insert questions + answers
        foreach ($questions as $q) {
            $insertQSql = "
                INSERT INTO question (question_set_id, user_id, category, points, question_type, text)
                VALUES ($1, $2, $3, $4, $5, $6)
                RETURNING id
            ";

            $resQ = pg_query_params(
                $this->db,
                $insertQSql,
                [
                    $setId,
                    $userId,
                    $q['category'],
                    $q['points'],
                    $q['type'],
                    $q['text']
                ]
            );

            if (!$resQ || pg_num_rows($resQ) === 0) {
                continue;
            }

            $rowQ = pg_fetch_assoc($resQ);
            $questionId = (int)$rowQ['id'];

            if ($q['type'] === 'multiple_choice') {
                // options
                foreach ($q['options'] as $idx => $optText) {
                    $optSql = "
                        INSERT INTO multiple_choice_option (question_id, option_index, option_text)
                        VALUES ($1, $2, $3)
                    ";
                    pg_query_params($this->db, $optSql, [$questionId, $idx, $optText]);
                }

                // correct index
                $ansSql = "
                    INSERT INTO multiple_choice_answer (question_id, correct_index)
                    VALUES ($1, $2)
                ";
                pg_query_params($this->db, $ansSql, [$questionId, $q['correct_index']]);

            } elseif ($q['type'] === 'true_false') {
                $tfSql = "
                    INSERT INTO true_false_answer (question_id, is_true)
                    VALUES ($1, $2)
                ";
                pg_query_params($this->db, $tfSql, [$questionId, $q['is_true']]);

            } elseif ($q['type'] === 'response') {
                $respSql = "
                    INSERT INTO response_answer (question_id, answer_text)
                    VALUES ($1, $2)
                ";
                pg_query_params($this->db, $respSql, [$questionId, $q['answer_text']]);
            }
        }
    }


    public function errors() { 
        return $this->errors;
    }
}
