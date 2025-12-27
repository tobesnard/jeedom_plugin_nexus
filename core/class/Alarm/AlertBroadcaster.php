<?php

namespace Nexus\Alarm;

require_once __DIR__ . '/../../../vendor/autoload.php';

class AlertBroadcaster
{
    // Constantes de commandes
    private const CMD_GALET_TTS   = "#[Multimédia][Galets][TTS]#";
    private const CMD_GALET_SOUND = "#[Multimédia][Galets][Sounds]#";
    private const CMD_GALET_STOP  = "#[Multimédia][Galets][Media Stop]#";
    private const SIRENS_ON  = "#[Sécurité][Sirènes Heiman][Sirène Long]#";
    private const SIREN_OFF  = "#[Sécurité][Sirènes Heiman][Off]#";

    /**
     * Diffuse une alerte de type sirène sur les Google Home (Galets)
     */
    public static function galetsSirenOn(string $soundName = "Tornado Siren")
    {
        $cmd = \cmd::byString(self::CMD_GALET_SOUND);
        if (!is_object($cmd)) {
            return;
        }

        // Gestion du volume via le nom de l'équipement parent
        $eqName = $cmd->getEqLogic()->getName();
        $oldVolume = self::setVolume($eqName, 100);
        \log::add('edom', 'info', "galetAlert ON : $eqName (Volume précédent: $oldVolume)");

        // Recherche du fichier son dans la configuration de la commande
        $filename = self::getSoundFile($cmd, $soundName);
        if ($filename) {
            $cmd->execCmd(['select' => $filename]);
        }
    }

    /**
     * Arrête les diffusions sur les galets
     */
    public static function galetsSirenOff()
    {
        $cmd = \cmd::byString(self::CMD_GALET_STOP);
        if (is_object($cmd)) {
            $cmd->execCmd();
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
     * @return string|null Ancien volume pour restauration ultérieure
     */
    private static function setVolume(string $equipement_name, int $value)
    {
        $cmd_vol_get = \cmd::byString("#[Multimédia][" . $equipement_name . "][Volume]#");
        $cmd_vol_set = \cmd::byString("#[Multimédia][" . $equipement_name . "][Volume Set]#");

        $oldVolume = is_object($cmd_vol_get) ? $cmd_vol_get->execCmd() : null;

        if (is_object($cmd_vol_set)) {
            $cmd_vol_set->execCmd(['slider' => $value]);
        }

        return $oldVolume;
    }

    /**
     * Centralisation de l'envoi TTS avec mise au volume max
     */
    private static function broadcastTTS(string $message)
    {
        $cmd = \cmd::byString(self::CMD_GALET_TTS);
        if (is_object($cmd)) {
            self::setVolume($cmd->getEqLogic()->getName(), 100);
            $cmd->execCmd(['message' => $message]);
        }
    }

    /**
     * Parse la configuration de la commande de type 'liste' pour trouver le fichier correspondant au label
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
