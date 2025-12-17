<?php

require_once __DIR__ . "/../../vendor/autoload.php";

use Nexus\Energy\Electricity\EnergyFacade;

/** Méthode Proxy : Récupère la consommation en kWh pour la journée d'hier **/
function energy_kwhDay(): float
{
    return EnergyFacade::kwhDay();
}

/** Méthode Proxy : Récupère la consommation en kWh pour le mois en cours **/
function energy_kwhMonth(): float
{
    return EnergyFacade::kwhMonth();
}

/** Méthode Proxy : Récupère la consommation en kWh pour l'année (glissante) en cours **/
function energy_kwhYear(): float
{
    return EnergyFacade::kwhYear();
}

/** Méthode Proxy : Récupère le coût total en euros pour la journée d'hier **/
function energy_euroDay(): float
{
    return EnergyFacade::euroDay();
}

/** Méthode Proxy : Récupère le coût total en euros pour le mois en cours **/
function energy_euroMonth(): float
{
    return EnergyFacade::euroMonth();
}

/** Méthode Proxy : Récupère le coût total en euros pour l'année en cours (glissante) **/
function energy_euroYear(): float
{
    return EnergyFacade::euroYear();
}
