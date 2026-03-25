<?php

namespace Nexus\Utils;

/**
 * Gestion de la configuration - Nexus Framework
 */
class Config
{
    /** @var array|null */
    private static $cache = null;

    /**
     * Chemin vers le fichier JSON de configuration
     */
    private static function getPath(): string
    {
        return NEXUS_ROOT . '/core/config/jeedom.config.json';
    }

    /**
     * Récupère un paramètre de configuration
     * * @param string $key Clé JSON
     * @param mixed $default Valeur par défaut
     * @return mixed
     */
    public static function get(string $key, $default = null)
    {
        if (self::$cache === null) {
            $path = self::getPath();

            if (!is_readable($path)) {
                Helpers::log("Fichier de configuration absent ou illisible : $path", 'error');
                return $default;
            }

            $data = json_decode(file_get_contents($path), true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                Helpers::log("Erreur de parsing JSON ($path) : " . json_last_error_msg(), 'error');
                self::$cache = [];
                return $default;
            }

            self::$cache = $data;
        }

        return self::$cache[$key] ?? $default;
    }
}
