<?php

require_once __DIR__ . '/../../../vendor/autoload.php';
require_once 'lib.php';

// Couleurs ANSI
$red    = "\033[31m";
$green  = "\033[32m";
$yellow = "\033[33m";
$blue   = "\033[34m";
$cyan   = "\033[36m";
$bold   = "\033[1m";
$reset  = "\033[0m";

echo "\n" . $bold . $blue . "--- GESTIONNAIRE D'ÉNERGIE NEXUS ---" . $reset . "\n";
echo $cyan . "1." . $reset . " Calculer la consommation (energyConsumption_calculate)\n";
echo $cyan . "2." . $reset . " Simuler la réécriture d'historique (Dry-Run)\n";
echo $cyan . "3." . $reset . " Quitter\n";
echo $bold . "Choix : " . $reset;

$choix = trim(fgets(STDIN));

switch ($choix) {
    case '1':
        energyConsumption_calculate();
        break;
    case '2':
        energyConsumption_rewriteHistoryDryRun();
        break;
    case '3':
        echo $red . "Sortie." . $reset . "\n";
        exit;

    default:
        echo $red . "Choix invalide." . $reset . "\n";
        break;
}

echo "\n";
