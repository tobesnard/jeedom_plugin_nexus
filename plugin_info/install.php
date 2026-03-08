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

require_once dirname(__FILE__) . '/../../../core/php/core.inc.php';

// Fonction exécutée automatiquement après l'installation du plugin
function nexus_install() {
    $path = realpath(__DIR__ . '/../');
    log::add('nexus', 'info', '[Nexus] Installation des dépendances Composer...');

    // 1. Exécute composer install pour charger les librairies (phpdotenv, etc.)
    // On force HOME=/tmp pour éviter l'erreur de Composer en environnement web (www-data)
    $cmd = 'export HOME=/tmp && cd ' . $path . ' && composer install --no-dev --no-interaction --no-ansi 2>&1';
    exec($cmd, $output, $return_var);

    if ($return_var !== 0) {
        log::add('nexus', 'error', '[Nexus] Erreur lors de l\'installation Composer : ' . implode("\n", $output));
    } else {
        log::add('nexus', 'info', '[Nexus] Dépendances installées.');
        
        // 2. Lancement EXPLICITE de la génération du .env (contourne le --no-scripts de Jeedom)
        $scriptEnv = $path . '/script/setup_env.php';
        if (file_exists($scriptEnv)) {
            $res = shell_exec('php ' . escapeshellarg($scriptEnv) . ' 2>&1');
            log::add('nexus', 'debug', '[Nexus] Résultat génération .env initial : ' . $res);
        } else {
            log::add('nexus', 'error', '[Nexus] Script de configuration introuvable : ' . $scriptEnv);
        }
    }
}

// Fonction exécutée automatiquement après la mise à jour du plugin
function nexus_update() {
    nexus_install();
}

// Fonction exécutée automatiquement après la suppression du plugin
function nexus_remove() {
}
