<?php
error_reporting(E_ALL);
ini_set('display_errors', '0');
ini_set('log_errors', '1');
header('Content-Type: application/json; charset=utf-8');

session_start();
require_once __DIR__ . '/../../src/db.php';

if (!isset($_SESSION['user_id'])) {
  http_response_code(401);
  echo json_encode(['ok' => false, 'error' => 'Not logged in']);
  exit;
}

$user_id = $_SESSION['user_id'];
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
  $set_id = isset($_GET['set_id']) ? (int)$_GET['set_id'] : 0;

  if ($set_id > 0) {
    $q1 = pg_query_params($db,
      "SELECT id, title, created_at FROM question_set WHERE id = $1 AND user_id = $2",
      [$set_id, $user_id]
    );
    if (!$q1 || pg_num_rows($q1) === 0) {
      echo json_encode(['ok' => false, 'error' => 'Set not found']); exit;
    }
    $set = pg_fetch_assoc($q1);

    $q2 = pg_query_params($db,
      "SELECT id, type, prompt, options, correct_index, correct_bool, correct_text
       FROM question WHERE set_id = $1 AND user_id = $2 ORDER BY id ASC",
      [$set_id, $user_id]
    );
    $questions = [];
    while ($row = pg_fetch_assoc($q2)) {
      $row['options'] = $row['options'] ? json_decode($row['options'], true) : null;
      $questions[] = $row;
    }

    echo json_encode(['ok' => true, 'set' => $set, 'questions' => $questions]); exit;
  }

  $res = pg_query_params($db,
    "SELECT id, title, created_at FROM question_set WHERE user_id = $1 ORDER BY id DESC",
    [$user_id]
  );
  $sets = [];
  while ($row = pg_fetch_assoc($res)) $sets[] = $row;
  echo json_encode(['ok' => true, 'sets' => $sets]); exit;
}

if ($method === 'POST') {
  if (isset($_POST['action'])) {
    $action = $_POST['action'];

    if ($action === 'update_question') {
      $id     = (int)($_POST['id'] ?? 0);
      $prompt = trim($_POST['prompt'] ?? '');
      if ($id <= 0 || $prompt === '') {
        echo json_encode(['ok' => false, 'error' => 'Missing id or prompt']); exit;
      }
      $res = pg_query_params($db,
        "UPDATE question SET prompt = $1 WHERE id = $2 AND user_id = $3",
        [$prompt, $id, $user_id]
      );
      echo json_encode(['ok' => (bool)$res]); exit;
    }

    if ($action === 'delete_question') {
      $id = (int)($_POST['id'] ?? 0);
      if ($id <= 0) { echo json_encode(['ok' => false, 'error' => 'Missing id']); exit; }
      $res = pg_query_params($db,
        "DELETE FROM question WHERE id = $1 AND user_id = $2",
        [$id, $user_id]
      );

      if (!empty($_POST['redirect'])) {
        header('Location: ' . $_POST['redirect']);
        exit;
      }
      echo json_encode(['ok' => (bool)$res]); exit;
    }

    if ($action === 'delete_set') {
      $set_id = (int)($_POST['set_id'] ?? 0);
      if ($set_id <= 0) { echo json_encode(['ok' => false, 'error' => 'Missing set_id']); exit; }
      $res = pg_query_params($db,
        "DELETE FROM question_set WHERE id = $1 AND user_id = $2",
        [$set_id, $user_id]
      );

      if (!empty($_POST['redirect'])) {
        header('Location: ' . $_POST['redirect']);
        exit;
      }
      echo json_encode(['ok' => (bool)$res]); exit;
    }

    if ($action === 'update_question_full') {
      $id     = (int)($_POST['id'] ?? 0);
      $type   = trim($_POST['type'] ?? '');
      $prompt = trim($_POST['prompt'] ?? '');
      if ($id <= 0 || $type === '' || $prompt === '') {
        echo json_encode(['ok' => false, 'error' => 'Missing id, type, or prompt']); exit;
      }
      $options = null; $ci = null; $cb = null; $ct = null;
      if ($type === 'Multiple Choice') {
        $opts = $_POST['options'] ?? [];
        if (!is_array($opts) || count($opts) < 4) {
          echo json_encode(['ok' => false, 'error' => 'Need 4 options for Multiple Choice']); exit;
        }
        $opts = array_values(array_map('strval', array_slice($opts, 0, 4)));
        $options = json_encode($opts);
        $ci = isset($_POST['correctIndex']) ? (int)$_POST['correctIndex'] : null;
      } elseif ($type === 'True or False') {
        if (!isset($_POST['correctBool'])) { echo json_encode(['ok' => false, 'error' => 'Missing True/False answer']); exit; }
        $cb = $_POST['correctBool'] === '1' ? true : false;
      } elseif ($type === 'Response') {
        $ct = trim($_POST['correctText'] ?? '');
      } else {
        echo json_encode(['ok' => false, 'error' => 'Unknown type']); exit;
      }
      $sql = "UPDATE question
              SET type = $1, prompt = $2, options = $3, correct_index = $4, correct_bool = $5, correct_text = $6
              WHERE id = $7 AND user_id = $8";
      $params = [ $type, $prompt, $options, $ci, $cb, $ct, $id, $user_id ];
      $res = pg_query_params($db, $sql, $params);
      echo json_encode(['ok' => (bool)$res]); exit;
    }

    echo json_encode(['ok' => false, 'error' => 'Unknown action']); exit;
  }

  // JSON body
  $raw = file_get_contents('php://input');
  $data = json_decode($raw, true);
  if (!is_array($data)) { http_response_code(400); echo json_encode(['ok' => false, 'error' => 'Invalid JSON']); exit; }


  if (isset($data['updates']) && is_array($data['updates'])) {
    $updated = 0;
    foreach ($data['updates'] as $u) {
      $id     = (int)($u['id'] ?? 0);
      $type   = trim($u['type'] ?? '');
      $prompt = trim($u['prompt'] ?? '');

      if ($id <= 0 || $prompt === '' || $type === '') { continue; }

      $options = null; $ci = null; $cb = null; $ct = null;

      if ($type === 'Multiple Choice') {
        $opts = $u['options'] ?? [];
        if (!is_array($opts) || count($opts) < 4) { continue; }
        $opts = array_values(array_map('strval', array_slice($opts, 0, 4)));
        $options = json_encode($opts);
        $ci = isset($u['correctIndex']) ? (int)$u['correctIndex'] : null;
      } elseif ($type === 'True or False') {
        if (!isset($u['correctBool'])) { continue; }
        $cb = $u['correctBool'] ? true : false;
      } elseif ($type === 'Response') {
        $ct = trim($u['correctText'] ?? '');
      } else {
        continue;
      }

      $sql = "UPDATE question
              SET prompt = $1,
                  options = $2,
                  correct_index = $3,
                  correct_bool = $4,
                  correct_text = $5
              WHERE id = $6 AND user_id = $7";
      $params = [ $prompt, $options, $ci, $cb, $ct, $id, $user_id ];
      $res = pg_query_params($db, $sql, $params);
      if ($res) { $updated++; }
    }
    echo json_encode(['ok' => true, 'updated' => $updated]); exit;
  }

  // JSON
  $title = trim($data['title'] ?? '');
  $items = $data['questions'] ?? [];
  if ($title === '' || !is_array($items)) { echo json_encode(['ok' => false, 'error' => 'Missing title or questions']); exit; }

  $r1 = pg_query_params($db,
    "INSERT INTO question_set (user_id, title) VALUES ($1, $2) RETURNING id",
    [$user_id, $title]
  );
  if (!$r1) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Failed to create set', 'detail' => pg_last_error($db)]);
    exit;
  }

  $set_row = pg_fetch_assoc($r1);
  $set_id = (int)$set_row['id'];

  $inserted = 0;
  foreach ($items as $q) {
    $type   = trim($q['type']   ?? '');
    $prompt = trim($q['prompt'] ?? '');
    if ($type === '' || $prompt === '') continue;

    $options = null; $ci = null; $cb = null; $ct = null;

    if ($type === 'Multiple Choice') {
      $options = isset($q['options']) && is_array($q['options']) ? json_encode($q['options']) : json_encode(["","","",""]);
      $ci = isset($q['correctIndex']) ? (int)$q['correctIndex'] : null;
    } elseif ($type === 'True or False') {
      $cb = isset($q['correctBool']) ? (bool)$q['correctBool'] : null;
    } else {
      $ct = trim($q['correctText'] ?? '');
    }

    $sql = "INSERT INTO question (user_id, set_id, type, prompt, options, correct_index, correct_bool, correct_text)
            VALUES ($1,$2,$3,$4,$5,$6,$7,$8)";
    $params = [ $user_id, $set_id, $type, $prompt, $options, $ci, $cb, $ct ];
    $res = pg_query_params($db, $sql, $params);
    if ($res) $inserted++;
  }

  echo json_encode(['ok' => true, 'set_id' => $set_id, 'inserted' => $inserted]); exit;
}

http_response_code(405);
echo json_encode(['ok' => false, 'error' => 'Method not allowed']);
