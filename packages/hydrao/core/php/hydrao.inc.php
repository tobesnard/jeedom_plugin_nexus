<?php

use Nexus\Hydrao\HydraoAPI;
use Nexus\Utils\Config;

/**
 * Récupère la liste des compteurs via l'API officielle Hydrao.
 * Utilise le fichier de configuration JSON local.
 * * @return array|null Liste des compteurs (objets) ou null en cas d'échec
 */
function hydrao_getMeters(): ?array
{
    try {
        // Définition du chemin relatif vers le fichier de config
        $configPath = __DIR__ . "/../config/config.json";

        // Récupération des paramètres via le moteur de configuration Nexus
        // Le 3ème paramètre permet de cibler le fichier spécifique
        $apiKey   = Config::get('api_key', null, $configPath);
        $email    = Config::get('email', null, $configPath);
        $password = Config::get('password', null, $configPath);

        // Validation de la présence des credentials
        if (!$apiKey || !$email || !$password) {
            throw new \Exception("Configuration Hydrao incomplète ou fichier introuvable dans : $configPath");
        }

        // Initialisation de l'API avec la clé
        $api = new HydraoAPI($apiKey);

        /**
         * Tentative d'authentification.
         * Note : La classe HydraoAPI gère elle-même le cache du token dans /tmp
         * pour éviter de consommer inutilement le quota de 500 requêtes/heure.
         */
        if (!$api->login($email, $password)) {
            throw new \Exception("Authentification Hydrao échouée (Vérifiez email/password).");
        }

        // Appel de l'endpoint /meters (Doc Section 5.1)
        $meters = $api->getMeters();

        // Retourne le résultat s'il s'agit d'un tableau, sinon null
        return is_array($meters) ? $meters : null;

    } catch (\Exception $e) {
        // Log de l'erreur pour le debug système
        error_log("[Hydrao] Erreur dans hydrao_getMeters : " . $e->getMessage());
        return null;
    }
}

/**
 * Exemple d'implémentation pour le monitoring
 */
/*
$data = hydrao_getMeters();
if ($data) {
    foreach ($data as $meter) {
        echo "ID: " . $meter->id . " | Label: " . $meter->label . "\n";
    }
}
*/
