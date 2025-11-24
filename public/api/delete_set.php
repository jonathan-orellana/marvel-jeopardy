<?php

function handle_deleteSetFromDatabase($db, $set_id, $user_id) {
    $result = ["ok" => false, "errors" => []];

    if ($set_id <= 0) {
        $result["errors"][] = "Invalid or missing set id.";
        return $result;
    }

    $ownRes = @pg_query_params(
        $db,
        "SELECT id FROM question_set WHERE id=$1 AND user_id=$2",
        [$set_id, $user_id]
    );

    if (!$ownRes || pg_num_rows($ownRes) === 0) {
        $result["errors"][] = "Set not found or not owned by you.";
        return $result;
    }

    @pg_query($db, "BEGIN");

    try {

        @pg_query_params($db,
            "DELETE FROM multiple_choice_answer 
             WHERE question_id IN (SELECT id FROM question WHERE question_set_id=$1)",
             [$set_id]
        );

        @pg_query_params($db,
            "DELETE FROM multiple_choice_option 
             WHERE question_id IN (SELECT id FROM question WHERE question_set_id=$1)",
             [$set_id]
        );

        @pg_query_params($db,
            "DELETE FROM true_false_answer 
             WHERE question_id IN (SELECT id FROM question WHERE question_set_id=$1)",
             [$set_id]
        );

        @pg_query_params($db,
            "DELETE FROM response_answer 
             WHERE question_id IN (SELECT id FROM question WHERE question_set_id=$1)",
             [$set_id]
        );

        @pg_query_params($db,
            "DELETE FROM question WHERE question_set_id=$1",
            [$set_id]
        );

        $delSetRes = @pg_query_params(
            $db,
            "DELETE FROM question_set WHERE id=$1 AND user_id=$2",
            [$set_id, $user_id]
        );

        if (!$delSetRes) {
            throw new Exception(pg_last_error($db));
        }

        @pg_query($db, "COMMIT");

        $result["ok"] = true;
        return $result;

    } catch (Exception $e) {
        @pg_query($db, "ROLLBACK");
        $result["errors"][] = "Database error: " . $e->getMessage();
        return $result;
    }
}
