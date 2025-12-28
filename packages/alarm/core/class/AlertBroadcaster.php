<?php

namespace Nexus\Alarm;

require_once __DIR__ . '/../../../../vendor/autoload.php';

use Nexus\Utils\Helpers;
use Nexus\Utils\Config;
use Nexus\Jeedom\Services\JeedomCmdService;

/**
 * Service de diffusion d'alertes - Nexus Framework
 * Pilotage des alertes sonores et TTS via la configuration centralisée.
 */
class AlertBroadcaster
{
    /**
     * Diffuse une alerte de type sirène sur les Google Home (Galets)
     * @param string $soundName Nom du label sonore à rechercher
     */
    public static function galetsSirenOn(string $soundName = "Tornado Siren")
    {
        $cmdString = Config::get('cmd_galets_sound');
        $cmd = \cmd::byString($cmdString);

        if (!is_object($cmd)) {
            Helpers::log("[AlertBroadcaster] Erreur : Commande son introuvable ($cmdString)", 'error');
            return;
        }

        // Gestion du volume via l'équipement parent
        $eqName = $cmd->getEqLogic()->getName();
        $oldVolume = self::setVolume($eqName, 100);

        Helpers::log("[AlertBroadcaster] galetAlert ON : $eqName (Volume précédent: $oldVolume)", 'info');

        // Recherche du fichier son dans la liste de la commande
        $filename = self::getSoundFile($cmd, $soundName);
        if ($filename) {
            JeedomCmdService::getInstance()->execByString($cmdString, ['select' => $filename]);
        } else {
            Helpers::log("[AlertBroadcaster] Fichier son '$soundName' introuvable dans la configuration de $eqName", 'warning');
        }
    }

    /**
     * Arrête les diffusions multimédia sur les galets
     */
    public static function galetsSirenOff()
    {
        $cmdString = Config::get('cmd_galets_stop');
        if ($cmdString) {
            JeedomCmdService::getInstance()->execByString($cmdString);
        }
    }

    /**
     * Messages TTS prédéfinis
     */
    public static function infoMessage()
    {
        self::broadcastTTS("Attention. Alerte intrusion. Le voisinage est prévenu. L’agent municipal arrive. La sirène va se déclencher.");
    }

    public static function warningMessage()
    {
        self::broadcastTTS("Attention. Alarme silencieuse en cours. Vous venez de pénétrer dans une zone sous surveillance électronique. Intervention en cours. Une alerte est envoyée au voisinage. L’agent municipal est informé de l’intrusion. Attention. la sirène va être déclenchée. Veuillez quitter la zone Immédiatement.");
    }

    /**
     * Gère le volume d'un équipement multimédia
     * @return string|null Ancien volume lu avant modification
     */
    private static function setVolume(string $equipement_name, int $value)
    {
        $cmdVolGet = "#[Multimédia][" . $equipement_name . "][Volume]#";
        $cmdVolSet = "#[Multimédia][" . $equipement_name . "][Volume Set]#";

        // Lecture du volume actuel (mise en cache possible via JeedomCmdService)
        $oldVolume = JeedomCmdService::getInstance()->execByString($cmdVolGet);

        // Application du nouveau volume
        JeedomCmdService::getInstance()->execByString($cmdVolSet, ['slider' => $value]);

        return $oldVolume;
    }

    /**
     * Centralisation de l'envoi TTS
     */
    private static function broadcastTTS(string $message)
    {
        $cmdString = Config::get('cmd_galets_tts');

        if ($cmdString) {
            $cmd = \cmd::byString($cmdString);
            if (is_object($cmd)) {
                // On s'assure que le volume est à 100 avant le TTS
                self::setVolume($cmd->getEqLogic()->getName(), 100);
                JeedomCmdService::getInstance()->execByString($cmdString, ['message' => $message]);
                return;
            }
        }

        Helpers::log("[AlertBroadcaster] Échec diffusion TTS : Commande '$cmdString' introuvable", 'error');
    }

    /**
     * Parse la configuration de la commande type 'liste' (format: file|label;file2|label2)
     */
    private static function getSoundFile($cmd, $searchLabel)
    {
        $config = $cmd->getConfiguration();
        $songs = explode(';', $config['listValue'] ?? '');
        foreach ($songs as $s) {
            $parts = explode('|', $s);
            if (count($parts) == 2 && trim($parts[1]) == $searchLabel) {
                return $parts[0];
            }
        }
        return null;
    }
}
