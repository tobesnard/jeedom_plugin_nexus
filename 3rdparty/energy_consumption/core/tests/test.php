<?php

require_once __DIR__ . "/../../vendor/autoload.php";

use Nexus\Energy\Electricity\Service\KwhReading\JeedomKwhReading;
use Nexus\Energy\Electricity\Consumption;
use Nexus\Energy\Electricity\ContractFactory;
use Nexus\Energy\Electricity\Util\BillingRenderer;

$contractsJsonFilePath = __DIR__ . '/../config/contrats_fictif.json';

try {
    // --- Chargement des contrats ---
    $contracts = ContractFactory::createFromConfigFile($contractsJsonFilePath);

    // --- Initialisation des services ---
    $kwhReadingService = new JeedomKwhReading();
    $consumption = new Consumption($kwhReadingService, $contracts);

    // --- Définition de la période ---
    $start = new \DateTimeImmutable('2025-12-01');
    $end   = new \DateTimeImmutable('2025-12-17');

    // --- Calcul ---
    $summary = $consumption->getBillingSummary($start, $end);

    // --- Affichage ---
    BillingRenderer::renderConsoleTable($summary);

    //
} catch (\Exception $e) {
    echo "ERREUR : " . $e->getMessage() . "\n";
    exit(1);
}
