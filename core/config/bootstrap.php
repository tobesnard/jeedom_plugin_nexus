<?php

/**
 * Nexus Framework - Core Initializer
 * Gère le chargement de l'autoloader, des variables d'environnement et du framework Jeedom.
 */

declare(strict_types=1);

// 1. Chargement de l'autoloader Composer
$autoloadPath = dirname(__DIR__, 3) . '/vendor/autoload.php';
if (!file_exists($autoloadPath)) {
    // Fallback si le chemin relatif diffère
    $autoloadPath = __DIR__ . '/../../../vendor/autoload.php';
}

if (file_exists($autoloadPath)) {
    require_once $autoloadPath;
}

// 2. Gestion des variables d'environnement (.env)
$envDir = dirname(__DIR__, 2);
$envFile = $envDir . '/.env';

if (file_exists($envFile) && class_exists('Dotenv\Dotenv')) {
    try {
        // Utilisation de createUnsafeImmutable() pour peupler automatiquement $_ENV, $_SERVER ET getenv()
        // Cela évite la boucle foreach manuelle avec putenv()
        $dotenv = Dotenv\Dotenv::createUnsafeImmutable($envDir);
        $dotenv->load();
        
        // Optionnel : Forcer la présence de variables critiques
        // $dotenv->required(['GEMINI_API_KEY', 'RCLONE_CONFIG_GOOGLE_DRIVE_TOKEN']);
        
    } catch (\Throwable $e) {
        error_log('[Nexus][Dotenv] Erreur lors du chargement : ' . $e->getMessage());
    }
} elseif (!file_exists($envFile)) {
    error_log("[Nexus][Dotenv] Fichier absent : $envFile");
}

// 3. Chargement des configurations et constantes
$constantsPath = __DIR__ . '/constants.php';
if (file_exists($constantsPath)) {
    require_once $constantsPath;
}

// 4. Initialisation du Core Jeedom
// On vérifie si la constante JEEDOM_CORE est définie (via constants.php)
if (defined('JEEDOM_CORE') && file_exists(JEEDOM_CORE)) {
    require_once JEEDOM_CORE;
}

// 5. Gestion des répertoires temporaires
if (defined('NEXUS_TMP_DIR') && !is_dir(NEXUS_TMP_DIR)) {
    if (!mkdir(NEXUS_TMP_DIR, 0775, true) && !is_dir(NEXUS_TMP_DIR)) {
        error_log(sprintf('[Nexus] Impossible de créer le répertoire : %s', NEXUS_TMP_DIR));
    }
}