<?php

$db = null;

if (!$db) {
    $local_host     = getenv('LOCAL_DB_HOST') ?: 'localhost';
    $local_port     = getenv('LOCAL_DB_PORT') ?: '5432';
    $local_name     = getenv('LOCAL_DB_NAME') ?: 'cs4640localdb';
    $local_user     = getenv('LOCAL_DB_USER') ?: 'cs4640localuser';
    $local_password = getenv('LOCAL_DB_PASSWORD') ?: 'cs4640LocalUser';

    $db = pg_connect("host=$local_host port=$local_port dbname=$local_name user=$local_user password=$local_password");
}

if (!$db) {
    die("DB connection failed: " . pg_last_error());
}

$initPath = __DIR__ . '/init.sql';

if (!file_exists($initPath)) {
    die("init.sql not found at: $initPath");
}

$sql = file_get_contents($initPath);

if ($sql === false) {
    die("Could not read init.sql");
}

$result = pg_query($db, $sql);

if (!$result) {
    die("Error running init.sql:\n" . pg_last_error($db));
}

echo "init.sql ran successfully. Tables created.\n";

?>
