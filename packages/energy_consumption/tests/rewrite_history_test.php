<?php

require_once __DIR__ . "/../../../vendor/autoload.php";

use Nexus\Energy\Electricity\Service\KwhReading\JeedomHistoryManager;
use Nexus\Energy\Electricity\Service\KwhReading\JeedomKwhReading;
use Nexus\Energy\Electricity\Consumption;
use Nexus\Energy\Electricity\ContractFactory;

$contractsJsonFilePath = __DIR__ . '/../core/config/contrats.json';
$contracts = ContractFactory::createFromConfigFile($contractsJsonFilePath);
$kwhReadingService = new JeedomKwhReading();
$consumption = new Consumption($kwhReadingService, $contracts);


// Initialisation du JeedomHistoryManager en mode dry-run (2ème argument à true)

// Initialisation du JeedomHistoryManager en mode dry-run (2ème argument à true)
$historyManager = new JeedomHistoryManager($consumption, true);

// Exemple de période à tester (à adapter selon vos données)
$startDate = date('Y-m-d', strtotime('-7 days'));
$endDate = date('Y-m-d');

// Appel de la méthode de réécriture d'historique en dry-run
try {
    $historyManager->rewriteAll($startDate, $endDate); // dryRun activé dans le constructeur
    echo "\n--- Dry run terminé ---\n";
} catch (Exception $e) {
    echo "Erreur lors du dry run : " . $e->getMessage() . "\n";
}