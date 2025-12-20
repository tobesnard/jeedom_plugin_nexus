<?php

/**
 * Script de validation des Helpers Nexus
 * Lancé en CLI : php test_utils.php
 */

require_once __DIR__ . "/../php/utils.inc.php";

// Configuration des tests (Input => Expected)
$testCases = [
    'utils_formatHour' => [
        ['755', '07:55'],
        [1230, '12:30'],
        [5,    '00:05'],
    ],
    'escapeChar' => [
        ['Test (valeur)', 'Test \(valeur\)'],
        ['Hello World',   'Hello World'],
    ],
    'uniform' => [
        [' Été À NOËL ', 'ete a noel'],
    ],
    'extract_notification_value' => [
        ['{"value":"Alerte"}', 'Alerte'],
        ['Message brut',       'Message brut'],
    ],
];

/**
 * Fonction de rendu des tests
 */
function runTest($name, $result, $expected)
{
    $status = ($result === $expected) ? "\033[32m[OK]\033[0m" : "\033[31m[FAIL]\033[0m";
    echo sprintf("%s %s -> Got: '%s' | Exp: '%s'\n", $status, str_pad($name, 20), $result, $expected);
}

echo "--- DÉBUT DES TESTS UNITAIRES ---\n";

// 1. Tests avec jeux de données
foreach ($testCases as $func => $scenarios) {
    if (!function_exists($func)) {
        echo "\033[33m[SKIP]\033[0m Fonction $func non définie.\n";
        continue;
    }
    foreach ($scenarios as $case) {
        runTest($func, $func($case[0]), $case[1]);
    }
}

// 2. Test spécifique pour les fonctions Jeedom (Mock ou variables réelles)
// Note : Nécessite un environnement Jeedom chargé pour fonctionner réellement
$cmdId = "#123#";
$start = "2025-01-01 00:00:00";
$end   = "2025-01-01 23:59:59";

echo "\n--- TESTS DÉPENDANCES JEEDOM ---\n";
try {
    echo "Min Between: " . min_between($cmdId, $start, $end) . "\n";
    echo "Max Between: " . max_between($cmdId, $start, $end) . "\n";
} catch (\Throwable $e) {
    echo "\033[33m[INFO]\033[0m Fonctions Jeedom ignorées (Hors environnement Core).\n";
}

// 3. Test de la fonction NOP
echo "Test NOP: ";
var_dump(nop());

echo "--- FIN DES TESTS ---\n";



// $title = "test ?";
// $answers = 'Oui;Non';
// $timeout = 5;
// $response = utils_askTelegram($title, $answers, $timeout); // Action Bloquante
