<?php

require_once __DIR__ . '/../../../vendor/autoload.php';

use Nexus\Multimedia\WakeUpCall\WakeUpCall;

// --- CONFIGURATION DU TEST ---
// Remplace 'salon' par un alias présent dans ton config.json
// Ou utilise directement une adresse IP
$target = 'galets';
$mediaName = 'siren';

try {
    echo "--- Démarrage du test pour : $target ---\n";

    // 1. Test du chargement (Factory)
    echo "[1/4] Chargement de l'instance...\n";
    $cast = WakeUpCall::load($target, true); // true force la réinitialisation
    echo "OK : Connecté à l'IP " . $cast->ip . "\n";

    // 2. Test de sauvegarde/sérialisation
    echo "[2/4] Test de la persistance locale...\n";
    $cast->save();
    if (file_exists("/tmp/nexus/")) {
        echo "OK : Fichier de cache créé dans /tmp/nexus/\n";
    }

    // 3. Test de lecture (Siren)
    // Note : Assure-toi que le volume du Chromecast est audible
    echo "[3/4] Envoi du flux audio (Siren)...\n";
    $cast->playMedia($mediaName);
    echo "OK : Commande play envoyée.\n";

    // Pause de 5 secondes pour laisser le temps de vérifier le son
    echo "Lecture en cours... (pause 5s)\n";
    sleep(3);

    // 4. Test d'arrêt
    echo "[4/4] Arrêt du flux...\n";
    $cast->stop();
    echo "OK : Commande stop envoyée.\n";

    echo "--- Test terminé avec succès ---\n";

} catch (Exception $e) {
    echo "!!! ERREUR : " . $e->getMessage() . "\n";
    echo "Fichier : " . $e->getFile() . " à la ligne " . $e->getLine() . "\n";
}
