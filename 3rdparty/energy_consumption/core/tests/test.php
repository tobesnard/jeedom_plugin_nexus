<?php

require_once __DIR__ . "/../../vendor/autoload.php";

use Nexus\Energy\Electricity\Service\KwhReading\JeedomKwhReading;
use Nexus\Energy\Electricity\Consumption;
use Nexus\Energy\Electricity\Contract;

// 1. Définition des contrats (simulation de changement en milieu de mois)
$contracts = [
    new Contract(
        new \DateTimeImmutable('2024-01-01'),
        0.1999,
        14.37,
        'BASE',
        new \DateTimeImmutable('2025-12-14'), // Fin de contrat le 14
        null,
        null,
        'Ancienne Offre'
    ),
    new Contract(
        new \DateTimeImmutable('2025-12-15'), // Nouveau contrat le 15
        0.2276,
        15.10,
        'BASE',
        null,
        null,
        null,
        'Offre Renouvelable'
    )
];

$kwhReadingService = new JeedomKwhReading();
$consumption = new Consumption($kwhReadingService, $contracts);

// 2. Définition de la période (ex: Décembre 2025)
$start = new \DateTimeImmutable('2025-12-01');
$end   = new \DateTimeImmutable('2025-12-17');

$summary = $consumption->getBillingSummary($start, $end);

// 3. Affichage type Tableau
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
