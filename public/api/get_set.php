<?php

function handle_getSetWithQuestions($db, $set_id, $user_id) {
    $result = [
        "ok" => false,
        "errors" => [],
        "set" => null,
        "questions" => []
    ];

    if ($set_id <= 0) {
        $result["errors"][] = "Invalid set id.";
        return $result;
    }

    // Get the set
    $setRes = @pg_query_params($db,
        "SELECT id, title, created_at
         FROM question_set
         WHERE id = $1 AND user_id = $2",
        [$set_id, $user_id]
    );

    if (!$setRes || pg_num_rows($setRes) === 0) {
        $result["errors"][] = "Set not found.";
        return $result;
    }

    $setData = pg_fetch_assoc($setRes);

    // Get questions
    $qRes = @pg_query_params($db,
        "SELECT 
            q.id,
            q.question_type,
            q.text,
            q.category,
            q.points,

            COALESCE(
              json_agg(mco.option_text ORDER BY mco.option_index)
                FILTER (WHERE mco.option_text IS NOT NULL),
              '[]'
            ) AS options,

            mca.correct_index,
            tfa.is_true,
            ra.answer_text

         FROM question q
         LEFT JOIN multiple_choice_option mco ON mco.question_id = q.id
         LEFT JOIN multiple_choice_answer mca ON mca.question_id = q.id
         LEFT JOIN true_false_answer tfa ON tfa.question_id = q.id
         LEFT JOIN response_answer ra ON ra.question_id = q.id

         WHERE q.question_set_id = $1 AND q.user_id = $2

         GROUP BY q.id, mca.correct_index, tfa.is_true, ra.answer_text
         ORDER BY q.id ASC",
        [$set_id, $user_id]
    );

    if (!$qRes) {
        $result["errors"][] = "Could not load questions.";
        // $result["errors"][] = pg_last_error($db);
        return $result;
    }

    $questions = [];
    while ($q = pg_fetch_assoc($qRes)) {
        $questions[] = $q;
    }

    $result["ok"] = true;
    $result["set"] = $setData;
    $result["questions"] = $questions;
    return $result;
}
