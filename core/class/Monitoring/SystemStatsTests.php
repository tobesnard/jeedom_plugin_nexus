<?php

namespace Nexus\Monitoring;

require_once "SystemStats.php";


// --- DÉBUT DU SCRIPT DE TEST ---

// Définition des codes ANSI pour la couleur
// GRN: Vert Vif (Valeurs)
// RST: Reset (Réinitialisation)
define('GRN', "\033[1;32m");
define('RST', "\033[0m");

echo "================================================\n";
echo "       Test des Statistiques Système \n";
echo "================================================\n";

// 1. Instanciation de la classe
try {
    $stats = new SystemStats();
} catch (Error $e) {
    echo "Erreur Fatale : La classe SystemStats n'est pas définie ou a une erreur de syntaxe.\n";
    echo "Assurez-vous d'avoir inclus le code complet de la classe SystemStats.\n";
    exit(1);
}

// 2. Appel des méthodes et affichage des résultats

$uptime = $stats->upTime();
echo "Uptime: **" . GRN . "$uptime" . RST . "**\n";

$hddStats = $stats->hddStats();
echo "Stats Disque: **" . GRN . "$hddStats" . RST . "**\n";

$distribution = $stats->distribution();
echo "Distribution: **" . GRN . "$distribution" . RST . "**\n";

$cpu = $stats->cpu();
echo "CPU (Cœurs - Fréquence): **" . GRN . "$cpu" . RST . "**\n";
$cpuTemp = $stats->cpuTemperature();
echo "Température CPU: **" . GRN . "$cpuTemp" . RST . "**\n";

$load1 = $stats->loadAverage1min();
$load5 = $stats->loadAverage5min();
$load15 = $stats->loadAverage15min();
echo "Charge (1min): **" . GRN . "$load1" . RST . "**\n";
echo "Charge (5min): **" . GRN . "$load5" . RST . "**\n";
echo "Charge (15min): **" . GRN . "$load15" . RST . "**\n";

$speedTest = $stats->speedTest();
echo "Débit descendant: **" . GRN . "$speedTest Mbps" . RST . "**\n";

$memoryStats = $stats->memoryStats();
if ($memoryStats) {
    echo "RAM Totale: **" . GRN . "{$memoryStats['ram']['total_mo']} Mo" . RST . "**\n";
    // Mettre la valeur et le pourcentage en couleur
    $ramUsed = "{$memoryStats['ram']['used_mo']} Mo ({$memoryStats['ram']['used_percent']}%)";
    echo "RAM Utilisée: **" . GRN . $ramUsed . RST . "**\n";

    $swapUsed = "{$memoryStats['swap']['used_mo']} Mo ({$memoryStats['swap']['used_percent']}%)";
    echo "Swap Utilisé: **" . GRN . $swapUsed . RST . "**\n";
} else {
    echo "Erreur: Impossible de lire les statistiques mémoire.\n";
}

echo "\n================================================\n";
echo "               ✅  Test Terminé\n";
echo "================================================\n";
