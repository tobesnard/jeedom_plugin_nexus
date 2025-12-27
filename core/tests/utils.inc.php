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
    'utils_escapeChar' => [
        ['Test (valeur)', 'Test \(valeur\)'],
        ['Hello World',   'Hello World'],
    ],
    'utils_uniform' => [
        [' Été À NOËL ', 'ete a noel'],
    ],
    'utils_extractNotificationValue' => [
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




echo "--- FIN DES TESTS ---\n";



// $title = "test ?";
// $answers = 'Oui;Non';
// $timeout = 5;
// $response = utils_askTelegram($title, $answers, $timeout); // Action Bloquante
