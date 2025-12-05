<?php

namespace Nexus\Utils;

/**
 * Classe utilitaire pour des fonctions de support (échappement de caractères)
 * et des fonctions d'aide aux statistiques d'historique Jeedom.
 */
class Utils
{
    /**
     * Échappe certains caractères spéciaux dans une chaîne pour une utilisation spécifique.
     * Actuellement échappe les parenthèses () pour les rendre littérales (e.g., dans une regex).
     * * @param string $_str La chaîne à échapper.
     * @return string La chaîne échappée.
     */
    public static function escapeChar($_str)
    {
        $data = $_str;
        $data = str_replace("(", "\(", $data);
        $data = str_replace(")", "\)", $data);
        return $data;
    }

    /**
     * Récupère la valeur minimale historique d'une commande entre deux dates.
     * Augmente la précision de la fonction homonyme du core Jeedom.
     * * @param string $_cmd_id L'ID de la commande (ex: #123#).
     * @param string $_startDate Date/heure de début.
     * @param string $_endDate Date/heure de fin.
     * @return float|string La valeur minimale arrondie à 2 décimales, ou une chaîne vide si erreur/non historisé.
     */
    public static function min_between($_cmd_id, $_startDate, $_endDate)
    {
        /** Augmente la précision de la fonction homonyme du core Jeedom */
        $cmd = cmd::byId(trim(str_replace('#', '', $_cmd_id)));

        // Vérification de l'objet commande et de l'historisation
        if (!is_object($cmd) || $cmd->getIsHistorized() == 0) {
            return '';
        }

        // Formatage des dates pour Jeedom
        $_startTime = date('Y-m-d H:i:s', strtotime($_startDate));
        $_endTime = date('Y-m-d H:i:s', strtotime($_endDate));

        $historyStatistique = $cmd->getStatistique($_startTime, $_endTime);

        if (!isset($historyStatistique['min'])) {
            return '';
        }

        // Retourne la valeur minimale arrondie
        return round($historyStatistique['min'], 2);
    }


    /**
     * Récupère la valeur maximale historique d'une commande entre deux dates.
     * Augmente la précision de la fonction homonyme du core Jeedom.
     * * @param string $_cmd_id L'ID de la commande (ex: #123#).
     * @param string $_startDate Date/heure de début.
     * @param string $_endDate Date/heure de fin.
     * @return float|string La valeur maximale arrondie à 2 décimales, ou une chaîne vide si erreur/non historisé.
     */
    public static function max_between($_cmd_id, $_startDate, $_endDate)
    {
        /** Augmente la précision de la fonction homonyme du core Jeedom */
        $cmd = cmd::byId(trim(str_replace('#', '', $_cmd_id)));

        // Vérification de l'objet commande et de l'historisation
        if (!is_object($cmd) || $cmd->getIsHistorized() == 0) {
            return '';
        }

        // Formatage des dates pour Jeedom
        $_startTime = date('Y-m-d H:i:s', strtotime($_startDate));
        $_endTime = date('Y-m-d H:i:s', strtotime($_endDate));

        $historyStatistique = $cmd->getStatistique($_startTime, $_endTime);

        if (!isset($historyStatistique['max'])) {
            return '';
        }

        // Retourne la valeur maximale arrondie
        return round($historyStatistique['max'], 2);
    }
}
