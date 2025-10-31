<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

require __DIR__ . '/../src/db.php'; 
if (!$db) { die('DB connect failed'); }

$sql = file_get_contents(__DIR__ . '/../init.sql');
if ($sql === false) { die('Could not read init.sql'); }

$res = pg_query($db, $sql);
if ($res === false) {
  die('Error: ' . htmlspecialchars(pg_last_error($db)));
}
echo 'OK: init.sql executed.';
