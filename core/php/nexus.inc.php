<?php

require_once __DIR__ . '/../config/bootstrap.php';

use Nexus\Utils\Helpers;

/**
 * Inclusion dynamique des fichiers *.inc.php (Méthodes proxy et librairies)
 * Utilise Nexus\Utils\Helpers pour le logging centralisé.
 **/
function requires_inc_php()
{
    // Activation du debug via constante ou variable si nécessaire
    $debug = false;

    $includeDirs = [
        __DIR__,
        dirname(__DIR__, 2) . '/packages',
    ];

    $currentFile = realpath(__FILE__);

    foreach ($includeDirs as $dir) {
        if (!is_dir($dir)) {
            if ($debug) {
                Helpers::log('[Nexus.inc] Répertoire manquant pour inclusion : ' . $dir, 'warning');
            }
            continue;
        }

        $directory = new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS);
        $iterator = new RecursiveIteratorIterator($directory, RecursiveIteratorIterator::SELF_FIRST);

        foreach ($iterator as $file) {
            $path = $file->getPathname();
            $realPath = realpath($path);

            // 1. Validation : fichier .inc.php uniquement et exclusion du fichier courant
            if (!$file->isFile()
                || $file->getExtension() !== 'php'
                || strpos($file->getFilename(), '.inc.php') === false
                || $realPath === $currentFile
            ) {
                continue;
            }

            // 2. Sécurité : Exclusion des répertoires de tests (insensible à la casse)
            if (preg_match('#[\\\\/](tests?|spec|phpunit)[\\\\/]#i', $path)) {
                continue;
            }

            // 3. Inclusion sécurisée via le Helper si nécessaire ou direct
            include_once $path;

            if ($debug) {
                Helpers::log('[Nexus.inc] Fichier inclus dynamiquement : ' . $path, 'debug');
            }
        }
    }
}

// Lancement de l'auto-inclusion
requires_inc_php();
