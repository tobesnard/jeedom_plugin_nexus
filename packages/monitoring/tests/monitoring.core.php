<?php

namespace Nexus\Monitoring;

require_once __DIR__ . "/../core/class/SystemStats.php";

// --- DÉBUT DU SCRIPT DE TEST ---

// Définition des codes ANSI pour la couleur
// GRN: Vert Vif (Valeurs)
// RST: Reset (Réinitialisation)
define('GRN', "\033[1;32m");
define('RST', "\033[0m");

echo "================================================\n";
echo "           Test des Statistiques Système \n";
echo "================================================\n";

// 1. Instanciation de la classe (SUPPRIMÉE - Les fonctions sont statiques)
try {
    // Vérification simple que la classe existe et est chargée
    if (!class_exists('Nexus\Monitoring\SystemStats')) {
        throw new \Error("La classe SystemStats n'est pas définie.");
    }
} catch (\Error $e) {
    echo "Erreur Fatale : La classe SystemStats n'est pas définie ou a une erreur de syntaxe.\n";
    echo "Message: " . $e->getMessage() . "\n";
    echo "Assurez-vous d'avoir inclus le code complet de la classe SystemStats avec les méthodes statiques.\n";
    exit(1);
}

// 2. Appel des méthodes et affichage des résultats (Appel statique via SystemStats::méthode())

$uptime = SystemStats::upTime();
echo "Uptime: **" . GRN . "$uptime" . RST . "**\n";

$hddStats = SystemStats::hddStats();
echo "Stats Disque: **" . GRN . "$hddStats" . RST . "**\n";

$distribution = SystemStats::distribution();
echo "Distribution: **" . GRN . "$distribution" . RST . "**\n";

$cpu = SystemStats::cpu();
echo "CPU (Cœurs - Fréquence): **" . GRN . "$cpu" . RST . "**\n";
$cpuTemp = SystemStats::cpuTemperature();
echo "Température CPU: **" . GRN . "$cpuTemp" . RST . "**\n";

$load1 = SystemStats::loadAverage1min();
$load5 = SystemStats::loadAverage5min();
$load15 = SystemStats::loadAverage15min();
echo "Charge (1min): **" . GRN . "$load1" . RST . "**\n";
echo "Charge (5min): **" . GRN . "$load5" . RST . "**\n";
echo "Charge (15min): **" . GRN . "$load15" . RST . "**\n";

$speedTest = SystemStats::speedTest();
echo "Débit descendant: **" . GRN . "$speedTest Mbps" . RST . "**\n";

$memoryStats = SystemStats::memoryStats();
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
echo "              ✅ Test Terminé\n";
echo "================================================\n";
