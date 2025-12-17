<?php

namespace Nexus\Energy\Electricity;

use Nexus\Energy\Electricity\Service\KwhReading\JeedomKwhReading;
use Nexus\Energy\Electricity\Consumption;
use Nexus\Energy\Electricity\ContractFactory;

/**
 * Facade simplifiant l'accès aux services de calcul d'énergie.
 *
 * Fournit des méthodes utilitaires statiques pour récupérer rapidement
 * des indicateurs (kWh / €) pour différentes périodes.
 * Implémente un singleton pour l'objet `Consumption`.
 */
/**
 * Façade d'accès simple au moteur de consommation.
 *
 * Fournit des méthodes statiques pratiques pour récupérer des indicateurs
 * (kWh / €) pour des périodes courantes (hier, mois, année glissante).
 *
 * Utilise un singleton pour instancier `Consumption` une seule fois.
 */
class EnergyFacade
{
    private static ?Consumption $instance = null;

    private function __construct()
    {
    }

    /**
        * Retourne l'instance unique du moteur de consommation.
        *
        * @return Consumption
     */
    /**
     * Retourne l'instance partagée de `Consumption`.
     *
     * @return Consumption
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

    /**
     * Consommation d'hier en kWh.
     */
    public static function kwhDay(): float
    {
        return (float) self::getEngine()->getYesterdaySummary()['totals']['kwh'];
    }

    /**
     * Consommation du mois en cours en kWh.
     */
    public static function kwhMonth(): float
    {
        return (float) self::getEngine()->getCurrentMonthSummary()['totals']['kwh'];
    }

    /**
     * Consommation annuelle glissante (12 derniers mois) en kWh.
     */
    public static function kwhYear(): float
    {
        return (float) self::getEngine()->getYearlyRollingSummary()['totals']['kwh'];
    }

    /**
     * Coût total d'hier en euros (TTC si les prix sont TTC).
     */
    public static function euroDay(): float
    {
        return (float) self::getEngine()->getYesterdaySummary()['totals']['cost'];
    }

    /**
     * Coût total du mois en cours en euros.
     */
    public static function euroMonth(): float
    {
        return (float) self::getEngine()->getCurrentMonthSummary()['totals']['cost'];
    }

    /**
     * Coût total de l'année glissante (12 derniers mois) en euros.
     */
    public static function euroYear(): float
    {
        return (float) self::getEngine()->getYearlyRollingSummary()['totals']['cost'];
    }
}
