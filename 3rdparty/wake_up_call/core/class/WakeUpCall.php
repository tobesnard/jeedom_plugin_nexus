<?php

namespace Nexus\Multimedia\WakeUpCall;

use Exception;

require_once __DIR__ . '/../../3rdparty/cast/Chromecast.php';

class WakeUpCall extends \Chromecast
{
    private static string $configFile = __DIR__ . '/../config/config.json';
    private array $config;
    public string $ip;

    public function __construct(string $deviceAliasOrIp)
    {
        $this->loadConfig();

        // Résolution IP : Alias JSON ou IP directe
        $this->ip = $this->config['devices'][$deviceAliasOrIp] ?? $deviceAliasOrIp;

        parent::__construct($this->ip, $this->config['port'] ?? '8009');
    }

    /**
     * Charge la configuration JSON
     */
    private function loadConfig(): void
    {
        if (!file_exists(self::$configFile)) {
            throw new Exception("Configuration file missing: " . self::$configFile);
        }
        $content = file_get_contents(self::$configFile);
        $this->config = json_decode($content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception("Invalid JSON in config file.");
        }
    }

    /**
     * Factory : gère la persistance par IP
     */
    public static function load(string $device, bool $refresh = false): self
    {
        $dummy = new self($device);
        $storagePath = $dummy->getStoragePath();

        if (!file_exists($storagePath) || $refresh) {
            $dummy->save();
            return $dummy;
        }

        $instance = unserialize(file_get_contents($storagePath));

        // Note: La ressource socket de la classe parente doit être réinitialisée ici
        // si la librairie Chromecast.php ne gère pas la reconnexion automatique.
        return $instance;
    }

    /**
     * Définit un chemin unique par device pour éviter les collisions
     */
    private function getStoragePath(): string
    {
        $base = $this->config['db_path'] ?? '/tmp/nexus/wake_up_call';
        return $base . '_' . md5($this->ip) . '.db';
    }

    public function save(): self
    {
        $path = $this->getStoragePath();
        $dir = dirname($path);

        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }

        file_put_contents($path, serialize($this));
        chmod($path, 0660);
        return $this;
    }

    /**
     * Moteur de lecture générique
     */
    public function playMedia(string $key, ?string $overrideUrl = null): void
    {
        $media = $this->config['media'][$key] ?? null;
        $url = $overrideUrl ?? ($media['url'] ?? null);

        if (!$url) {
            throw new Exception("No URL found for media key: $key");
        }

        // DEBUG : Afficher l'URL testée dans la console
        echo "DEBUG: Tentative de lecture de l'URL : " . $url . "\n";

        // Forcer BUFFERED pour l'audio et spécifier le MIME exact
        $type = $media['type'] ?? 'BUFFERED';
        $mime = $media['mime'] ?? 'audio/mpeg'; // audio/mpeg est plus standard que audio/mp3

        // Lancement de l'application de lecture
        $this->DMP->play($url, $type, $mime, true, 0);
        $this->save();
    }

    public function stop(): void
    {
        $this->DMP->stop();
        $this->save();
    }
}
