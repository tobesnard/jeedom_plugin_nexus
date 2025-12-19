<?php

require_once __DIR__ . "/../core/php/energy_consumption.inc.php";

use Nexus\Energy\Electricity\Util\BillingRenderer;

try {
    // 1. "HIER"
    $kwhDay = energy_kwhDay();
    $euroDay = energy_euroDay();
    echo "  => Proxy energy_kwhDay  : " . \colorValue($kwhDay, "kWh") . "\n";
    echo "  => Proxy energy_euroDay : " . \colorValue($euroDay, "€") . "\n\n";


    // 2. "MOIS EN COURS"
    $kwhMonth = energy_kwhMonth();
    $euroMonth = energy_euroMonth();
    echo "  => Proxy energy_kwhMonth  : " . \colorValue($kwhMonth, "kWh") . "\n";
    echo "  => Proxy energy_euroMonth : " . \colorValue($euroMonth, "€") . "\n\n";


    // 3. "ANNÉE GLISSANTE"
    $kwhYear = energy_kwhYear();
    $euroYear = energy_euroYear();
    echo "  => Proxy energy_kwhYear  : " . \colorValue($kwhYear, "kWh") . "\n";
    echo "  => Proxy energy_euroYear : " . \colorValue($euroYear, "€") . "\n\n";

} catch (\Exception $e) {
    echo "\033[1;31m[ERREUR] " . $e->getMessage() . "\033[0m\n";
    exit(1);
}

/**
 * Helper de formatage pour le test
 */
function colorValue($val, $unit)
{
    $color = ($unit === 'kWh') ? "\033[36m" : "\033[32m";
    return $color . number_format($val, 2, '.', '') . " $unit\033[0m";
}
