<?php

namespace Nexus\Utils;

use log;
use Throwable;

/**
 * Classe Helpers - Nexus Framework
 * Centralisation de la gestion des erreurs et du logging Jeedom
 */
class Helpers
{
    /**
     * Exécute une fonction anonyme de manière sécurisée avec niveau de log configurable.
     *
     * @param callable $callback Logique à exécuter
     * @param mixed $default Valeur de retour en cas d'exception
     * @param string $level Niveau de log Jeedom (error, warning, info, debug)
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

            // Centralisation dans le flux de log 'nexus'
            log::add('nexus', $level, $message);

            return $default;
        }
    }
}
