<?php

namespace Nexus\Jeedom;

require_once __DIR__ . "/../../../../vendor/autoload.php";

use Nexus\Utils\Helpers;
use Nexus\Utils\Config;
use Nexus\Jeedom\Services\JeedomCmdService;

/**
 * Service de Notification - Nexus Framework
 * * S'appuie sur JeedomCmdService pour l'exécution et Config pour les paramètres.
 */
class Notification
{
    /**
     * Envoie une notification sur le canal d'urgence (Thread)
     * * @param string $message Le contenu de la notification
     * @return bool True en cas de succès, false sinon
     */
    public static function emergencyThreadNotification(string $message): bool
    {
        if (empty($message)) {
            return false;
        }

        // Récupération du chemin de la commande via le service Config
        $cmdString = Config::get('emergency_thread_notification');

        if (!$cmdString) {
            Helpers::log("Configuration manquante : 'emergency_thread_notification'", 'warning');
            return false;
        }

        // Exécution via le JeedomCmdService (gestion automatique des erreurs et logs)
        $result = JeedomCmdService::getInstance()->execByString($cmdString, [
            'message' => $message,
        ]);

        // Le service retourne null en cas d'échec capturé
        return ($result !== null);
    }
}
