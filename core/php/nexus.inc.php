<?php

/* This file is part of Jeedom.
*
* Jeedom is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
* Jeedom is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with Jeedom. If not, see <http://www.gnu.org/licenses/>.
*/

require_once __DIR__  . '/../../../../core/php/core.inc.php';

// Classes **/core/class généré par `composer dump-autoload` ou dépendance automatique Jeedom
require_once __DIR__ . '/../../vendor/autoload.php';

/**
 * Inclusion des fichiers *.inc.php (Méthodes proxy et librairies)
 * Exclusion automatique des répertoires liés aux tests (tests, test, Spec, etc.)
 **/
function requires_inc_php()
{
    $debug = false;

    // Tableau de répertoires à inclure
    $includeDirs = [
        __DIR__,
        dirname(__DIR__, 2) . '/3rdparty',
    ];

    $currentFile = realpath(__FILE__);

    foreach ($includeDirs as $dir) {
        if (!is_dir($dir)) {
            if (class_exists('log') && $debug) {
                log::add('nexus', 'warn', 'Répertoire manquant : ' . $dir);
            }
            continue;
        }

        $directory = new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS);
        $iterator = new RecursiveIteratorIterator($directory, RecursiveIteratorIterator::SELF_FIRST);

        foreach ($iterator as $file) {
            $path = $file->getPathname();
            $realPath = realpath($path);

            // 1. Vérification extension et exclusion fichier courant
            if (!$file->isFile() || $file->getExtension() !== 'php' || strpos($file->getFilename(), '.inc.php') === false || $realPath === $currentFile) {
                continue;
            }

            // 2. Exclusion des répertoires de tests (insensible à la casse)
            // Filtre les segments de chemin : /tests/, /test/, /UnitTests/, etc.
            if (preg_match('#[\\\\/](tests?|spec|phpunit)[\\\\/]#i', $path)) {
                continue;
            }

            include_once $path;

            if (class_exists('log') && $debug) {
                log::add('nexus', 'debug', 'Fichier inclus : ' . $path);
            }
        }
    }
}

requires_inc_php();
