<?php

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
