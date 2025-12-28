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
     * Wrapper pour log::add centralisant le flux sur le log 'nexus'
     *
     * @param string $message Le message à loguer
     * @param string $level Niveau de log (error, warning, info, debug)
     */
    public static function log(string $message, string $level = 'info'): void
    {
        if (class_exists('\log')) {
            \log::add('nexus', $level, $message);
        }
    }

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

            // Utilisation du nouveau wrapper interne
            self::log($message, $level);

            return $default;
        }
    }
}
