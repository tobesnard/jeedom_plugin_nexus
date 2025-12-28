<?php

namespace Nexus\Utils;

use Throwable;
use Nexus\Jeedom\Services\JeedomLogService;

/**
 * Classe Helpers - Nexus Framework
 * Gestion durcie du logging et de l'exécution sécurisée.
 */
class Helpers
{
    /**
     * Log sécurisé (protection contre le Log Injection)
     *
     * @param string $message
     * @param string $level
     */
    public static function log(string $message, string $level = 'info'): void
    {
        // Nettoyage des caractères de contrôle pour éviter la falsification de logs (CWE-117)
        $sanitizedMessage = str_replace(["\r", "\n"], ['\r', '\n'], $message);

        JeedomLogService::getInstance()->log($sanitizedMessage, $level);
    }

    /**
     * Ajoute un message dans le centre de messages de Jeedom
     * Utile pour les alertes critiques nécessitant une action utilisateur.
     * * @param string $type Le type de message (ex: Alarme, Sécurité)
     * @param string $message Le contenu du message
     */
    public static function message(string $type, string $message): void
    {
        $sanitizedMessage = str_replace(["\r", "\n"], ['\r', '\n'], $message);
        JeedomLogService::getInstance()->addMessage($type, $sanitizedMessage);
    }

    /**
     * Exécute une fonction anonyme de manière sécurisée avec traçabilité complète. Utilisation principalement dans les *.inc.php
     *
     * @param callable $callback
     * @param mixed $default
     * @param string $level
     * @return mixed
     */
    public static function execute(callable $callback, $default = null, string $level = 'error')
    {
        try {
            return $callback();
        } catch (Throwable $e) {
            // Construction d'un rapport d'erreur complet pour expert
            $report = sprintf(
                "Exception: %s | Message: %s | File: %s:%d",
                get_class($e),
                $e->getMessage(),
                $e->getFile(),
                $e->getLine(),
            );

            // Ajout de la stack trace pour analyse profonde
            if ($level === 'debug') {
                $report .= " | Trace: " . $e->getTraceAsString();
            }

            self::log($report . $context, $level);

            return $default;
        }
    }
}
