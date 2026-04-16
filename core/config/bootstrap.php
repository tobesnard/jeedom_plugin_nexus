<?php
// Chargement automatique du .env pour les variables d'environnement (API keys, etc.)

// Chargement explicite de l'autoload Composer
if (file_exists(__DIR__ . '/../../../vendor/autoload.php')) {
    require_once __DIR__ . '/../../../vendor/autoload.php';
}

// Vérification de la présence du fichier .env
// Correction du chemin pour pointer vers la racine du plugin Nexus
$envPath = dirname(__DIR__, 2) . '/.env';
if (!file_exists($envPath)) {
    error_log('[DEBUG][Dotenv] Fichier .env introuvable à ' . $envPath);
} else {
    error_log('[DEBUG][Dotenv] Fichier .env trouvé à ' . $envPath);
    // Chargement Dotenv avec gestion d'erreur
    try {
        if (class_exists('Dotenv\\Dotenv')) {
            $dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__, 2));
            $dotenv->load();
            error_log('[DEBUG][Dotenv] .env chargé');
            error_log('[DEBUG][Dotenv] getenv(GEMINI_API_KEY)=' . getenv('GEMINI_API_KEY'));
            error_log('[DEBUG][Dotenv] $_ENV[GEMINI_API_KEY]=' . ($_ENV['GEMINI_API_KEY'] ?? ''));
        } else {
            error_log('[DEBUG][Dotenv] Classe Dotenv\\Dotenv introuvable après require autoload.');
        }
    } catch (Exception $e) {
        error_log('[DEBUG][Dotenv] Exception : ' . $e->getMessage());
    }
}

/**
 * Nexus Framework - Initialisation
 */

// 1. Chargement des constantes
require_once __DIR__ . '/constants.php';

// 2. Chargement du Core Jeedom si présent
if (file_exists(JEEDOM_CORE)) {
    require_once JEEDOM_CORE;
}

// 3. Initialisation de l'environnement (ex: créer le dossier tmp si absent)
if (!is_dir(NEXUS_TMP_DIR)) {
    mkdir(NEXUS_TMP_DIR, 0775, true);
}
