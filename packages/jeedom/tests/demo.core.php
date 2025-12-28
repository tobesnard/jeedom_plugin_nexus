<?php

namespace Nexus\Jeedom;

// Simulation simplifiée du Core Jeedom pour le test
if (!class_exists('cmd')) {
    class cmd
    {
        public static function byString($string)
        {
            echo "[Test] Recherche de la commande : $string\n";
            return new self();
        }
        public function execCmd($options)
        {
            echo "[Test] Message envoyé : " . $options['message'] . "\n";
            return true;
        }
    }
}

require_once __DIR__ . '/../core/class/Notification.php'; // Remplace par le chemin réel

try {
    echo "--- Début du test ---\n";

    $message = "Alerte de test système - " . date('H:i:s');
    $status = Notification::emergencyThreadNotification($message);

    if ($status) {
        echo "RÉSULTAT : Succès du transfert.\n";
    } else {
        echo "RÉSULTAT : Échec (vérifiez la clé dans le JSON).\n";
    }

} catch (\Throwable $e) {
    echo "ERREUR CAPTURÉE : " . $e->getMessage() . "\n";
}
