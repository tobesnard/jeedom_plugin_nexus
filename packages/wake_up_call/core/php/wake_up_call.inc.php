<?php

require_once __DIR__ . '/../../../../vendor/autoload.php';
require_once "/var/www/html/core/php/core.inc.php";

use Nexus\Multimedia\WakeUpCall\WakeUpCall;
use Nexus\Utils\Helpers;

/**
 * Joue la vidéo de test Big Buck Bunny sur l'appareil 'galets'.
 */
function wakeUpCall_bunny()
{
    Helpers::execute(function () {
        $cast = WakeUpCall::load('galets', true);
        $cast->playMedia('bunny');
    });
}

/**
 * Joue la sirène d'alarme sur l'appareil 'galets'.
 */
function wakeUpCall_siren()
{
    Helpers::execute(function () {
        $cast = WakeUpCall::load('galets', true);
        $cast->playMedia('siren');
    });
}

/**
 * Joue la musique de réveil sur l'appareil 'galets'.
 */
function wakeUpCall_wakeUp()
{
    Helpers::execute(function () {
        $cast = WakeUpCall::load('galets', true);
        $cast->playMedia('wakeup');
    });
}

/**
 * Arrête la lecture en cours sur l'appareil 'galets'.
 */
function wakeUpCall_stop()
{
    Helpers::execute(function () {
        $cast = WakeUpCall::load('galets', true);
        $cast->stop();
    });
}
