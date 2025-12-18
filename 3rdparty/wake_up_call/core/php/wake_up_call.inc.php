<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use Nexus\Multimedia\WakeUpCall\WakeUpCall;

/**
 * Joue la vidéo de test Big Buck Bunny sur l'appareil 'galets'.
 */
function wakeUpCall_bunny()
{
    $cast = WakeUpCall::load('galets', true);
    $cast->playMedia('bunny');
}

/**
 * Joue la sirène d'alarme sur l'appareil 'galets'.
 */
function wakeUpCall_siren()
{
    $cast = WakeUpCall::load('galets', true);
    $cast->playMedia('siren');
}

/**
 * Joue la musique de réveil sur l'appareil 'galets'.
 */
function wakeUpCall_wakeUp()
{
    $cast = WakeUpCall::load('galets', true);
    $cast->playMedia('wakeup');
}

/**
 * Arrête la lecture en cours sur l'appareil 'galets'.
 */
function wakeUpCall_stop()
{
    $cast = WakeUpCall::load('galets', true);
    $cast->stop();
}
