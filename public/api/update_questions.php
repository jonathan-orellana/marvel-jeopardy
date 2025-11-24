<?php
function handle_updateQuestionsToDatabase($db, $questions, $user_id) {
  $result = ["ok"=>false,"errors"=>[]];

  if (!is_array($questions)) {
    $result["errors"][] = "Bad payload.";
    return $result;
  }

  @pg_query($db, "BEGIN");

  try {
    foreach ($questions as $q) {
      $id = (int)($q["id"] ?? 0);
      $type = $q["question_type"] ?? "";
      $text = trim($q["text"] ?? "");

      if ($id<=0 || $text==="" || !in_array($type, ["multipleChoice","trueFalse","response"])) {
        throw new Exception("Invalid question data.");
      }

      $upd = @pg_query_params($db,
        "UPDATE question SET question_type=$1, text=$2
         WHERE id=$3 AND user_id=$4",
        [$type, $text, $id, $user_id]
      );
      if (!$upd) throw new Exception(pg_last_error($db));

      @pg_query_params($db, "DELETE FROM multiple_choice_option WHERE question_id=$1", [$id]);
      @pg_query_params($db, "DELETE FROM multiple_choice_answer WHERE question_id=$1", [$id]);
      @pg_query_params($db, "DELETE FROM true_false_answer WHERE question_id=$1", [$id]);
      @pg_query_params($db, "DELETE FROM response_answer WHERE question_id=$1", [$id]);

      if ($type === "multipleChoice") {
        $options = $q["options"] ?? [];
        $correct = $q["correct_index"];

        if (!is_array($options) || count($options) !== 4) {
          throw new Exception("MC needs 4 options.");
        }
        if ($correct === null) {
          throw new Exception("MC needs correct_index.");
        }

        foreach ($options as $i => $optText) {
          $optText = trim($optText);
          if ($optText==="") throw new Exception("MC options can't be empty.");

          $insOpt = @pg_query_params($db,
            "INSERT INTO multiple_choice_option (question_id, option_index, option_text)
             VALUES ($1,$2,$3)",
            [$id, $i, $optText]
          );
          if (!$insOpt) throw new Exception(pg_last_error($db));
        }

        $insAns = @pg_query_params($db,
          "INSERT INTO multiple_choice_answer (question_id, correct_index)
           VALUES ($1,$2)",
          [$id, (int)$correct]
        );
        if (!$insAns) throw new Exception(pg_last_error($db));
      }

      if ($type === "trueFalse") {
        if (!array_key_exists("is_true", $q) || $q["is_true"] === null) {
          throw new Exception("TF needs is_true.");
        }

        $insTF = @pg_query_params($db,
          "INSERT INTO true_false_answer (question_id, is_true)
           VALUES ($1,$2)",
          [$id, (bool)$q["is_true"]]
        );
        if (!$insTF) throw new Exception(pg_last_error($db));
      }

      if ($type === "response") {
        $ans = trim($q["answer_text"] ?? "");
        if ($ans==="") throw new Exception("Response needs answer_text.");

        $insR = @pg_query_params($db,
          "INSERT INTO response_answer (question_id, answer_text)
           VALUES ($1,$2)",
          [$id, $ans]
        );
        if (!$insR) throw new Exception(pg_last_error($db));
      }
    }

    @pg_query($db, "COMMIT");
    $result["ok"] = true;
    return $result;

  } catch (Exception $e) {
    @pg_query($db, "ROLLBACK");
    $result["errors"][] = $e->getMessage();
    return $result;
  }
}
