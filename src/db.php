<?php
// Read from environment variables
$host     = getenv('DB_HOST')     ?: 'db';
$port     = getenv('DB_PORT')     ?: '5432';
$database = getenv('DB_NAME')     ?: 'example';
$user     = getenv('DB_USER')     ?: 'localuser';
$password = getenv('DB_PASSWORD') ?: 'cs4640LocalUser!';

// Try to connect to cs4610 db (intended for deployment)
$db = @pg_connect("host=$host port=$port dbname=$database user=$user password=$password");

// If the connection failed, try local (intended for testing)
if (!$db) {
    $local_host     = getenv('LOCAL_DB_HOST');
    $local_port     = getenv('LOCAL_DB_PORT');
    $local_name     = getenv('LOCAL_DB_NAME');
    $local_user     = getenv('LOCAL_DB_USER');
    $local_password = getenv('LOCAL_DB_PASSWORD');  

    $db = pg_connect("host=$host port=$port dbname=$database user=$user password=$password");
}

// If both failed
if (!$db) {
    die("Huh, something went wrong, connecting to database failed, check credentials.");
}
