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
 * Inclusion des fichiers 3rdparty/../*.inc.php (Méthodes proxy et librairies)
 **/
function requires_inc_php()
{
    $thirdparty_dir = dirname(__DIR__, 2) . '/3rdparty';

    if (!is_dir($thirdparty_dir)) {
        if (class_exists('log')) {
            log::add('nexus', 'warn', 'Le répertoire 3rdparty n\'existe pas : ' . $thirdparty_dir);
        }
        return;
    }

    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($thirdparty_dir, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST
    );

    foreach ($iterator as $file) {
        if ($file->isFile() && $file->getExtension() === 'php' && strpos($file->getFilename(), '.inc.php') !== false) {
            include_once $file->getPathname();
        }
    }
}

requires_inc_php();




class nexustest
{
    public static function dir()
    {
        return __DIR__;
    }
}
