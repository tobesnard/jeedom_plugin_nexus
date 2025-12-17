<?php

require_once __DIR__ . "/../../vendor/autoload.php";

use Nexus\Energy\Electricity\Service\KwhReading\JeedomKwhReading;
use Nexus\Energy\Electricity\Consumption;
use Nexus\Energy\Electricity\ContractFactory;

/**
 * Helper interne pour initialiser le service Consumption
 * Utilise un pattern static pour éviter de recharger les contrats à chaque appel
 */
function getConsumptionService(): Consumption
{
    static $service = null;
    if ($service === null) {
        $contractsJsonFilePath = __DIR__ . '/../config/contrats.json';
        $contracts = ContractFactory::createFromConfigFile($contractsJsonFilePath);
        $kwhReadingService = new JeedomKwhReading();
        $service = new Consumption($kwhReadingService, $contracts);
    }
    return $service;
}

/** Méthode Proxy : Récupère la consommation en Kwh pour la journée d'hier **/
function energy_kwhDay(): float
{
    $summary = getConsumptionService()->getYesterdaySummary();
    return (float) $summary['totals']['kwh'];
}

/* Méthode Proxy : Récupère la consommation en KWH pour le mois en cours **/
function energy_kwhMonth(): float
{
    $summary = getConsumptionService()->getCurrentMonthSummary();
    return (float) $summary['totals']['kwh'];
}

/* Méthode Proxy : Récupère la consommation en Kwh pour l'année (glissante) en cours **/
function energy_kwhYear(): float
{
    $summary = getConsumptionService()->getYearlyRollingSummary();
    return (float) $summary['totals']['kwh'];
}

/** Méthode Proxy: Récupère le coût total en energy la journée d'hier **/
function energy_euroDay(): float
{
    $summary = getConsumptionService()->getYesterdaySummary();
    return (float) $summary['totals']['cost'];
}

/** Méthode Proxy : Récupère le coût total en energy pour le mois en cours **/
function energy_euroMonth(): float
{
    $summary = getConsumptionService()->getCurrentMonthSummary();
    return (float) $summary['totals']['cost'];
}

/** Méthode Proxy : Récupère le coût total en energy pour l'année en cours (glissant) **/
function energy_euroYear(): float
{
    $summary = getConsumptionService()->getYearlyRollingSummary();
    return (float) $summary['totals']['cost'];
}
