<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use Nexus\Multimedia\WakeUpCall\WakeUpCall;

function wakeUpCall_bunny()
{
    $cast = WakeUpCall::load('galets', true);
    $cast->playMedia('bunny');
}

function wakeUpCall_siren()
{
    $cast = WakeUpCall::load('galets', true);
    $cast->playMedia('siren');
}

function wakeUpCall_wakeUp()
{
    $cast = WakeUpCall::load('galets', true);
    $cast->playMedia('wakeup');
}

function wakeUpCall_stop()
{
    $cast = WakeUpCall::load('galets', true);
    $cast->stop();
}
