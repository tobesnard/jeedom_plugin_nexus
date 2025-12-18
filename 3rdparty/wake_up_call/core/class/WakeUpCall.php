<?php

namespace Nexus\Multimedia\WakeUpCall;

use Exception;
use Chromecast;

/**
 * Classe WakeUpCall pour contrôler les appareils Chromecast pour les appels de réveil.
 *
 * Cette classe étend la classe Chromecast pour fournir des fonctionnalités spécifiques
 * à la lecture de médias pour les réveils, avec gestion de la configuration et de la persistance.
 */
class WakeUpCall extends Chromecast
{
    /** @var string Chemin vers le fichier de configuration JSON */
    private static string $configFile = __DIR__ . '/../config/config.json';

    /** @var array Configuration chargée depuis le fichier JSON */
    private array $config;

    /** @var string Adresse IP de l'appareil Chromecast */
    public string $ip;

    /**
     * Définit le chemin du fichier de configuration (pour les tests).
     *
     * @param string $filePath Chemin vers le fichier JSON
     */
    public static function setConfigFile(string $filePath): void
    {
        self::$configFile = $filePath;
    }

    /**
     * Retourne la configuration chargée (pour les tests).
     *
     * @return array
     */
    public function getConfig(): array
    {
        return $this->config;
    }

    /**
     * Constructeur de la classe WakeUpCall.
     *
     * Initialise la connexion à un appareil Chromecast en résolvant l'alias ou en utilisant l'IP directe.
     *
     * @param string $deviceAliasOrIp Alias de l'appareil défini dans la config ou adresse IP directe
     * @throws Exception Si le fichier de configuration est manquant ou invalide
     */
    public function __construct(string $deviceAliasOrIp)
    {
        $this->loadConfig();

        // Résolution IP : Alias JSON ou IP directe
        $this->ip = $this->config['devices'][$deviceAliasOrIp] ?? $deviceAliasOrIp;

        parent::__construct($this->ip, $this->config['port'] ?? '8009');
    }

    /**
     * Charge la configuration depuis le fichier JSON.
     *
     * @throws Exception Si le fichier n'existe pas ou si le JSON est invalide
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
     * Factory method pour charger une instance persistée ou créer une nouvelle.
     *
     * Gère la persistance des instances par IP pour éviter les reconnexions inutiles.
     *
     * @param string $device Alias ou IP de l'appareil
     * @param bool $refresh Force la création d'une nouvelle instance
     * @return self Instance de WakeUpCall
     * @throws Exception Si la configuration est invalide
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
     * Définit un chemin unique par device pour éviter les collisions de stockage.
     *
     * @return string Chemin vers le fichier de stockage
     */
    private function getStoragePath(): string
    {
        $base = $this->config['db_path'] ?? '/tmp/nexus/wake_up_call';
        return $base . '_' . md5($this->ip) . '.db';
    }

    /**
     * Sauvegarde l'instance dans un fichier pour la persistance.
     *
     * @return self L'instance elle-même pour le chaînage
     */
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
     * Joue un média sur l'appareil Chromecast.
     *
     * Utilise la configuration pour récupérer l'URL et les paramètres du média.
     *
     * @param string $key Clé du média dans la configuration
     * @param string|null $overrideUrl URL alternative à utiliser au lieu de celle de la config
     * @throws Exception Si aucune URL n'est trouvée pour la clé
     */
    public function playMedia(string $key, ?string $overrideUrl = null): void
    {
        $media = $this->config['media'][$key] ?? null;
        $url = $overrideUrl ?? ($media['url'] ?? null);

        if (!$url) {
            throw new Exception("No URL found for media key: $key");
        }

        $type = $media['type'] ?? 'BUFFERED';
        $mime = $media['mime'] ?? 'audio/mpeg'; // audio/mpeg est plus standard que audio/mp3

        // Lancement de l'application de lecture
        $this->DMP->play($url, $type, $mime, true, 0);
        $this->save();
    }

    /**
     * Arrête la lecture en cours sur l'appareil Chromecast.
     */
    public function stop(): void
    {
        $this->DMP->stop();
        $this->save();
    }
}
