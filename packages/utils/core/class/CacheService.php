<?php

namespace Nexus\Utils;

/**
 * Service de Cache Mémoire - Singleton
 * Centralise le stockage temporaire pour l'ensemble du framework Nexus.
 */
class CacheService
{
    /** @var self|null */
    private static $instance = null;

    /** @var array Stockage interne */
    private array $cache = [];

    /** @var int TTL par défaut */
    private int $defaultTimeout = 300;

    /**
     * Constructeur privé
     */
    private function __construct() {}

    /**
     * Empêche le clonage
     */
    private function __clone() {}

    /**
     * Récupère l'instance unique du cache
     * * @return self
     */
    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Définit le timeout par défaut (optionnel)
     * * @param int $timeout
     */
    public function setDefaultTimeout(int $timeout): void
    {
        $this->defaultTimeout = $timeout;
    }

    /**
     * Récupère une valeur
     * * @param string $key
     * @return mixed|null
     */
    public function get(string $key)
    {
        if ($this->isValid($key)) {
            return $this->cache[$key]['value'];
        }
        return null;
    }

    /**
     * Stocke une valeur
     * * @param string $key
     * @param mixed $value
     * @param int|null $timeout
     */
    public function set(string $key, $value, ?int $timeout = null): void
    {
        $this->cache[$key] = [
            'value'     => $value,
            'timestamp' => time(),
            'timeout'   => $timeout ?? $this->defaultTimeout,
        ];
    }

    /**
     * Vérifie la validité
     */
    public function isValid(string $key): bool
    {
        return isset($this->cache[$key])
               && (time() - $this->cache[$key]['timestamp'] < $this->cache[$key]['timeout']);
    }

    /**
     * Génère une clé unique
     */
    public function generateKey(...$parts): string
    {
        return md5(json_encode($parts));
    }

    /**
     * Vide l'intégralité du cache mémoire
     */
    public function clear(): void
    {
        $this->cache = [];
    }
}
