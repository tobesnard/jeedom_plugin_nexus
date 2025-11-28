<?php

namespace Nexus\Utils;

class ScenarioLock
{
    /**
     * Lock multi-scenarios sans JSON
     * - Utilise une seule variable Jeedom 'lock' (chaîne)
     * - Chaque scénario a sa propre entrée name|timestamp
     * - Bloque si moins de $delay secondes depuis le dernier passage de CE scénario
     */
    public static function lock(callable $callback, $delay = 3)
    {
        global $scenario;

        $now  = time();
        $name = $scenario->getName();

        // Lire la chaîne existante
        $raw  = \scenario::getData('lock');
        $map  = self::parseLockString($raw);

        if (!isset($map[$name]) || ($now - $map[$name]) >= $delay) {
            $map[$name] = $now;
            \scenario::setData('lock', self::buildLockString($map));
            $scenario->setLog("Lock autorisé pour [$name], mise à jour à $now");
            $callback();
        } else {
            $scenario->setLog("Exécution bloquée pour [$name] (écart=" . ($now - $map[$name]) . "s, délai=$delay s)");
        }
    }
    /**
     * Parse une chaîne "name|time,name2|time2" en tableau associatif [name => time]
     */
    private static function parseLockString($str)
    {
        $map = [];
        if (!is_string($str) || $str === '') {
            return $map;
        }

        foreach (explode(',', $str) as $pair) {
            $pair = trim($pair);
            if ($pair === '') {
                continue;
            }

            $parts = explode('|', $pair, 2);
            if (count($parts) !== 2) {
                continue;
            }

            $name = trim($parts[0]);
            $time = trim($parts[1]);

            if ($name === '' || !ctype_digit($time)) {
                continue;
            }
            $map[$name] = (int)$time;
        }
        return $map;
    }

    /**
     * Recompose le tableau [name => time] vers la chaîne "name|time,name2|time2"
     */
    private static function buildLockString(array $map)
    {
        $items = [];
        foreach ($map as $name => $time) {
            // Sécurité: éviter les délimiteurs dans le nom
            $name = str_replace(['|', ','], '_', $name);
            $items[] = $name . '|' . (int)$time;
        }
        return implode(',', $items);
    }
}
