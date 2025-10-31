<?php
// Read from environment variables
$host     = getenv('DB_HOST')     ?: 'localhost';
$database = getenv('DB_NAME')     ?: 'qrk9cs';
$user     = getenv('DB_USER')     ?: 'qrk9cs';
$password = getenv('DB_PASSWORD') ?: '3OhZKsDHD5Mc';

// Try to connect to cs4610 db (intended for deployment)
$db = @pg_connect("host=$host dbname=$database user=$user password=$password");

// If the connection failed, try local (intended for testing)
if (!$db) {
    $local_host     = getenv('LOCAL_DB_HOST') ?: 'localhost';
    $local_port     = getenv('LOCAL_DB_PORT') ?: '5432';
    $local_name     = getenv('LOCAL_DB_NAME') ?: 'cs4640localdb';
    $local_user     = getenv('LOCAL_DB_USER') ?: 'cs4640localuser';
    $local_password = getenv('LOCAL_DB_PASSWORD') ?: 'cs4640LocalUser'; 

    $db = pg_connect("host=$local_host port=$local_port dbname=$local_name user=$local_user password=$local_password");
}

// If both failed
if (!$db) {
    die("Huh, something went wrong, connecting to database failed, check credentials.");
}
