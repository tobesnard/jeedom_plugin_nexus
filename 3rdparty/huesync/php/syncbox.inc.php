<?php

require_once __DIR__ . '/../vendor/autoload.php';

/**
* Méthode proxy : Exécute une action auprès de la syncbox
*/
function syncbox_action($action)
{
    return Nexus\HueSync\Syncbox::action($action);
}

/**
* Méthode proxy : Relève une information auprès de la syncbox
*/
function syncbox_info($info)
{
    return Nexus\HueSync\Syncbox::info($info);
}
