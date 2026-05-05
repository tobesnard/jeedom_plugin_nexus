<?php

require_once __DIR__ . "/../core/php/reolink.inc.php";


function getCameraStatus(): array
{
    return [
        'push' => camera_getPushStatus() ? "Activées" : "Désactivées",
        'mail' => camera_getMailStatus() ? "Activées" : "Désactivées",
        'rec' => camera_getRecStatus() ? "Activé" : "Désactivé",
        'siren' => camera_getSirenStatus() ? "Activée" : "Désactivée",
        'spotlight' => camera_getSpotlightStatus() ? "Activé" : "Désactivé",
        'buzzer' => camera_getBuzzerStatus() ? "Activé" : "Désactivé"
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
    echo "Buzzer : {$status['buzzer']}\n";
    echo "========================================\n\n";
}

function arm_disarm_test(): void
{
    echo "=== Test d'armement/désarmement de la caméra Reolink ===\n\n";

    echo "1. Armement de la caméra...\n";
    camera_arm();
    printCameraStatus();    

    sleep(2);

    echo "2. Désarmement de la caméra...\n";
    camera_disarm();
    printCameraStatus();    

    echo "\n=== Fin du test ===\n";
}

function moving_test(): void
{
    echo "=== Test de déplacement de la caméra Reolink ===\n\n";

    echo "1. Déplacement vers la gauche...\n";
    camera_ptzCtrlLeft();
    sleep(2);

    echo "2. Déplacement vers la droite...\n";
    camera_ptzCtrlRight();
    sleep(2);

    echo "3. Déplacement vers le haut...\n";
    camera_ptzCtrlUp();
    sleep(2);

    echo "4. Déplacement vers le bas...\n";
    camera_ptzCtrlDown();
    sleep(2);

    echo "\n=== Fin du test ===\n";
}

function zoom_test(): void
{
    echo "=== Test de zoom de la caméra Reolink ===\n\n";

    $manager = new \Nexus\Security\Camera\ReolinkSecurityManager(
        getenv('REOLINK_IP'),
        getenv('REOLINK_USERNAME'),
        getenv('REOLINK_PASSWORD')
    );

    echo "1. Zoom avant pendant 3 secondes...\n";
    $manager->zoomInc(20); // vitesse réduite à 20
    sleep(3);
    $manager->stopMove();
    echo "Stop\n";

    sleep(2);

    echo "2. Zoom arrière pendant 3 secondes...\n";
    $manager->zoomDec(20);
    sleep(3);
    $manager->stopMove();
    echo "Stop\n";

    echo "\n=== Fin du test ===\n";
}

// arm_disarm_test();
// moving_test();
zoom_test();