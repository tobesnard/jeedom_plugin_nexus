<?php

require_once __DIR__ . "/../../../../vendor/autoload.php";

use Nexus\Security\Camera\ReolinkSecurityManager;
use Nexus\Utils\Helpers;

/**
 * Proxy : Arme la caméra Reolink (Mode Away - Surveillance active)
 */
function camera_arm()
{
    return Helpers::execute(function () {
        $reolinkIp = getenv('REOLINK_IP');
        $reolinkUsername = getenv('REOLINK_USERNAME');
        $reolinkPassword = getenv('REOLINK_PASSWORD');

        $manager = new ReolinkSecurityManager($reolinkIp, $reolinkUsername, $reolinkPassword);
        $result = $manager->armAll();
        
        if ($result['success']) {
            Helpers::log("[Camera] Armement réussi", 'info');
        } else {
            Helpers::log("[Camera] Échec armement: " . $result['response'], 'error');
        }

    },  "Erreur lors de l\'armement de la caméra Reolink");
}

/** 
 * Proxy : Désarme la caméra Reolink (Mode Home - Surveillance désactivée)
 */
function camera_disarm()
{
    return Helpers::execute(function () {
        $reolinkIp = getenv('REOLINK_IP');
        $reolinkUsername = getenv('REOLINK_USERNAME');
        $reolinkPassword = getenv('REOLINK_PASSWORD');
        $manager = new ReolinkSecurityManager($reolinkIp, $reolinkUsername, $reolinkPassword);
        $result = $manager->disarmAll();
        
        if ($result['success']) {
            Helpers::log("[Camera] Désarmement réussi", 'info');
        } else {
            Helpers::log("[Camera] Échec désarmement: " . $result['response'], 'error');
        }
        
    },  "Erreur lors du désarmement de la caméra Reolink");
}   


/**
 * Proxy : GetPushV20 - Récupère l'état actuel des notifications push de la caméra Reolink.
 *
 * @return bool true si les notifications push sont activées, false sinon.
 */
function camera_getPushStatus(): bool
{
    return Helpers::execute(function () {
        $reolinkIp = getenv('REOLINK_IP');
        $reolinkUsername = getenv('REOLINK_USERNAME');
        $reolinkPassword = getenv('REOLINK_PASSWORD');
        $manager = new ReolinkSecurityManager($reolinkIp, $reolinkUsername, $reolinkPassword);
        return $manager->getPushStatus();
    }, false);
}


/** Proxy : GetEmailV20 - Récupère l'état actuel des notifications email de la caméra Reolink.
 *
 * @return bool true si les notifications email sont activées, false sinon.
 */
function camera_getMailStatus(): bool
{
    return Helpers::execute(function () {
        $reolinkIp = getenv('REOLINK_IP');
        $reolinkUsername = getenv('REOLINK_USERNAME');
        $reolinkPassword = getenv('REOLINK_PASSWORD');
        $manager = new ReolinkSecurityManager($reolinkIp, $reolinkUsername, $reolinkPassword);
        return $manager->getMailStatus();
    }, false);
}

/** Proxy : GetRecV20 - Récupère l'état actuel des enregistrements de la caméra Reolink.
 *
 * @return bool true si les enregistrements sont activés, false sinon.
 */
function camera_getRecStatus(): bool
{
    return Helpers::execute(function () {
        $reolinkIp = getenv('REOLINK_IP');
        $reolinkUsername = getenv('REOLINK_USERNAME');
        $reolinkPassword = getenv('REOLINK_PASSWORD');
        $manager = new ReolinkSecurityManager($reolinkIp, $reolinkUsername, $reolinkPassword);
        return $manager->getRecStatus();
    }, false);
}

/** Proxy : GetAudioAlarmV20 - Récupère l'état actuel de la sirène de la caméra Reolink.
 *
 * @return bool true si la sirène est activée, false sinon.
 */
function camera_getSirenStatus(): bool
{
    return Helpers::execute(function () {
        $reolinkIp = getenv('REOLINK_IP');
        $reolinkUsername = getenv('REOLINK_USERNAME');
        $reolinkPassword = getenv('REOLINK_PASSWORD');
        $manager = new ReolinkSecurityManager($reolinkIp, $reolinkUsername, $reolinkPassword);
        return $manager->getSirenStatus();
    }, false);
}

/** Proxy : GetBuzzerAlarmV20 - Récupère l'état actuel du buzzer de la caméra Reolink.
 *
 * @return bool true si le buzzer est activé, false sinon.
 */
function camera_getBuzzerStatus(): bool
{
    return Helpers::execute(function () {
        $reolinkIp = getenv('REOLINK_IP');
        $reolinkUsername = getenv('REOLINK_USERNAME');
        $reolinkPassword = getenv('REOLINK_PASSWORD');
        $manager = new ReolinkSecurityManager($reolinkIp, $reolinkUsername, $reolinkPassword);
        return $manager->getBuzzerStatus();
    }, false);
}

/** Proxy : GetWhiteLed - Récupère l'état actuel du projecteur de la caméra Reolink.
 *
 * @return bool true si le projecteur est activé, false sinon.
 */
function camera_getSpotlightStatus(): bool
{
    return Helpers::execute(function () {
        $reolinkIp = getenv('REOLINK_IP');
        $reolinkUsername = getenv('REOLINK_USERNAME');
        $reolinkPassword = getenv('REOLINK_PASSWORD');
        $manager = new ReolinkSecurityManager($reolinkIp, $reolinkUsername, $reolinkPassword);
        return $manager->getSpotlightStatus();
    }, false);
}