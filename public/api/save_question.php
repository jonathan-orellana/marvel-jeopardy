<?php
function handle_saveQuestionToDatabase($db) {
    $result = [
        'ok' => false,
        'errors' => []
    ];

    // Read
    $json = file_get_contents("php://input");
    $_POST = json_decode($json, true) ?? [];

    $questions = $_POST['questions'] ?? null;
    $user_id = $_POST['user_id'] ?? null;
    $question_set_id = $_POST['question_set_id'] ?? null;

    // Validation
    if (!$questions || !is_array($questions)) {
        $result['errors'][] = "No questions sent";
        return $result;
    }

    if (!$user_id) {
        $result['errors'][] = "Missing user_id";
        return $result;
    }

    if (!$question_set_id) {
        $result['errors'][] = "Missing question_set_id";
        return $result;
    }

    // Query
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

    // Save to database
    try {
        $db->beginTransaction();

        foreach ($questions as $q) {

            $insertQuestion->execute([
                ":qsid" => $question_set_id,
                ":uid"  => $user_id,
                ":type" => $q["type"],
                ":text" => $q["text"]
            ]);

            $question_id = $db->lastInsertId();

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

            if ($q["type"] === "trueFalse") {
                $insertTF->execute([
                    ":qid"    => $question_id,
                    ":is_true" => $q["correct"] ? 1 : 0
                ]);
            }

            if ($q["type"] === "response") {
                $insertResponse->execute([
                    ":qid"   => $question_id,
                    ":answer" => $q["correct"]
                ]);
            }
        }

        $db->commit();
        $result['ok'] = true;
        return $result;

    } catch (Exception $e) {
        $db->rollBack();
        $result['errors'][] = $e->getMessage();
        return $result;
    }
}
