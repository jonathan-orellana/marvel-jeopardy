<?php
function handle_saveQuestionToDatabase($db) {
    $result = [
        'ok' => false,
        'errors' => []
    ];

    // Your fetch() sends JSON, not form-data,
    // so convert JSON body into $_POST like you do in signup.
    $json = file_get_contents("php://input");
    $_POST = json_decode($json, true) ?? [];

    // Get data (same style)
    $title = trim($_POST['title'] ?? '');
    $questions = $_POST['questions'] ?? null;

    // Better: get user id from session, not client
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $user_id = $_SESSION['user'] ?? null;

    // Existing set optional
    $question_set_id = $_POST['question_set_id'] ?? null;

    // -----------------------------
    // Validations (early returns)
    // -----------------------------
    if (!$user_id) {
        $result['errors'][] = "You must be logged in.";
        return $result;
    }

    if ($title === '') {
        $result['errors'][] = "Set title is required.";
        return $result;
    }

    if (!$questions || !is_array($questions) || count($questions) === 0) {
        $result['errors'][] = "You must add at least one question.";
        return $result;
    }

    // Validate each question structure
    foreach ($questions as $q) {
        $type = $q['type'] ?? '';
        $text = trim($q['text'] ?? '');

        if ($type === '' || $text === '') {
            $result['errors'][] = "All questions must have a type and text.";
            return $result;
        }

        if ($type === "multipleChoice") {
            $opts = $q['options'] ?? [];
            $correct = $q['correct'] ?? null;

            if (!is_array($opts) || count($opts) < 2) {
                $result['errors'][] = "Multiple choice questions need options.";
                return $result;
            }
            foreach ($opts as $opt) {
                if (trim($opt) === '') {
                    $result['errors'][] = "Multiple choice options cannot be empty.";
                    return $result;
                }
            }
            if ($correct === null) {
                $result['errors'][] = "Multiple choice questions need a correct answer.";
                return $result;
            }
        }

        if ($type === "trueFalse") {
            if (!array_key_exists('correct', $q)) {
                $result['errors'][] = "True/False questions need a correct answer.";
                return $result;
            }
        }

        if ($type === "response") {
            $answer = trim($q['correct'] ?? '');
            if ($answer === '') {
                $result['errors'][] = "Response questions need an answer.";
                return $result;
            }
        }
    }

    // -----------------------------
    // Save (pg_query_params style)
    // -----------------------------
    // Start transaction
    $ok = pg_query($db, "BEGIN");
    if (!$ok) {
        $result['errors'][] = "Database error: " . pg_last_error($db);
        return $result;
    }

    try {
        // 1) Create a new set if needed
        if (!$question_set_id) {
            $setQuery = "
                INSERT INTO question_set (title, user_id)
                VALUES ($1, $2)
                RETURNING id
            ";
            $setRes = pg_query_params($db, $setQuery, [$title, $user_id]);

            if (!$setRes) {
                throw new Exception("Database error: " . pg_last_error($db));
            }

            $question_set_id = (int) pg_fetch_result($setRes, 0, 'id');
        } else {
            // Update title if set already exists
            $updQuery = "UPDATE question_set SET title = $1 WHERE id = $2 AND user_id = $3";
            $updRes = pg_query_params($db, $updQuery, [$title, $question_set_id, $user_id]);

            if (!$updRes) {
                throw new Exception("Database error: " . pg_last_error($db));
            }
        }

        // 2) Insert questions
        foreach ($questions as $q) {
            $type = $q['type'];
            $text = trim($q['text']);

            $qQuery = "
                INSERT INTO question (question_set_id, user_id, question_type, text)
                VALUES ($1, $2, $3, $4)
                RETURNING id
            ";
            $qRes = pg_query_params($db, $qQuery, [
                $question_set_id, $user_id, $type, $text
            ]);

            if (!$qRes) {
                throw new Exception("Database error: " . pg_last_error($db));
            }

            $question_id = (int) pg_fetch_result($qRes, 0, 'id');

            // Multiple choice
            if ($type === "multipleChoice") {
                foreach ($q['options'] as $i => $optText) {
                    $optQuery = "
                        INSERT INTO multiple_choice_option (question_id, option_index, option_text)
                        VALUES ($1, $2, $3)
                    ";
                    $optRes = pg_query_params($db, $optQuery, [$question_id, $i, $optText]);

                    if (!$optRes) {
                        throw new Exception("Database error: " . pg_last_error($db));
                    }
                }

                $ansQuery = "
                    INSERT INTO multiple_choice_answer (question_id, correct_index)
                    VALUES ($1, $2)
                ";
                $ansRes = pg_query_params($db, $ansQuery, [$question_id, $q['correct']]);

                if (!$ansRes) {
                    throw new Exception("Database error: " . pg_last_error($db));
                }
            }

            // True/False
            if ($type === "trueFalse") {
                $tfQuery = "
                    INSERT INTO true_false_answer (question_id, is_true)
                    VALUES ($1, $2)
                ";
                $tfRes = pg_query_params($db, $tfQuery, [$question_id, (bool)$q['correct']]);

                if (!$tfRes) {
                    throw new Exception("Database error: " . pg_last_error($db));
                }
            }

            // Response
            if ($type === "response") {
                $rQuery = "
                    INSERT INTO response_answer (question_id, answer_text)
                    VALUES ($1, $2)
                ";
                $rRes = pg_query_params($db, $rQuery, [$question_id, $q['correct']]);

                if (!$rRes) {
                    throw new Exception("Database error: " . pg_last_error($db));
                }
            }
        }

        pg_query($db, "COMMIT");

        $result['ok'] = true;
        $result['question_set_id'] = $question_set_id;
        return $result;

    } catch (Exception $e) {
        pg_query($db, "ROLLBACK");
        $result['errors'][] = $e->getMessage();
        return $result;
    }
}
