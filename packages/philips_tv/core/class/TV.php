<?php

namespace Nexus\Multimedia\PhilipsTV;

use Exception;
use DateTime;

/**
 * Classe API PhilipsTV 📺
 *
 * Basée sur l'API JointSpace (Joint Space) pour les téléviseurs Philips.
 * L'accès à l'API JointSpace via HTTPS nécessite une authentification Digest.
 *
 * @link https://github.com/eslavnov/pylips/blob/master/docs/Home.md Documentation de référence PyLips
 * @link https://jointspace.sourceforge.net/projectdata/documentation/jasonApi/1/doc/API.html Documentation de référence JointSpace
 *
 * Pour obtenir les identifiants d'utilisateur (username/password), il est recommandé
 * d'utiliser un outil comme `pylips.py` ou de suivre la procédure d'appariement du téléviseur.
 *
 * Utilisation :
 * $tv = TV::getInstance();
 * $tv->action('power_on');
 * $tv->setSettings('headphones_volume', '{"value":20}');
 */
class TV
{
    // --- Propriétés de la Classe (Singleton et Configuration) ---

    private static ?DateTime $datetime = null;
    private static ?self $_instance = null;

    private const ACTIONS_FILEPATH = __DIR__ . '/../config/philipstv_actions.json';
    private const CONFIG_FILEPATH = __DIR__ . '/../config/philipstv_config.json';

    private static string $version;
    private static string $philipsTV_ip;
    private static string $philipsTV_port;
    private static string $philipsTV_mac;
    private static string $username;
    private static string $password;

    private ?object $structure = null;
    private ?array $actionsJSON = null;

    // --- Propriétés pour la requête ---
    private string $baseUrl;

    /**
     * Constructeur privé pour le patron Singleton.
     */
    private function __construct()
    {
        // Chargement la configuration
        $this->loadConfig();

        static::$datetime = new DateTime();

        $this->baseUrl = "https://" . self::$philipsTV_ip . ":" . self::$philipsTV_port;

        // Chargement du fichier JSON des actions
        if (is_null($this->actionsJSON)) {
            $this->loadActionsJSON();
        }

        /* * OPTIMISATION EXPERT :
         * Le chargement de la structure est retiré du constructeur (Lazy Loading).
         * Cela évite les Fatal Errors de Timeout si la TV est éteinte lors de l'instanciation.
         */
    }

    /**
     * Empêche le clonage de l'instance Singleton.
     */
    private function __clone() {}

    // --- Méthodes de service (Privées) ---

    /**
     * Assure que la TV est non seulement allumée (WoL) mais que l'API répond réellement.
     * Utilise un polling sur une route système légère.
     */
    private function ensureTvIsResponsive(?string $macAddress = null): void
    {
        $targetMac = $macAddress ?? self::$philipsTV_mac;
        $safeMac = escapeshellarg($targetMac);

        $startTime = microtime(true);
        $maxWait = 10.0; // Augmenté à 10s car le boot complet de l'API est lent

        while ((microtime(true) - $startTime) < $maxWait) {
            // 1. Envoi du Magic Packet
            exec("wakeonlan $safeMac >/dev/null 2>&1");

            // 2. Polling applicatif : On tente une requête légère sans exception
            if ($this->pingApi()) {
                return;
            }

            usleep(250000); // Pause 250ms entre chaque tentative
        }
    }

    /**
     * Teste si l'API est capable de répondre à une requête simple.
     */
    private function pingApi(): bool
    {
        $ch = curl_init("{$this->baseUrl}/system");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERPWD, self::$username . ":" . self::$password);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_DIGEST);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 2);

        curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return ($http_code >= 200 && $http_code < 300);
    }

    /**
     * Charge la configuration JSON et récupère la MAC
     */
    private function loadConfig(): void
    {
        if (!file_exists(self::CONFIG_FILEPATH)) {
            throw new Exception("Fichier de configuration introuvable : " . self::CONFIG_FILEPATH);
        }

        $jsonContent = file_get_contents(self::CONFIG_FILEPATH);
        $data = json_decode($jsonContent, true);

        if (!isset($data['version'], $data['ip'], $data['port'], $data['mac'], $data['username'], $data['password'])) {
            throw new Exception("Configuration incomplète dans le fichier JSON.");
        }

        self::$version = $data['version'];
        self::$philipsTV_ip = $data['ip'];
        self::$philipsTV_port = $data['port'];
        self::$philipsTV_mac = $data['mac'];
        self::$username = $data['username'];
        self::$password = $data['password'];
    }

    /**
     * Charge le contenu du fichier JSON des actions.
     */
    private function loadActionsJSON(): void
    {
        if (!file_exists(self::ACTIONS_FILEPATH)) {
            throw new Exception("Le fichier d'actions JSON est introuvable : " . self::ACTIONS_FILEPATH);
        }
        $string = file_get_contents(self::ACTIONS_FILEPATH);
        $this->actionsJSON = json_decode($string, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception("Erreur décodage JSON actions : " . json_last_error_msg());
        }
    }

    /**
     * Charge la structure complète des paramètres du téléviseur (Lazy).
     */
    private function loadSettingsStructure(): void
    {
        try {
            $response = $this->request_https("menuitems/settings/structure", "GET");
            $decoded = json_decode($response);
            $this->structure = $decoded->node ?? (object) ['node' => null];
        } catch (Exception $e) {
            // Évite le blocage si l'API n'est pas encore prête
            error_log("Avertissement : Structure non disponible. " . $e->getMessage());
            $this->structure = (object) ['node' => null];
        }
    }

    /**
     * Exécute une requête HTTPS vers l'API JointSpace en utilisant cURL.
     */
    private function request_https(string $_uri, string $_method, ?array $_data = null): string
    {
        $url = "{$this->baseUrl}/{$_uri}";
        $attempts = 0;
        $maxAttempts = 3; // On tente 3 fois avant de lâcher

        while ($attempts < $maxAttempts) {
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $_method);
            curl_setopt($ch, CURLOPT_USERPWD, self::$username . ":" . self::$password);
            curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_DIGEST);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
            curl_setopt($ch, CURLOPT_TIMEOUT, 5);

            if ($_method === 'POST' && !is_null($_data)) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($_data));
                curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            }

            $response = curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curl_error = curl_errno($ch);
            curl_close($ch);

            if (!$curl_error && $http_code >= 200 && $http_code < 300) {
                return $response;
            }

            $attempts++;
            if ($attempts < $maxAttempts) {
                usleep(500000); // Attend 500ms avant de retenter
            }
        }

        throw new Exception("Erreur cURL ({$_uri}) après {$maxAttempts} tentatives.");
    }

    /**
     * Méthode de recherche récursive du 'node_id' basé sur le 'context'.
     */
    private function search_nodeId(string $_key, ?object $_json): ?int
    {
        if (is_null($_json)) {
            return null;
        }

        if (isset($_json->context) && $_json->context === $_key && isset($_json->node_id)) {
            return (int) $_json->node_id;
        }

        if (isset($_json->data->nodes) && is_array($_json->data->nodes)) {
            foreach ($_json->data->nodes as $node) {
                $result = $this->search_nodeId($_key, $node);
                if ($result !== null) {
                    return $result;
                }
            }
        }
        return null;
    }

    // --- Méthodes Publiques (API) ---

    /**
     * Retourne l'instance unique de la classe TV (Singleton).
     */
    public static function getInstance(): self
    {
        $now = new DateTime();
        $nowFormatted = $now->format('Ymd');

        if (is_null(self::$_instance) || is_null(self::$datetime) || self::$datetime->format('Ymd') !== $nowFormatted) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    /**
     * Retourne la version de l'API wrapper.
     */
    public function version(): string
    {
        return self::$version;
    }

    /**
     * Exécute une action prédéfinie.
     */
    public function action(string $_action, $_value = null): ?string
    {
        if (!is_array($this->actionsJSON)) {
            throw new Exception("Le fichier d'actions n'est pas chargé.");
        }

        self::ensureTvIsResponsive();

        // --- Action GET ---
        if (isset($this->actionsJSON['get'][$_action])) {
            $uri = $this->actionsJSON['get'][$_action]['path'];
            return $this->request_https($uri, "GET");
        }

        // --- Action POST ---
        if (isset($this->actionsJSON['post'][$_action])) {
            $uri = $this->actionsJSON['post'][$_action]['path'];
            $body = $this->actionsJSON['post'][$_action]['body'];

            if (!is_null($_value)) {
                $body = $this->replacePlaceholder($body, $_value);
            }

            return $this->request_https($uri, "POST", $body);
        }

        throw new Exception("Action '{$_action}' non trouvée.");
    }

    /**
     * Remplacement du placeholder "?" par la valeur fournie.
     */
    private function replacePlaceholder($data, $value)
    {
        if (is_array($data)) {
            foreach ($data as $key => $item) {
                $data[$key] = $this->replacePlaceholder($item, $value);
            }
            return $data;
        } elseif (is_string($data) && $data === '?') {
            return $value;
        }
        return $data;
    }

    /**
     * Change la chaîne de télévision par son nom.
     */
    public function setChannel(string $_channel_name = "BFM TV"): string
    {
        $listeChannelsJson = $this->action('list_channels');
        $listeChannels = json_decode($listeChannelsJson);

        if (json_last_error() !== JSON_ERROR_NONE || !isset($listeChannels->Channel)) {
            throw new Exception("Erreur décodage liste chaînes.");
        }

        $ccid = null;
        foreach ($listeChannels->Channel as $ch) {
            if (isset($ch->name) && $ch->name === $_channel_name) {
                $ccid = $ch->ccid;
                break;
            }
        }

        if (is_null($ccid)) {
            throw new Exception("Chaîne '{$_channel_name}' non trouvée.");
        }

        $channel_data = [
            "channel" => ["ccid" => $ccid],
            "channelList" => ["id" => "allter"],
        ];

        return $this->request_https("activities/tv", "POST", $channel_data);
    }

    /**
     * Modifie un paramètre via menuitems/settings/update.
     */
    public function setSettings(string $_name, string $_data): string
    {
        // Chargement à la demande de la structure
        if (is_null($this->structure)) {
            $this->loadSettingsStructure();
        }

        $nodeId = $this->search_nodeId($_name, $this->structure);
        if (is_null($nodeId)) {
            throw new Exception("NodeId pour '{$_name}' non trouvé.");
        }

        // Récupère l'état courant
        $bodyCurrent = ['nodes' => [['nodeid' => $nodeId]]];
        $settingJson = $this->request_https("menuitems/settings/current", "POST", $bodyCurrent);
        $setting = json_decode($settingJson);

        // Application de la nouvelle valeur
        $dataToSet = json_decode($_data, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception("JSON invalide: '{$_data}'");
        }

        $setting->values[0]->value->data = $dataToSet;

        return $this->request_https("menuitems/settings/update", "POST", (array) $setting);
    }

    /**
     * Affiche des informations de débogage.
     */
    public function debug(): void
    {
        echo "\n*** Debug ***\n";
        echo "Version: {$this->version()}\n";
        echo "IP TV: " . self::$philipsTV_ip . "\n";
        echo "Structure chargée: " . (is_object($this->structure) ? 'Oui' : 'Non') . "\n";
    }
}
