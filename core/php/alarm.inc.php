<?php

require_once __DIR__ . '/../../vendor/autoload.php';

/**
 * Méthode proxy : Diffuse le message de mise en garde niveau 1
 */
function alarm_infoMessage()
{
    Nexus\Alarm\AlertBroadcaster::infoMessage();
}

/**
 * Méthode proxy : Diffuse le message de mise en garde niveau 2
 */
function alarm_warningMessage()
{
    Nexus\Alarm\AlertBroadcaster::warningMessage();
}

/**
 * Méthode proxy : Active la sirène sur les galets
 */
function alarm_galetsSirenOn()
{
    Nexus\Alarm\AlertBroadcaster::galetsSirenOn();
}

/**
 * Méthode proxy : Coupe la sirène sur les galets
 */
function alarm_galetsSirenOff()
{
    Nexus\Alarm\AlertBroadcaster::galetsSirenOff();
}
