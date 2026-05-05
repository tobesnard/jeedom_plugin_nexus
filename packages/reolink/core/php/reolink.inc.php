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
/**
 * Proxy : Arrête tout mouvement PTZ en cours.
 */
function camera_ptzCtrlStop()
{
    return Helpers::execute(function () {
        $reolinkIp = getenv('REOLINK_IP');
        $reolinkUsername = getenv('REOLINK_USERNAME');
        $reolinkPassword = getenv('REOLINK_PASSWORD');

        $manager = new ReolinkSecurityManager($reolinkIp, $reolinkUsername, $reolinkPassword);
        $result = $manager->stopMove();

        if ($result['success']) {
            Helpers::log("[Camera] PTZ Stop réussi", 'info');
        } else {
            Helpers::log("[Camera] Échec PTZ Stop: " . json_encode($result['response']), 'error');
        }
    }, "Erreur lors de l'arrêt PTZ");
}

/**
 * Proxy : Oriente la caméra vers le haut.
 */
function camera_ptzCtrlUp()
{
    return Helpers::execute(function () {
        $reolinkIp = getenv('REOLINK_IP');
        $reolinkUsername = getenv('REOLINK_USERNAME');
        $reolinkPassword = getenv('REOLINK_PASSWORD');

        $manager = new ReolinkSecurityManager($reolinkIp, $reolinkUsername, $reolinkPassword);
        $result = $manager->moveUp();

        if ($result['success']) {
            Helpers::log("[Camera] PTZ Haut réussi", 'info');
        } else {
            Helpers::log("[Camera] Échec PTZ Haut: " . json_encode($result['response']), 'error');
        }
    }, "Erreur mouvement Haut");
}

/**
 * Proxy : Oriente la caméra vers le bas.
 */
function camera_ptzCtrlDown()
{
    return Helpers::execute(function () {
        $reolinkIp = getenv('REOLINK_IP');
        $reolinkUsername = getenv('REOLINK_USERNAME');
        $reolinkPassword = getenv('REOLINK_PASSWORD');

        $manager = new ReolinkSecurityManager($reolinkIp, $reolinkUsername, $reolinkPassword);
        $result = $manager->moveDown();

        if ($result['success']) {
            Helpers::log("[Camera] PTZ Bas réussi", 'info');
        } else {
            Helpers::log("[Camera] Échec PTZ Bas: " . json_encode($result['response']), 'error');
        }
    }, "Erreur mouvement Bas");
}

/**
 * Proxy : Oriente la caméra vers la droite.
 */
function camera_ptzCtrlRight()
{
    return Helpers::execute(function (){
        $reolinkIp = getenv('REOLINK_IP');
        $reolinkUsername = getenv('REOLINK_USERNAME');
        $reolinkPassword = getenv('REOLINK_PASSWORD');

        $manager = new ReolinkSecurityManager($reolinkIp, $reolinkUsername, $reolinkPassword);
        $result = $manager->moveRight();

        if ($result['success']) {
            Helpers::log("[Camera] PTZ Droite réussi", 'info');
        } else {
            Helpers::log("[Camera] Échec PTZ Droite: " . json_encode($result['response']), 'error');
        }
    }, "Erreur mouvement Droite");
}

/**
 * Proxy : Oriente la caméra vers la gauche.
 */
function camera_ptzCtrlLeft()
{
    return Helpers::execute(function (){
        $reolinkIp = getenv('REOLINK_IP');
        $reolinkUsername = getenv('REOLINK_USERNAME');
        $reolinkPassword = getenv('REOLINK_PASSWORD');

        $manager = new ReolinkSecurityManager($reolinkIp, $reolinkUsername, $reolinkPassword);
        $result = $manager->moveLeft();

        if ($result['success']) {
            Helpers::log("[Camera] PTZ Gauche réussi", 'info');
        } else {
            Helpers::log("[Camera] Échec PTZ Gauche: " . json_encode($result['response']), 'error');
        }
    }, "Erreur mouvement Gauche");
}

/**
 * Proxy : Zoom avant de la caméra Reolink.
 */
function camera_zoomInc()
{
    return Helpers::execute(function (){
        $reolinkIp = getenv('REOLINK_IP');
        $reolinkUsername = getenv('REOLINK_USERNAME');
        $reolinkPassword = getenv('REOLINK_PASSWORD');

        $manager = new ReolinkSecurityManager($reolinkIp, $reolinkUsername, $reolinkPassword, 1);
        $result = $manager->zoomInc();

        if ($result['success']) {
            Helpers::log("[Camera] PTZ Zoom Inc réussi", 'info');
        } else {
            Helpers::log("[Camera] Échec PTZ Zoom Inc: " . json_encode($result['response']), 'error');
        }
    }, "Erreur mouvement Zoom Inc");
}

/**
 * Proxy : Zoom arrière de la caméra Reolink.
 */
function camera_zoomDec()
{
    return Helpers::execute(function (){
        $reolinkIp = getenv('REOLINK_IP');
        $reolinkUsername = getenv('REOLINK_USERNAME');
        $reolinkPassword = getenv('REOLINK_PASSWORD');

        $manager = new ReolinkSecurityManager($reolinkIp, $reolinkUsername, $reolinkPassword, 1);
        $result = $manager->zoomDec();

        if ($result['success']) {
            Helpers::log("[Camera] PTZ Zoom Dec réussi", 'info');
        } else {
            Helpers::log("[Camera] Échec PTZ Zoom Dec: " . json_encode($result['response']), 'error');
        }
    }, "Erreur mouvement Zoom Dec");
}