<?php

require_once __DIR__ . '/../../vendor/autoload.php';
require_once "/var/www/html/core/php/core.inc.php";

use Nexus\Utils\Helpers;
use Nexus\Monitoring\SystemStats;

/**
 * Méthode proxy : Récupère le temps écoulé depuis le dernier redémarrage (uptime).
 * @return string Uptime sous la forme "X jour(s), Y heures et Z minutes".
 */
function monitoring_upTime(): string
{
    return Helpers::execute(function () {
        return SystemStats::upTime();
    }, "");
}

/**
 * Méthode proxy : Récupère les statistiques d'utilisation du disque pour le point de montage racine (/).
 * @return string Stats sous la forme "Total : XGo - Utilisé : YGo (Z%)".
 */
function monitoring_hddStats(): string
{
    return Helpers::execute(function () {
        return SystemStats::hddStats();
    }, "");
}

/**
 * Méthode proxy : Récupère les informations sur la distribution et l'architecture du système.
 * @return string Infos sous la forme "Debian GNU/Linux X (codename) Ybits (arch)".
 */
function monitoring_distribution(): string
{
    return Helpers::execute(function () {
        return SystemStats::distribution();
    }, "");
}

/**
 * Méthode proxy : Récupère le nombre de coeurs CPU et la fréquence maximale.
 * @return string Le nombre de coeurs et la fréquence max sous la forme "X - Y.YYGhz".
 */
function monitoring_cpu(): string
{
    return Helpers::execute(function () {
        return SystemStats::cpu();
    }, "");
}

/**
 * Méthode proxy : Récupère la température du CPU.
 * @return string La température en degrés Celsius sous la forme "X °C".
 */
function monitoring_cpuTemperature(): string
{
    return Helpers::execute(function () {
        return SystemStats::cpuTemperature();
    }, "");
}

/**
 * Méthode proxy : Récupère la charge moyenne du CPU sur 1 minute.
 * @return float La charge moyenne arrondie à 2 décimales.
 */
function monitoring_loadAverage1min(): float
{
    return Helpers::execute(function () {
        return (float) SystemStats::loadAverage1min();
    }, 0.0);
}

/**
 * Méthode proxy : Récupère la charge moyenne du CPU sur 5 minutes.
 * @return float La charge moyenne arrondie à 2 décimales.
 */
function monitoring_loadAverage5min(): float
{
    return Helpers::execute(function () {
        return (float) SystemStats::loadAverage5min();
    }, 0.0);
}

/**
 * Méthode proxy : Récupère la charge moyenne du CPU sur 15 minutes.
 * @return float La charge moyenne arrondie à 2 décimales.
 */
function monitoring_loadAverage15min(): float
{
    return Helpers::execute(function () {
        return (float) SystemStats::loadAverage15min();
    }, 0.0);
}

/**
 * Méthode proxy : Récupère l'utilisation de la RAM.
 * @return string
 */
function monitoring_ramUsed(): string
{
    return Helpers::execute(function () {
        $memoryStats = SystemStats::memoryStats();
        if ($memoryStats) {
            return "{$memoryStats['ram']['used_mo']} Mo ({$memoryStats['ram']['used_percent']}%)";
        }
        return "";
    }, "");
}

/**
 * Méthode proxy : Effectue un test de débit descendant (nécessite speedtest-cli).
 * @return float La vitesse de téléchargement en Mbit/s arrondie à 2 décimales.
 */
function monitoring_speedTest(): float
{
    return Helpers::execute(function () {
        return (float) SystemStats::speedTest();
    }, 0.0);
}
