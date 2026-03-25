<?php

require_once __DIR__ . "/../../../vendor/autoload.php";

use Nexus\Energy\Electricity\Service\KwhReading\JeedomKwhReading;
use Nexus\Energy\Electricity\Consumption;
use Nexus\Energy\Electricity\ContractFactory;
use Nexus\Energy\Electricity\Util\BillingRenderer;

// $contractsJsonFilePath = __DIR__ . '/../config/contrats_fictif.json';
$contractsJsonFilePath = __DIR__ . '/../core/config/contrats.json';

try {
    // --- Chargement des contrats et services ---
    $contracts = ContractFactory::createFromConfigFile($contractsJsonFilePath);
    $kwhReadingService = new JeedomKwhReading();
    $consumption = new Consumption($kwhReadingService, $contracts);

    // --- 0. Rélevé de consommation ---
    $start = new \DateTimeImmutable('2025-12-01');
    $end   = new \DateTimeImmutable('2025-12-17');
    $summary = $consumption->getBillingSummary($start, $end);
    BillingRenderer::renderConsoleTable($summary, "CONSOMMATION SUR PERIODE");

    // --- 1. Consommation d'hier ---
    $yesterdaySummary = $consumption->getYesterdaySummary();
    BillingRenderer::renderConsoleTable($yesterdaySummary, "CONSOMMATION D'HIER");

    // --- 2. Consommation du mois en cours ---
    $monthSummary = $consumption->getCurrentMonthSummary();
    $titleMonth = sprintf(
        "CONSOMMATION DU MOIS EN COURS (%s au %s)",
        $monthSummary['period']['start'],
        $monthSummary['period']['end']
    );
    BillingRenderer::renderConsoleTable($monthSummary, $titleMonth);

    // --- 3. Consommation de l'année (Glissante) ---
    $yearSummary = $consumption->getYearlyRollingSummary();
    $titleYear = sprintf(
        "RÉSUMÉ ANNEE GLISSANTE (%s au %s)",
        $yearSummary['period']['start'],
        $yearSummary['period']['end']
    );
    BillingRenderer::renderConsoleTable($yearSummary, $titleYear);

    //
} catch (\Exception $e) {
    echo "ERREUR : " . $e->getMessage() . "\n";
    exit(1);
}
