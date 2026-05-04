<?php

require_once __DIR__ . "/../../../vendor/autoload.php";

use Nexus\Security\Camera\ReolinkSecurityManager;

echo "=== Test de connexion Reolink ===\n\n";

$manager = new ReolinkSecurityManager("192.168.1.244", "admin", "L1mp3rm@n3nce");

echo "1. Test armement (arm)...\n";
$result = $manager->armAll();
echo "Action: " . $result['action'] . "\n";
echo "Succès: " . ($result['success'] ? 'OUI' : 'NON') . "\n";
// Utilisation de json_encode pour voir le détail sans erreur de conversion
echo "Réponse détaillée: " . json_encode($result['response'], JSON_PRETTY_PRINT) . "\n\n";

sleep(2);

echo "2. Test désarmement (disarm)...\n";
$result = $manager->disarmAll();
echo "Action: " . $result['action'] . "\n";
echo "Succès: " . ($result['success'] ? 'OUI' : 'NON') . "\n";
echo "Réponse détaillée: " . json_encode($result['response'], JSON_PRETTY_PRINT) . "\n\n";

echo "=== Fin du test ===\n";