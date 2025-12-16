<?php

namespace Nexus\Multimedia\PhilipsTV;

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

    private static ?\DateTime $datetime = null;
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

    // --- Propriétés pour la requête (Recommandation : utiliser Guzzle ou une librairie cURL wrapper) ---
    private string $baseUrl;

    /**
     * Constructeur privé pour le patron Singleton.
     */
    private function __construct()
    {
        // Chargement la configuration
        $this->loadConfig();

        static::$datetime = new \DateTime();

        $this->baseUrl = "https://" . self::$philipsTV_ip . ":" . self::$philipsTV_port;

        // Chargement du fichier JSON des actions
        if (is_null($this->actionsJSON)) {
            $this->loadActionsJSON();
        }

        // Chargement de la structure des paramètres (à ne faire qu'une seule fois)
        if (is_null($this->structure)) {
            $this->loadSettingsStructure();
        }
    }

    /**
     * Empêche le clonage de l'instance Singleton.
     */
    private function __clone()
    {
    }

    // --- Méthodes de service (Privées) ---


    /**
    * Charge la configuration JSON et récupère la MAC
    */
    private function loadConfig(): void
    {
        if (!file_exists(self::CONFIG_FILEPATH)) {
            throw new Exception("Fichier de configuration introuvable : " . self::CONFIG_FILEPATH);
        }

        $jsonContent = file_get_contents(self::CONFIG_FILEPATH);
        $data = json_decode($jsonContent, true); // 'true' pour obtenir un tableau associatif

        if (isset($data['version'])) {
            self::$version = $data['version'];
        } else {
            throw new Exception("Le numéro de version est manquant dans le fichier JSON.");
        }

        if (isset($data['ip'])) {
            self::$philipsTV_ip = $data['ip'];
        } else {
            throw new Exception("L'adresse IP est manquante dans le fichier JSON.");
        }

        if (isset($data['port'])) {
            self::$philipsTV_port = $data['port'];
        } else {
            throw new Exception("Le port est manquant dans le fichier JSON.");
        }

        if (isset($data['mac'])) {
            self::$philipsTV_mac = $data['mac'];
        } else {
            throw new Exception("L'adresse MAC est manquante dans le fichier JSON.");
        }

        if (isset($data['username'])) {
            self::$username = $data['username'];
        } else {
            throw new Exception("Le nom d'utilisateur est manquant dans le fichier JSON.");
        }
        if (isset($data['password'])) {
            self::$password = $data['password'];
        } else {
            throw new Exception("Le mot de passe est manquant dans le fichier JSON.");
        }
    }



    /**
     * Charge le contenu du fichier JSON des actions.
     * @return void
     */
    private function loadActionsJSON(): void
    {
        if (!file_exists(self::ACTIONS_FILEPATH)) {
            throw new \Exception("Le fichier d'actions JSON est introuvable : " . self::ACTIONS_FILEPATH);
        }
        $string = file_get_contents(self::ACTIONS_FILEPATH);
        $this->actionsJSON = json_decode($string, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception("Erreur lors du décodage du fichier JSON d'actions : " . json_last_error_msg());
        }
    }

    /**
     * Charge la structure complète des paramètres du téléviseur.
     * @return void
     */
    private function loadSettingsStructure(): void
    {
        $response = $this->request_https("menuitems/settings/structure", "GET");
        $this->structure = json_decode($response);

        if (json_last_error() !== JSON_ERROR_NONE) {
            // Loguer ou ignorer si la structure n'est pas critique au démarrage
            error_log("Avertissement : Impossible de charger la structure des paramètres de la TV. " . json_last_error_msg());
            $this->structure = (object)['node' => null]; // Initialisation sécurisée
        } else {
            $this->structure = $this->structure->node ?? (object)['node' => null];
        }
    }

    /**
     * Exécute une requête HTTPS vers l'API JointSpace en utilisant cURL (alternative à shell_exec).
     *
     * @param string $_uri La partie de l'URI après le port (ex: 'system/powerstate').
     * @param string $_method La méthode HTTP ('GET' ou 'POST').
     * @param array|null $_data Le corps de la requête pour 'POST' (sera encodé en JSON).
     * @return string La réponse brute de l'API.
     * @throws \Exception En cas d'échec de la requête ou de cURL.
     */
    private function request_https(string $_uri, string $_method, ?array $_data = null): string
    {
        $url = "{$this->baseUrl}/{$_uri}";
        $ch = curl_init($url);

        // Options cURL
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // Retourne le transfert comme une chaîne de caractères
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $_method);
        curl_setopt($ch, CURLOPT_USERPWD, "" . self::$username . ":" . self::$password);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_DIGEST); // Authentification Digest

        // Sécurité/Connexion
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // JointSpace utilise un certificat auto-signé, à éviter en prod !
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);

        // Données pour POST
        if ($_method === 'POST' && !is_null($_data)) {
            $json_data = json_encode($_data);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $json_data);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        }

        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if (curl_errno($ch)) {
            $error = curl_error($ch);
            curl_close($ch);
            throw new \Exception("Erreur cURL ({$_uri}): " . $error);
        }

        curl_close($ch);

        // Gestion des codes de réponse HTTP (ex: 401 Unauthorized, 400 Bad Request)
        if ($http_code < 200 || $http_code >= 300) {
            throw new \Exception("Erreur HTTP ({$_uri}) : Code {$http_code} - Réponse: {$response}");
        }

        return $response;
    }

    /**
     * Méthode de recherche récursive du 'node_id' basé sur le 'context'.
     *
     * @param string $_key Le 'context' à rechercher (ex: 'headphones_volume').
     * @param object|null $_json La structure (ou un sous-nœud).
     * @return int|null Le 'node_id' trouvé ou null.
     */
    private function search_nodeId(string $_key, ?object $_json): ?int
    {
        if (is_null($_json)) {
            return null;
        }

        if (isset($_json->context) && $_json->context === $_key && isset($_json->node_id)) {
            return (int)$_json->node_id;
        }

        // Vérification de l'existence des sous-nœuds et si c'est un tableau d'objets
        if (isset($_json->data->nodes) && is_array($_json->data->nodes)) {
            foreach ($_json->data->nodes as $node) {
                $result = $this->search_nodeId($_key, $node);
                if ($result !== null) {
                    return $result;
                }
            }
        }

        return null; // Retourne null si non trouvé
    }


    // Le search_nodeStringId est redondant/non utilisé dans l'implémentation actuelle et peut être supprimé
    // ou rendu plus générique si besoin. Je le supprime ici pour alléger.


    // --- Méthodes Publiques (API) ---

    /**
     * Retourne l'instance unique de la classe TV (Singleton).
     * Le Singleton est régénéré quotidiennement pour recharger potentiellement la structure
     * des paramètres du téléviseur si elle a changé, mais ce n'est généralement pas nécessaire.
     *
     * @return self L'instance unique de la classe.
     */
    public static function getInstance(): self
    {
        $now = new \DateTime();
        $nowFormatted = $now->format('Ymd');

        // Logique de rafraîchissement quotidien de l'instance (optionnel)
        if (is_null(self::$_instance) || is_null(self::$datetime) || self::$datetime->format('Ymd') !== $nowFormatted) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    /**
     * Retourne la version de l'API wrapper.
     * @return string
     */
    public function version(): string
    {
        return self::$version;
    }

    /**
     * Exécute une action prédéfinie à partir du fichier JSON.
     * Utilise le fichier de configuration pour accélérer le processus.
     *
     * @param string $_action Le nom de l'action à exécuter (ex: 'ambilight_mode').
     * @param mixed $_value La valeur optionnelle pour remplacer le placeholder dans le body JSON.
     * @return string|null La réponse JSON de l'API ou null en cas d'échec.
     * @throws \Exception Si l'action n'est pas trouvée ou si la requête échoue.
     */
    public function action(string $_action, $_value = null): ?string
    {
        if (!is_array($this->actionsJSON)) {
            throw new \Exception("Le fichier d'actions n'a pas été chargé correctement.");
        }

        // --- Action GET ---
        if (isset($this->actionsJSON['get'][$_action])) {
            $uri = $this->actionsJSON['get'][$_action]['path'];
            return $this->request_https($uri, "GET");
        }

        // --- Action POST ---
        if (isset($this->actionsJSON['post'][$_action])) {
            $uri = $this->actionsJSON['post'][$_action]['path'];
            $body = $this->actionsJSON['post'][$_action]['body']; // Corps de base (array)

            if (!is_null($_value)) {
                // Recherche récursive du placeholder '?' et remplacement
                $body = $this->replacePlaceholder($body, $_value);
            }

            return $this->request_https($uri, "POST", $body);
        }

        throw new \Exception("Action '{$_action}' non trouvée dans le fichier d'actions.");
    }

    /**
     * Modifie le contenu d'une chaîne ou d'un tableau/objet en remplaçant la valeur
     * spéciale "?" par la valeur fournie.
     *
     * @param mixed $data Le tableau/objet/chaîne où effectuer le remplacement.
     * @param mixed $value La valeur de remplacement.
     * @return mixed Le tableau/objet/chaîne modifié.
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
     *
     * @param string $_channel_name Le nom de la chaîne (ex: "BFM TV").
     * @return string La réponse JSON de l'API.
     * @throws \Exception Si la chaîne n'est pas trouvée ou si la requête échoue.
     */
    public function setChannel(string $_channel_name = "BFM TV"): string
    {
        $listeChannelsJson = $this->action('list_channels');
        $listeChannels = json_decode($listeChannelsJson);

        if (json_last_error() !== JSON_ERROR_NONE || !isset($listeChannels->Channel)) {
            throw new \Exception("Impossible de décoder la liste des chaînes ou format incorrect.");
        }

        $ccid = null;
        foreach ($listeChannels->Channel as $ch) {
            if (isset($ch->name) && $ch->name === $_channel_name) {
                $ccid = $ch->ccid;
                break;
            }
        }

        if (is_null($ccid)) {
            throw new \Exception("Chaîne '{$_channel_name}' non trouvée.");
        }

        // Construction du corps de la requête comme un tableau (plus propre qu'une chaîne JSON)
        $channel_data = [
            "channel" => ["ccid" => $ccid],
            "channelList" => ["id" => "allter"] // Généralement 'allter' pour toutes les chaînes TNT
        ];

        return $this->request_https("activities/tv", "POST", $channel_data);
    }

    /**
     * Modifie un paramètre via le mécanisme menuitems/settings/update.
     *
     * @param string $_name Le 'context' du paramètre à modifier (ex: 'headphones_volume').
     * @param string $_data La valeur à définir, encodée en JSON (ex: '{"value":20}').
     * @return string La réponse JSON de l'API.
     * @throws \Exception Si le nodeId n'est pas trouvé ou si la requête échoue.
     */
    public function setSettings(string $_name, string $_data): string
    {
        // 1. Recherche du nodeid
        if (!isset($this->structure)) {
            throw new \Exception("Structure des paramètres non chargée.");
        }

        $nodeId = $this->search_nodeId($_name, $this->structure);

        if (is_null($nodeId)) {
            throw new \Exception("NodeId pour le paramètre '{$_name}' non trouvé.");
        }

        // 2. Récupère la structure du paramètre courant pour la modification
        $bodyCurrent = ['nodes' => [['nodeid' => $nodeId]]];
        $settingJson = $this->request_https("menuitems/settings/current", "POST", $bodyCurrent);
        $setting = json_decode($settingJson);

        if (json_last_error() !== JSON_ERROR_NONE || !isset($setting->values[0])) {
            throw new \Exception("Erreur lors de la récupération du paramètre courant ou format invalide.");
        }

        // 3. Modification du paramètre
        $dataToSet = json_decode($_data, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception("Le JSON de données fourni est invalide: '{$_data}'");
        }

        // La structure de l'objet 'value' peut varier. On suppose que la valeur est dans 'data'.
        // NOTE: Cela pourrait nécessiter une logique plus complexe selon le type de paramètre.
        $setting->values[0]->value->data = $dataToSet;

        // 4. Envoie le paramétrage mis à jour
        return $this->request_https("menuitems/settings/update", "POST", (array)$setting);
    }

    // --- Méthode de Débogage ---

    /**
     * Affiche des informations de débogage.
     */
    public function debug(): void
    {
        echo "\n*** Debug ***\n";
        echo "Version: {$this->version}\n";
        echo "IP TV: {self::$philipsTV_ip}\n";
        echo "Actions JSON chargées: " . (is_array($this->actionsJSON) ? count($this->actionsJSON['get'] ?? []) + count($this->actionsJSON['post'] ?? []) : 'Non') . " actions\n";
        echo "Structure des paramètres chargée: " . (is_object($this->structure) ? 'Oui' : 'Non') . "\n";
    }
}
