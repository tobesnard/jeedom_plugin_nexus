<?php

require_once __DIR__ . '/../../../../vendor/autoload.php';

use Nexus\Cloud\BackupManager;
use Nexus\Utils\Helpers;

/* Méthode Proxy : Enregistre sur le cloud les sauvegardes Jeedom et nettoie les anciennes */
function cloud_backup()
{

    Helpers::execute(function () {
        $backup = new BackupManager();
        $backup->run();
    });
}
