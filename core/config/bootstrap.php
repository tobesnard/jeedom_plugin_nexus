<?php

/**
 * Nexus Framework - Initialisation
 */

// 1. Chargement des constantes
require_once __DIR__ . '/constants.php';

// 2. Chargement de l'Autoloader et de l'environnement (.env)
if (file_exists(__DIR__ . '/../../vendor/autoload.php')) {
    require_once __DIR__ . '/../../vendor/autoload.php';
}

if (file_exists(__DIR__ . '/../../.env')) {
    $dotenv = Dotenv\Dotenv::createUnsafeImmutable(__DIR__ . '/../../');
    $dotenv->safeLoad();
}

// 3. Chargement du Core Jeedom si présent
if (file_exists(JEEDOM_CORE)) {
    require_once JEEDOM_CORE;
}

// 3. Initialisation de l'environnement (ex: créer le dossier tmp si absent)
if (!is_dir(NEXUS_TMP_DIR)) {
    mkdir(NEXUS_TMP_DIR, 0775, true);
}
