<?php

require_once __DIR__ . "/../../vendor/autoload.php";

use Nexus\Energy\Electricity\EnergyFacade;

/**
 * Proxy : consommation d'hier (kWh).
 *
 * Utilisé principalement comme helper global par des scripts ou templates
 * externes (par exemple des widgets Jeedom). Ces fonctions sont fines
 * et délèguent tout le travail à `EnergyFacade`.
 *
 * @return float kWh
 */
function energy_kwhDay(): float
{
    return EnergyFacade::kwhDay();
}

/**
 * Proxy : consommation du mois en cours (kWh).
 *
 * @return float kWh
 */
function energy_kwhMonth(): float
{
    return EnergyFacade::kwhMonth();
}

/**
 * Proxy : consommation année glissante (kWh).
 *
 * @return float kWh
 */
function energy_kwhYear(): float
{
    return EnergyFacade::kwhYear();
}

/**
 * Proxy : coût total d'hier (euros).
 *
 * @return float euros
 */
function energy_euroDay(): float
{
    return EnergyFacade::euroDay();
}

/**
 * Proxy : coût total du mois en cours (euros).
 *
 * @return float euros
 */
function energy_euroMonth(): float
{
    return EnergyFacade::euroMonth();
}

/**
 * Proxy : coût total année glissante (euros).
 *
 * @return float euros
 */
function energy_euroYear(): float
{
    return EnergyFacade::euroYear();
}
