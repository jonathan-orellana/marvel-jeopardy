<?php
function handle_saveQuestionToDatabase($db) {
    $result = [
        'ok' => false,
        'errors' => []
    ];

    // Read JSON body
    $json = file_get_contents("php://input");
    $_POST = json_decode($json, true) ?? [];

    $questions = $_POST['questions'] ?? null;
    $user_id = $_POST['user_id'] ?? null;

    // NEW: title for the set
    $title = trim($_POST['title'] ?? '');

    // If frontend doesn't send question_set_id, we will create one
    $question_set_id = $_POST['question_set_id'] ?? null;

    // -----------------------------
    // Validation
    // -----------------------------
    if (!$questions || !is_array($questions)) {
        $result['errors'][] = "No questions sent";
        return $result;
    }

    if (!$user_id) {
        $result['errors'][] = "Missing user_id";
        return $result;
    }

    // If question_set_id not provided, title is required to create a set
    if (!$question_set_id && $title === '') {
        $result['errors'][] = "Missing title for new question set";
        return $result;
    }

    // -----------------------------
    // Prepare insert statements
    // -----------------------------
    $insertSet = $db->prepare("
        INSERT INTO question_set (title, user_id)
        VALUES (:title, :uid)
    ");

    $updateSetTitle = $db->prepare("
        UPDATE question_set
        SET title = :title
        WHERE id = :qsid
    ");

    $insertQuestion = $db->prepare("
        INSERT INTO question (question_set_id, user_id, question_type, text)
        VALUES (:qsid, :uid, :type, :text)
    ");

    $insertMCOption = $db->prepare("
        INSERT INTO multiple_choice_option (question_id, option_index, option_text)
        VALUES (:qid, :oindex, :otext)
    ");

    $insertMCAnswer = $db->prepare("
        INSERT INTO multiple_choice_answer (question_id, correct_index)
        VALUES (:qid, :correct_index)
    ");

    $insertTF = $db->prepare("
        INSERT INTO true_false_answer (question_id, is_true)
        VALUES (:qid, :is_true)
    ");

    $insertResponse = $db->prepare("
        INSERT INTO response_answer (question_id, answer_text)
        VALUES (:qid, :answer)
    ");

    // -----------------------------
    // Save to database
    // -----------------------------
    try {
        $db->beginTransaction();

        // 1) Create a new question set if needed
        if (!$question_set_id) {
            $insertSet->execute([
                ":title" => $title,
                ":uid"   => $user_id
            ]);
            $question_set_id = $db->lastInsertId();
        } else {
            // Optional: if title is sent and set already exists, update title
            if ($title !== '') {
                $updateSetTitle->execute([
                    ":title" => $title,
                    ":qsid"  => $question_set_id
                ]);
            }
        }

        // 2) Insert each question
        foreach ($questions as $q) {

            $insertQuestion->execute([
                ":qsid" => $question_set_id,
                ":uid"  => $user_id,
                ":type" => $q["type"],
                ":text" => $q["text"]
            ]);

            $question_id = $db->lastInsertId();

            // MULTIPLE CHOICE
            if ($q["type"] === "multipleChoice") {

                foreach ($q["options"] as $i => $opt) {
                    $insertMCOption->execute([
                        ":qid"    => $question_id,
                        ":oindex" => $i,
                        ":otext"  => $opt
                    ]);
                }

                $insertMCAnswer->execute([
                    ":qid"           => $question_id,
                    ":correct_index" => $q["correct"]
                ]);
            }

            // TRUE/FALSE
            if ($q["type"] === "trueFalse") {
                $insertTF->execute([
                    ":qid"     => $question_id,
                    ":is_true" => $q["correct"] ? 1 : 0
                ]);
            }

            // RESPONSE
            if ($q["type"] === "response") {
                $insertResponse->execute([
                    ":qid"    => $question_id,
                    ":answer" => $q["correct"]
                ]);
            }
        }

        $db->commit();
        $result['ok'] = true;
        $result['question_set_id'] = $question_set_id; // return it to frontend
        return $result;

    } catch (Exception $e) {
        $db->rollBack();
        $result['errors'][] = $e->getMessage();
        return $result;
    }
}
