<?php
/*
Website:
https://cs4640.cs.virginia.edu/qrk9cs/marvel-jeopardy/public/index.php

*/

// Show errors during development
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Bring in the controller
require_once __DIR__ . '/../src/controller.php';

// Instantiate the controller with GET params
$controller = new MarvelController($_GET);

// Run controller 
$controller->run();