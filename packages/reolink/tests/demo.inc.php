<?php

require_once __DIR__ . "/../core/php/reolink.inc.php";


function getCameraStatus(): array
{
    return [
        'push' => camera_getPushStatus() ? "Activées" : "Désactivées",
        'mail' => camera_getMailStatus() ? "Activées" : "Désactivées",
        'rec' => camera_getRecStatus() ? "Activé" : "Désactivé",
        'siren' => camera_getSirenStatus() ? "Activée" : "Désactivée",
        'spotlight' => camera_getSpotlightStatus() ? "Activé" : "Désactivé"
    ];
}

function printCameraStatus(): void
{
    $status = getCameraStatus();
    echo "\n=== État actuel de la caméra Reolink ===\n";
    echo "Notifications Push : {$status['push']}\n";
    echo "Notifications Mail : {$status['mail']}\n";
    echo "Enregistrement : {$status['rec']}\n";
    echo "Sirène : {$status['siren']}\n";
    echo "Projecteur : {$status['spotlight']}\n";
    echo "========================================\n\n";
}

echo "=== Test Reolink Camera ===\n\n";

echo "1. Armement de la caméra...\n";
camera_arm();
printCameraStatus();    

sleep(2);

echo "2. Désarmement de la caméra...\n";
camera_disarm();
printCameraStatus();    

echo "\n=== Fin du test ===\n";