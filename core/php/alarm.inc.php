<?php

require_once __DIR__ . '/../../vendor/autoload.php';
require_once "/var/www/html/core/php/core.inc.php";

use Nexus\Utils\Helpers;
use Nexus\Alarm\AlertBroadcaster;

/**
 * Méthode proxy : Diffuse le message de mise en garde niveau 1
 */
function alarm_infoMessage()
{
    Helpers::execute(function () {
        AlertBroadcaster::infoMessage();
    });
}

/**
 * Méthode proxy : Diffuse le message de mise en garde niveau 2
 */
function alarm_warningMessage()
{
    Helpers::execute(function () {
        AlertBroadcaster::warningMessage();
    });
}

/**
 * Méthode proxy : Active la sirène sur les galets
 */
function alarm_galetsSirenOn()
{
    Helpers::execute(function () {
        AlertBroadcaster::galetsSirenOn();
    });
}

/**
 * Méthode proxy : Coupe la sirène sur les galets
 */
function alarm_galetsSirenOff()
{
    Helpers::execute(function () {
        AlertBroadcaster::galetsSirenOff();
    });
}
