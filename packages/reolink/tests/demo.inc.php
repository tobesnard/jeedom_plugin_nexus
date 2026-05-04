<?php

require_once __DIR__ . "/../core/php/reolink.inc.php";


echo "=== Test Reolink Camera ===\n\n";

echo "1. Armement de la caméra...\n";
$result = camera_arm();

sleep(2);

echo "2. Désarmement de la caméra...\n";
$result = camera_disarm();

echo "\n";
echo "=== Fin du test ===\n";