<?php

require_once __DIR__ . '/../../../vendor/autoload.php';
require_once "/var/www/html/core/php/core.inc.php";

use Nexus\Utils\Helpers;

/**
* Méthode proxy : Exécute une action auprès de la syncbox
*/
function syncbox_action($action)
{
    return Helpers::execute(function () use ($action) {
        return Nexus\HueSync\Syncbox::action($action);
    });
}

/**
* Méthode proxy : Relève une information auprès de la syncbox
*/
function syncbox_info($info)
{
    return Helpers::execute(function () use ($info) {
        return Nexus\HueSync\Syncbox::info($info);
    });
}
