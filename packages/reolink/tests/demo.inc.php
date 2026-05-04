<?php

require_once __DIR__ . "/../core/php/reolink.inc.php";

echo "=== Test Reolink Camera ===\n\n";

echo "1. Armement de la caméra...\n";
$result = camera_arm();
echo "Action: " . $result['action'] . "\n";
echo "Succès: " . ($result['success'] ? 'OUI' : 'NON') . "\n";
if (!$result['success']) {
    echo "Réponse: " . $result['response'] . "\n";
}
echo "\n";

sleep(2);

echo "2. Désarmement de la caméra...\n";
$result = camera_disarm();
echo "Action: " . $result['action'] . "\n";
echo "Succès: " . ($result['success'] ? 'OUI' : 'NON') . "\n";
if (!$result['success']) {
    echo "Réponse: " . $result['response'] . "\n";
}
echo "\n";

echo "=== Fin du test ===\n";