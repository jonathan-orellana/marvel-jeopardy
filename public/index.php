<?php
// Show errors during development
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Bring in the controller (plain include; no autoload magic)
require_once __DIR__ . '/../src/MarvelController.php';

// Instantiate the controller with GET params and run
$controller = new MarvelController($_GET);
$controller->run();
