<?php

$possiblePaths = [
    '/var/www/html/core/php/core.inc.php',
    $_SERVER['DOCUMENT_ROOT'] . '/core/php/core.inc.php',
];

foreach ($possiblePaths as $path) {
    if (file_exists($path)) {
        require_once $path;
        break;
    }
}
