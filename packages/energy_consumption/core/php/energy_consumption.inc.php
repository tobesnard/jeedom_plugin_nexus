<?php

require_once __DIR__ . "/../../../../vendor/autoload.php";

use Nexus\Energy\Electricity\EnergyFacade;
use Nexus\Utils\Helpers;

/**
 * Proxy : consommation d'hier (kWh).
 *
 * @return float kWh
 */
function energy_kwhDay(): float
{
    return Helpers::execute(function () {
        return (float) EnergyFacade::kwhDay();
    }, 0.0);
}

/**
 * Proxy : consommation du mois en cours (kWh).
 *
 * @return float kWh
 */
function energy_kwhMonth(): float
{
    return Helpers::execute(function () {
        return (float) EnergyFacade::kwhMonth();
    }, 0.0);
}

/**
 * Proxy : consommation année glissante (kWh).
 *
 * @return float kWh
 */
function energy_kwhYear(): float
{
    return Helpers::execute(function () {
        return (float) EnergyFacade::kwhYear();
    }, 0.0);
}

/**
 * Proxy : coût total d'hier (euros).
 *
 * @return float euros
 */
function energy_euroDay(): float
{
    return Helpers::execute(function () {
        return (float) EnergyFacade::euroDay();
    }, 0.0);
}

/**
 * Proxy : coût total du mois en cours (euros).
 *
 * @return float euros
 */
function energy_euroMonth(): float
{
    return Helpers::execute(function () {
        return (float) EnergyFacade::euroMonth();
    }, 0.0);
}

/**
 * Proxy : coût total année glissante (euros).
 *
 * @return float euros
 */
function energy_euroYear(): float
{
    return Helpers::execute(function () {
        return (float) EnergyFacade::euroYear();
    }, 0.0);
}

/**
 * Proxy : réécriture complète de l'historique (kWh et Euros).
 * Déclenche le recalcul massif depuis le premier relevé disponible jusqu'à hier.
 *
 * @return void
 */
function energy_rewriteHistory(): void
{
    Helpers::execute(function () {
        EnergyFacade::rewriteHistory();
    });
}
