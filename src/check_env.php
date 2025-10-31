<?php
$vars = [
  'DB_HOST', 'DB_PORT', 'DB_NAME', 'DB_USER', 'DB_PASSWORD',
  'LOCAL_DB_HOST', 'LOCAL_DB_PORT', 'LOCAL_DB_NAME', 'LOCAL_DB_USER', 'LOCAL_DB_PASSWORD'
];

foreach ($vars as $v) {
    echo $v . ' = "' . getenv($v) . '"' . PHP_EOL;
}
