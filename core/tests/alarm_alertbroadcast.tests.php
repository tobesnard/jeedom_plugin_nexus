<?php

namespace Nexus\Alarm;

require_once __DIR__ . "/../class/Alarm/AlertBroadcaster.php";

echo "================================================\n";
echo "           Test AlertBroadcaster \n";
echo "================================================\n";

try {
    // Vérification simple que la classe existe et est chargée
    if (!class_exists('Nexus\Alarm\AlertBroadcaster')) {
        throw new \Error("La classe AlertBroadcast n'est pas définie.");
    }
} catch (\Error $e) {
    echo "Erreur Fatale : La classe AlertBroadcaster n'est pas définie ou a une erreur de syntaxe.\n";
    echo "Message: " . $e->getMessage() . "\n";
    echo "Assurez-vous d'avoir inclus le code complet de la classe SystemStats avec les méthodes statiques.\n";
    exit(1);
}

// Diffuse le message d'information
AlertBroadcaster::infoMessage();

// Diffuser le message de mise en garde
// AlertBroadcaster::warningMessage();

// Activer la sirène des galets avec un son spécifique
// AlertBroadcaster::galetsSirenOn();
// sleep(3);
// AlertBroadcaster::galetsSirenOff();
