<?php

require __DIR__ . "/../../../vendor/autoload.php";

use Nexus\Hydrao\HydraoAPI;
use Nexus\Utils\Config;

// Couleurs ANSI
define('C_RESET', "\033[0m");
define('C_GREEN', "\033[32m");
define('C_CYAN', "\033[36m");
define('C_RED', "\033[31m");
define('C_YELLOW', "\033[33m");
define('C_BOLD', "\033[1m");

// Chemin du fichier de configuration
$configPath = __DIR__ . "/../core/config/config.json";

try {
    echo C_BOLD . C_CYAN . "[INIT]" . C_RESET . " Initialisation HydraoAPI (Guzzle Core)...\n";

    // Récupération des paramètres via Config::get
    $apiKey   = Config::get('api_key', null, $configPath);
    $email    = Config::get('email', null, $configPath);
    $password = Config::get('password', null, $configPath);
    $uuid     = Config::get('uuid', null, $configPath);

    if (!$apiKey || !$email || !$password) {
        throw new \Exception("Configuration incomplète dans $configPath");
    }

    $api = new HydraoAPI($apiKey);

    echo C_CYAN . "[AUTH]" . C_RESET . " Connexion à l'API : " . $email . "...\n";

    // Tentative de login (L'optimisation du cache est gérée dans la classe HydraoAPI)
    if (!$api->login($email, $password)) {
        throw new \Exception("Échec critique : Le serveur n'a pas renvoyé de jeton 'access_token'.");
    }

    echo C_BOLD . C_GREEN . "[SUCCESS]" . C_RESET . " Authentification réussie. Monitoring actif.\n";
    echo str_repeat("-", 60) . "\n";

    while (true) {
        $timestamp = date('H:i:s');
        echo C_YELLOW . "[$timestamp] --- Cycle de lecture ---\n" . C_RESET;

        try {
            // 1. Lecture Profil
            $me = $api->getUserMe();
            echo C_GREEN . "  [USER]   " . C_RESET . "ID: " . ($me->id ?? 'N/A') . " | Email: " . ($me->email ?? 'N/A') . "\n";

            // 2. Lecture Live (Événements) via l'UUID de la config
            if ($uuid) {
                $live = $api->getEvents($uuid);
                $isStreaming = isset($live->type) && $live->type === 'liveShower';
                echo C_GREEN . "  [LIVE]   " . C_RESET . "Flux en direct : " . ($isStreaming ? C_RED . "OUI" : "NON") . C_RESET . "\n";
            }

            // 3. Lecture Compteurs
            $meters = $api->getMeters();
            $count = is_array($meters) ? count($meters) : 0;
            echo C_GREEN . "  [METERS] " . C_RESET . "Compteurs actifs : " . C_BOLD . $count . C_RESET . "\n";

        } catch (\Exception $loopError) {
            echo C_RED . "  [ERROR]  Erreur API : " . $loopError->getMessage() . C_RESET . "\n";

            // Gestion de l'expiration du token ou Rate Limit
            if (strpos($loopError->getMessage(), '401') !== false || strpos($loopError->getMessage(), '429') !== false) {
                echo C_YELLOW . "  [RETRY]  Problème de session. Tentative de reconnexion au prochain cycle...\n" . C_RESET;
                $api->login($email, $password, true); // On force le renouvellement
            }
        }

        echo C_CYAN . "[WAIT]" . C_RESET . " Pause 30s...\n";
        sleep(30);
    }

} catch (\Exception $e) {
    fwrite(STDERR, "\n" . C_BOLD . C_RED . "[FATAL ERROR] " . C_RESET . $e->getMessage() . "\n\n");
    exit(1);
}
