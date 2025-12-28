<?php

namespace Nexus\Utils;

use Throwable;
use Nexus\Jeedom\Services\JeedomCmdService;

/**
 * Classe Helpers - Nexus Framework
 * Centralisation de la gestion des erreurs et du logging via le service Jeedom
 */
class Helpers
{
    /**
     * Wrapper utilisant le Singleton JeedomCmdService
     *
     * @param string $message Le message à loguer
     * @param string $level Niveau de log (error, warning, info, debug)
     */
    public static function log(string $message, string $level = 'info'): void
    {
        // Utilisation de l'instance unique du service pour logger
        JeedomCmdService::getInstance()->log($message, $level);
    }

    /**
     * Exécute une fonction anonyme de manière sécurisée
     *
     * @param callable $callback Logique à exécuter
     * @param mixed $default Valeur de retour en cas d'exception
     * @param string $level Niveau de log en cas d'erreur
     * @return mixed
     */
    public static function execute(callable $callback, $default = null, string $level = 'error')
    {
        try {
            return $callback();
        } catch (Throwable $e) {
            $message = sprintf(
                "[%s]: %s | File: %s:%d",
                get_class($e),
                $e->getMessage(),
                $e->getFile(),
                $e->getLine(),
            );

            self::log($message, $level);

            return $default;
        }
    }
}
