<?php

namespace Nexus\Utils;

/**
 * Gestion de la configuration - Nexus Framework
 */
class Config
{
    /** @var array Stockage des contenus indexés par chemin de fichier */
    private static array $cache = [];

    /**
     * Chemin par défaut vers le fichier JSON de configuration
     */
    private static function getDefaultPath(): string
    {
        return NEXUS_ROOT . '/core/config/jeedom.config.json';
    }

    /**
     * Récupère un paramètre de configuration
     * * @param string $key Clé JSON
     * @param mixed $default Valeur par défaut
     * @param string|null $filename Chemin optionnel vers un fichier config spécifique
     * @return mixed
     */
    public static function get(string $key, $default = null, ?string $filename = null)
    {
        $path = $filename ?? self::getDefaultPath();

        // Si le fichier n'est pas encore en cache, on le charge
        if (!isset(self::$cache[$path])) {

            if (!is_readable($path)) {
                Helpers::log("Fichier de configuration absent ou illisible : $path", 'error');
                return $default;
            }

            $data = json_decode(file_get_contents($path), true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                Helpers::log("Erreur de parsing JSON ($path) : " . json_last_error_msg(), 'error');
                self::$cache[$path] = [];
                return $default;
            }

            self::$cache[$path] = $data;
        }

        return self::$cache[$path][$key] ?? $default;
    }
}
