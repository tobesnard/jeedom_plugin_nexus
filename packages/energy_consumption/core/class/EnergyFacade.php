<?php

namespace Nexus\Energy\Electricity;

use Nexus\Energy\Electricity\Service\KwhReading\JeedomKwhReading;
use Nexus\Utils\Helpers;

/**
 * Façade d'accès simplifiée pour le moteur de consommation électrique.
 * * Centralise l'accès aux calculs de coûts et de consommation (kWh) tout en
 * gérant l'instanciation automatique des services requis via la configuration.
 */
class EnergyFacade
{
    /** @var Consumption|null Instance unique du moteur de calcul (Singleton) */
    private static ?Consumption $instance = null;

    /**
     * Constructeur privé pour empêcher l'instanciation directe.
     */
    private function __construct() {}

    /**
     * Initialise et retourne l'instance partagée du moteur de consommation.
     * Charge automatiquement les contrats et le service de lecture Jeedom.
     * * @return Consumption
     * @throws \RuntimeException Si la configuration est manquante
     */
    public static function getEngine(): Consumption
    {
        if (self::$instance === null) {
            // Utilisation du manager pour identifier l'ID source défini dans config.json
            $manager = new JeedomHistoryManager(null, true);
            $contractsPath = __DIR__ . '/../config/contrats.json';

            if (!file_exists($contractsPath)) {
                throw new \RuntimeException("Fichier de configuration des contrats absent : $contractsPath");
            }

            $contracts = ContractFactory::createFromConfigFile($contractsPath);
            $readingService = new JeedomKwhReading($manager->getSourceCmdId());

            self::$instance = new Consumption($readingService, $contracts);
        }
        return self::$instance;
    }

    /**
     * Lance une simulation de réécriture d'historique (sans modification DB).
     * Affiche les résultats directement dans la console.
     * * @param string $start Date de début (YYYY-MM-DD ou 'yesterday')
     * @param string $end   Date de fin (YYYY-MM-DD ou 'yesterday')
     * @return void
     */
    public static function dryRun(string $start = 'yesterday', string $end = 'yesterday'): void
    {
        try {
            $manager = new JeedomHistoryManager(null, true);
            $manager->rewriteAll($start, $end);
        } catch (\Exception $e) {
            echo "\033[31m[ERREUR DRY-RUN] " . $e->getMessage() . "\033[0m\n";
        }
    }

    /**
     * Exécute une réécriture réelle des données historiques en base de données.
     * La période est calculée automatiquement de la première donnée source trouvée à hier.
     * * @return void
     */
    public static function rewriteHistory(): void
    {
        try {
            $manager = new JeedomHistoryManager(null, false);
            $readingService = new JeedomKwhReading($manager->getSourceCmdId());

            // Détermination de la borne de départ automatique
            $firstDate = $readingService->getFirstReadingDate();
            $start = $firstDate ? $firstDate->format('Y-m-d') : date('Y-m-d', strtotime('-1 year'));
            $end = date('Y-m-d', strtotime('yesterday'));

            Helpers::log("Lancement réécriture massive (Source: {$manager->getSourceCmdId()}) de $start à $end", 'info');

            $manager->rewriteAll($start, $end);

            Helpers::log("Réécriture massive terminée avec succès", 'info');
        } catch (\Exception $e) {
            Helpers::log("Erreur lors de la réécriture massive : " . $e->getMessage(), 'error');
        }
    }

    /**
     * @return float Consommation totale d'hier en kWh
     */
    public static function kwhDay(): float
    {
        return (float) self::getEngine()->getYesterdaySummary()['totals']['kwh'];
    }

    /**
     * @return float Consommation du mois calendaire en cours en kWh
     */
    public static function kwhMonth(): float
    {
        return (float) self::getEngine()->getCurrentMonthSummary()['totals']['kwh'];
    }

    /**
     * @return float Consommation sur 365 jours glissants en kWh
     */
    public static function kwhYear(): float
    {
        return (float) self::getEngine()->getYearlyRollingSummary()['totals']['kwh'];
    }

    /**
     * @return float Coût total d'hier en Euros
     */
    public static function euroDay(): float
    {
        return (float) self::getEngine()->getYesterdaySummary()['totals']['cost'];
    }

    /**
     * @return float Coût total du mois calendaire en cours en Euros
     */
    public static function euroMonth(): float
    {
        return (float) self::getEngine()->getCurrentMonthSummary()['totals']['cost'];
    }

    /**
     * @return float Coût sur 365 jours glissants en Euros
     */
    public static function euroYear(): float
    {
        return (float) self::getEngine()->getYearlyRollingSummary()['totals']['cost'];
    }
}
