<?php

namespace Nexus\Utils;

// require_once "/var/www/html/core/php/core.inc.php";

// use cmd;
// use dataStore;
// use scenarioExpression;

/**
 * Classe utilitaire pour Jeedom - Nexus Framework
 * Optimisée pour PHP 7.4+ (Type hinting, Performance, Refactoring)
 */
class Utils
{
    /**
     * Échappe les parenthèses pour les expressions régulières.
     */
    public static function escapeChar(string $str): string
    {
        return str_replace(['(', ')'], ['\(', '\)'], $str);
    }

    /**
     * Factorisation de la récupération de statistiques historiques (Min/Max).
     */
    private static function getHistoryStat(string $cmdId, string $startDate, string $endDate, string $type): ?float
    {
        $id = trim(str_replace('#', '', $cmdId));
        $cmd = cmd::byId($id);

        if (!is_object($cmd) || !$cmd->getIsHistorized()) {
            return null;
        }

        $startTime = date('Y-m-d H:i:s', strtotime($startDate));
        $endTime = date('Y-m-d H:i:s', strtotime($endDate));

        $stats = $cmd->getStatistique($startTime, $endTime);

        if (!isset($stats[$type]) || $stats[$type] === '') {
            return null;
        }

        return round((float) $stats[$type], 2);
    }

    // public static function minBetween(string $cmdId, string $startDate, string $endDate): ?float
    // {
    //     return self::getHistoryStat($cmdId, $startDate, $endDate, 'min');
    // }

    // public static function maxBetween(string $cmdId, string $startDate, string $endDate): ?float
    // {
    //     return self::getHistoryStat($cmdId, $startDate, $endDate, 'max');
    // }

    /**
     * Supprime les accents et uniformise le texte (Minuscule, Trim).
     */
    public static function uniform(?string $texte): string
    {
        if (empty($texte)) {
            return '';
        }

        $texte = trim(mb_strtolower($texte, 'UTF-8'));
        $texte = preg_replace('~&([a-z]{1,2})(?:acute|cedil|circ|grave|orn|ring|slash|th|tilde|uml);~i', '$1', htmlentities($texte, ENT_QUOTES, 'UTF-8'));
        return preg_replace('~&[^;]+;~', '', $texte);
    }

    /**
     * Extrait la valeur d'un JSON ou d'une chaîne TTS.
     */
    public static function extractNotificationValue(...$args): string
    {
        $message = implode(',', $args);
        // Utilisation de json_decode si possible, sinon regex
        $data = json_decode($message, true);
        if (json_last_error() === JSON_ERROR_NONE && isset($data['value'])) {
            return (string) $data['value'];
        }
        // return json_encode($args);
        return preg_replace('/.*"value":"([^"]+)".*/', '$1', $message);
    }

    /**
     * Formate un entier HHMM en string HH:MM.
     */
    public static function formatHour($heure): string
    {
        $chaine = str_pad((string) ((int) $heure), 4, "0", STR_PAD_LEFT);
        return substr($chaine, 0, 2) . ":" . substr($chaine, 2, 2);
    }

    /**
     * Interaction Telegram simplifiée avec gestion de variable DataStore.
     */
    // public static function askTelegram(string $title, string $answers, int $timeout, ?string $variableName = null): string
    // {
    //     $varName = $variableName ?? 'ASK_VAR';
    //
    //     $options = [
    //         'question' => $title,
    //         'answer'   => $answers,
    //         'timeout'  => $timeout,
    //         'variable' => $varName,
    //         'cmd'      => '#[Télécommunication][Telegram][Tony]#',
    //     ];
    //
    //     echo "yo";
    //
    //     \scenarioExpression::createAndExec('action', 'ask', $options);
    //
    //
    //     $dataStore = \dataStore::byTypeLinkIdKey('scenario', -1, $varName);
    //     $value = is_object($dataStore) ? $dataStore->getValue() : '';
    //
    //     print_r($dataStore, true);
    //
    //     // Nettoyage si variable temporaire
    //     if (is_object($dataStore) && is_null($variableName)) {
    //         $dataStore->remove();
    //     }
    //
    //     return self::uniform($value);
    // }
}
