<?php

require_once __DIR__ . "/../../../vendor/autoload.php";

use Nexus\Energy\Electricity\EnergyFacade;
use Nexus\Energy\Electricity\Util\BillingRenderer;

/**
 * Configure et lance une simulation de réécriture d'historique (Dry-Run).
 * * Cette fonction interagit avec l'utilisateur via la console pour définir
 * la période de simulation. Elle ne modifie aucune donnée en base de données.
 *
 * @return void
 */
function energyConsumption_rewriteHistoryDryRun(): void
{
    // Définition des bornes temporelles par défaut (J-10 à Hier)
    $defaultStart = date('Y-m-d', strtotime('-10 days'));
    $defaultEnd   = date('Y-m-d', strtotime('yesterday'));

    echo "\n\033[33m╔══════════════════════════════════════════════════════╗\033[0m";
    echo "\n\033[33m║       CONFIGURATION DE LA SIMULATION (DRY-RUN)       ║\033[0m";
    echo "\n\033[33m╚══════════════════════════════════════════════════════╝\033[0m\n";

    // Saisie utilisateur pour la date de début
    echo "Date de début (YYYY-MM-DD) [\033[32m$defaultStart\033[0m] : ";
    $inputStart = trim(fgets(STDIN));
    $start = $inputStart ?: $defaultStart;

    // Saisie utilisateur pour la date de fin
    echo "Date de fin   (YYYY-MM-DD) [\033[32m$defaultEnd\033[0m] : ";
    $inputEnd = trim(fgets(STDIN));
    $end = $inputEnd ?: $defaultEnd;

    echo "\n\033[36m➜ Lancement de la simulation du $start au $end...\033[0m\n";

    // Exécution via la Façade
    EnergyFacade::dryRun($start, $end);
}

/**
 * Calcule et affiche les différents rapports de consommation électrique.
 * * Génère 4 rapports distincts :
 * 1. Période personnalisée (J-10 à Hier)
 * 2. Rapport journalier (Hier)
 * 3. Rapport mensuel (Mois calendaire en cours)
 * 4. Rapport annuel (12 mois glissants)
 *
 * @return void
 */
function energyConsumption_calculate(): void
{
    try {
        // Récupération du moteur de calcul via la Façade (Singleton)
        $engine = EnergyFacade::getEngine();

        echo "\n\033[34m╔══════════════════════════════════════════════════════╗\033[0m";
        echo "\n\033[34m║           RAPPORTS DE CONSOMMATION ÉNERGIE           ║\033[0m";
        echo "\n\033[34m╚══════════════════════════════════════════════════════╝\033[0m\n";

        // --- 1. RÉSUMÉ SUR PÉRIODE (Défaut : 10 derniers jours) ---
        $end   = new \DateTimeImmutable('yesterday');
        $start = $end->modify('-10 days');

        $summary = $engine->getBillingSummary($start, $end);
        $title   = sprintf("PÉRIODE : %s AU %s", $start->format('Y-m-d'), $end->format('Y-m-d'));

        BillingRenderer::renderConsoleTable($summary, $title);

        // --- 2. CONSOMMATION D'HIER ---
        $yesterday = $engine->getYesterdaySummary();
        BillingRenderer::renderConsoleTable($yesterday, "CONSOMMATION D'HIER");

        // --- 3. CONSOMMATION DU MOIS EN COURS ---
        $month = $engine->getCurrentMonthSummary();
        $title = sprintf("MOIS EN COURS (%s AU %s)", $month['period']['start'], $month['period']['end']);

        BillingRenderer::renderConsoleTable($month, $title);

        // --- 4. CONSOMMATION ANNUELLE (GLISSANTE) ---
        $year  = $engine->getYearlyRollingSummary();
        $title = sprintf("ANNÉE GLISSANTE (%s AU %s)", $year['period']['start'], $year['period']['end']);

        BillingRenderer::renderConsoleTable($year, $title);

    } catch (\Exception $e) {
        echo "\n\033[31m[ERREUR FATALE] " . $e->getMessage() . "\033[0m\n";
        exit(1);
    }
}
