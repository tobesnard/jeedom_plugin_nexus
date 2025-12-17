<?php

namespace Nexus\Energy\Electricity;

use Nexus\Energy\Electricity\Service\KwhReading\JeedomKwhReading;
use Nexus\Energy\Electricity\Consumption;
use Nexus\Energy\Electricity\ContractFactory;

/**
 * Facade simplifiant l'accès aux services de calcul d'énergie.
 * Implémente le pattern Singleton pour optimiser les ressources.
 */
class EnergyFacade
{
    private static ?Consumption $instance = null;

    private function __construct()
    {
    }

    /**
     * Retourne l'instance unique du moteur de consommation
     */
    public static function getEngine(): Consumption
    {
        if (self::$instance === null) {
            $configPath = __DIR__ . '/../config/contrats.json';

            if (!file_exists($configPath)) {
                throw new \RuntimeException("Fichier de configuration absent : $configPath");
            }

            $contracts = ContractFactory::createFromConfigFile($configPath);
            $readingService = new JeedomKwhReading();
            self::$instance = new Consumption($readingService, $contracts);
        }
        return self::$instance;
    }

    public static function kwhDay(): float
    {
        return (float) self::getEngine()->getYesterdaySummary()['totals']['kwh'];
    }

    public static function kwhMonth(): float
    {
        return (float) self::getEngine()->getCurrentMonthSummary()['totals']['kwh'];
    }

    public static function kwhYear(): float
    {
        return (float) self::getEngine()->getYearlyRollingSummary()['totals']['kwh'];
    }

    public static function euroDay(): float
    {
        return (float) self::getEngine()->getYesterdaySummary()['totals']['cost'];
    }

    public static function euroMonth(): float
    {
        return (float) self::getEngine()->getCurrentMonthSummary()['totals']['cost'];
    }

    public static function euroYear(): float
    {
        return (float) self::getEngine()->getYearlyRollingSummary()['totals']['cost'];
    }
}
