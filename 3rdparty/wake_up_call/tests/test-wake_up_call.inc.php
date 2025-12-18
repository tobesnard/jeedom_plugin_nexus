<?php

require_once __DIR__ . '/../core/php/wake_up_call.inc.php';

use Nexus\Multimedia\WakeUpCall\WakeUpCall;

/**
 * Fonctions de test
 */
function test_bunny()
{
    echo "[Test] Lancement Big Buck Bunny...\n";
    wakeUpCall_bunny();
}

function test_siren()
{
    echo "[Test] Lancement Sirène...\n";
    wakeUpCall_siren();
}

function test_wakeUp()
{
    echo "[Test] Lancement MP3 par défaut...\n";
    wakeUpCall_wakeUp();
}

function test_stop()
{
    echo "[Test] Arrêt du flux...\n";
    wakeUpCall_stop();
}

/**
 * Logique d'exécution
 */
$action = $argv[1] ?? null;

try {
    switch ($action) {
        case 'bunny':
            test_bunny();
            break;
        case 'siren':
            test_siren();
            break;
        case 'wakeup':
            test_wakeUp();
            break;
        case 'stop':
            test_stop();
            break;
        default:
            echo "Usage: php test_nexus.php [bunny|siren|wakeup|stop]\n";
            exit(1);
    }
    echo "Commande envoyée avec succès.\n";
    sleep(5);
    wakeUpCall_stop();
} catch (Exception $e) {
    echo "ERREUR : " . $e->getMessage() . "\n";
}
