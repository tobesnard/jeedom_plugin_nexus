<?php

require_once __DIR__ . '/../../vendor/autoload.php';


/**
 * Méthode proxy : Récupère le temps écoulé depuis le dernier redémarrage (uptime).
 * @return string Uptime sous la forme "X jour(s), Y heures et Z minutes".
 */
function monitoring_upTime()
{
    return Nexus\Monitoring\SystemStats::upTime();
}

/**
 * Méthode proxy : Récupère les statistiques d'utilisation du disque pour le point de montage racine (/).
 * @return string Stats sous la forme "Total : XGo - Utilisé : YGo (Z%)".
 */
function monitoring_hddStats()
{
    return Nexus\Monitoring\SystemStats::hddStats();
}

/**
 * Méthode proxy : Récupère les informations sur la distribution et l'architecture du système.
 * @return string Infos sous la forme "Debian GNU/Linux X (codename) Ybits (arch)".
 */
function monitoring_distribution()
{
    return Nexus\Monitoring\SystemStats::distribution();
}

/**
 * Méthode proxy : Récupère le nombre de coeurs CPU et la fréquence maximale.
 * @return string Le nombre de coeurs et la fréquence max sous la forme "X - Y.YYGhz".
 */
function monitoring_cpu()
{
    return Nexus\Monitoring\SystemStats::cpu();
}

/**
 * Méthode proxy : Récupère la température du CPU.
 * @return string La température en degrés Celsius sous la forme "X °C".
 */
function monitoring_cpuTemperature()
{
    return Nexus\Monitoring\SystemStats::cpuTemperature();
}

/**
 * Méthode proxy : Récupère la charge moyenne du CPU sur 1 minute.
 * @return float La charge moyenne arrondie à 2 décimales.
 */
function monitoring_loadAverage1min()
{
    return Nexus\Monitoring\SystemStats::loadAverage1min();
}

/**
 * Méthode proxy : Récupère la charge moyenne du CPU sur 5 minutes.
 * @return float La charge moyenne arrondie à 2 décimales.
 */
function monitoring_loadAverage5min()
{
    return Nexus\Monitoring\SystemStats::loadAverage5min();
}

/**
 * Méthode proxy : Récupère la charge moyenne du CPU sur 15 minutes.
 * @return float La charge moyenne arrondie à 2 décimales.
 */
function monitoring_loadAverage15min()
{
    return Nexus\Monitoring\SystemStats::loadAverage15min();
}

/**
 * Méthode proxy : Effectue un test de débit descendant (nécessite speedtest-cli).
 * @return float La vitesse de téléchargement en Mbit/s arrondie à 2 décimales.
 */
function monitoring_ramUsed()
{
    $memoryStats = Nexus\Monitoring\SystemStats::memoryStats();
    if ($memoryStats) {
        return "{$memoryStats['ram']['used_mo']} Mo ({$memoryStats['ram']['used_percent']}%)";
    } else {
        return "";
    }
}

/**
 * Méthode proxy : Effectue un test de débit descendant (nécessite speedtest-cli).
 * @return float La vitesse de téléchargement en Mbit/s arrondie à 2 décimales.
 */
function monitoring_speedTest()
{
    return Nexus\Monitoring\SystemStats::speedTest();
}
