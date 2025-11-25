<?php
require __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

$db = @pg_connect(sprintf(
    "host=%s port=%s dbname=%s user=%s password=%s",
    $_ENV['SERVER_DB_HOST'],
    $_ENV['SERVER_DB_PORT'],
    $_ENV['SERVER_DB_NAME'],
    $_ENV['SERVER_DB_USER'],
    $_ENV['SERVER_DB_PASSWORD']
));

if (!$db) {
    $db = @pg_connect(sprintf(
        "host=%s port=%s dbname=%s user=%s password=%s",
        $_ENV['LOCAL_DB_HOST'],
        $_ENV['LOCAL_DB_PORT'],
        $_ENV['LOCAL_DB_NAME'],
        $_ENV['LOCAL_DB_USER'],
        $_ENV['LOCAL_DB_PASSWORD']
    ));
}

if (!$db) {
    die("DB connection failed.");
}
