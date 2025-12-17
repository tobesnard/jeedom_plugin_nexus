<?php

require_once __DIR__ . "/../../vendor/autoload.php";

use Nexus\Energy\Electricity\Service\KwhReading\JeedomKwhReading;
use Nexus\Energy\Electricity\Consumption;
use Nexus\Energy\Electricity\Contract;
use Nexus\Energy\Electricity\ContractFactory;

// --- Chargement des contrats depuis le JSON ---
$contractsJsonFilePath = __DIR__ . '/../config/contrats_fictif.json';
$contracts = ContractFactory::createFromConfigFile($contractsJsonFilePath);

// --- Initialisation des services ---
$kwhReadingService = new JeedomKwhReading();
$consumption = new Consumption($kwhReadingService, $contracts);

// Période de test (ex: Décembre 2025)
$start = new \DateTimeImmutable('2025-12-01');
$end   = new \DateTimeImmutable('2025-12-17');

$summary = $consumption->getBillingSummary($start, $end);

// --- Affichage ---
echo "\n" . str_repeat("=", 85) . "\n";
printf("%-12s | %-20s | %-10s | %-10s | %-10s\n", "Date", "Contrat", "kWh", "Prix Unit.", "Coût Jour");
echo str_repeat("-", 85) . "\n";

foreach ($summary['daily_details'] as $row) {
    printf(
        "%-12s | %-20s | %-10.2f | %-10.4f | %-10.2f €\n",
        $row['date'],
        substr($row['contract'], 0, 20),
        $row['kwh'],
        $row['unit_price'] ?? 0,
        $row['daily_cost']
    );
}

echo str_repeat("-", 85) . "\n";
printf("%-12s   %-20s | %-10.2f | %-10s | %-10.2f €\n", "TOTAL", "", $summary['totals']['kwh'], "", $summary['totals']['cost']);
echo str_repeat("=", 85) . "\n\n";
