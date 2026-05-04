<?php

require_once __DIR__ . "/../../../../vendor/autoload.php";

use Nexus\Security\Camera\ReolinkSecurityManager;
use Nexus\Utils\Helpers;

/**
 * Proxy : Arme la caméra Reolink (Mode Away - Surveillance active)
 * @return array ['action' => string, 'success' => bool, 'response' => string]
 */
function camera_arm()
{
    return Helpers::execute(function () {
        $reolinkIp = getenv('REOLINK_IP');
        $reolinkUsername = getenv('REOLINK_USERNAME');
        $reolinkPassword = getenv('REOLINK_PASSWORD');

        echo "Tentative d'armement de la caméra Reolink à l'adresse {$reolinkIp} avec l'utilisateur {$reolinkUsername}\n";
        
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
 * @return array ['action' => string, 'success' => bool, 'response' => string]
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