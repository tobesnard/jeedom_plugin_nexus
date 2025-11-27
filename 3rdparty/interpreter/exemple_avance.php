<?php

/**
 * Exemple d'utilisation avancée de l'Interpréteur Jeedom
 * 
 * Ce fichier montre comment créer et utiliser des expressions
 * plus complexes avec le système refactorisé.
 */

require_once __DIR__ . '/vendor/autoload.php';

use Interpreter\Context\RuleContext;
use Interpreter\Expression\Terminal\LiteralExpression;

echo "\n";
echo "===========================================\n";
echo "  Exemple d'Usage Avancé - Interpréteur\n";
echo "===========================================\n\n";

// Création du contexte avec différentes variables
$context = new RuleContext(true);

// Simulation d'un environnement domotique
$context->set('temperature_salon', 23.5);
$context->set('temperature_chambre', 19.2);
$context->set('mode_chauffage', 'auto');
$context->set('presence', true);
$context->set('heure_actuelle', 14);
$context->set('jour_semaine', 'mardi');

echo "1. Environnement domotique simulé\n";
echo "-----------------------------------\n";
foreach ($context->getAllData() as $key => $value) {
    echo "  {$key}: " . (is_bool($value) ? ($value ? 'true' : 'false') : $value) . "\n";
}

// Création d'expressions pour des règles métier
echo "\n2. Expressions pour règles métier\n";
echo "----------------------------------\n";

// Expression littérale pour seuil de température
$seuilConfort = new LiteralExpression(21.0);
echo "Seuil de confort: {$seuilConfort} = " . $seuilConfort->interpret($context) . "\n";

// Expression booléenne pour mode économie
$modeEconomie = LiteralExpression::boolean(false);
echo "Mode économie: {$modeEconomie} = " . ($modeEconomie->interpret($context) ? 'true' : 'false') . "\n";

// Expression numérique pour plage horaire
$heureDebut = LiteralExpression::number(8);
$heureFin = LiteralExpression::number(22);
echo "Plage horaire: {$heureDebut} à {$heureFin}\n";

// Tests de validation d'expressions
echo "\n3. Tests de validation et typage\n";
echo "--------------------------------\n";

$expressions = [
    'Température' => new LiteralExpression(22.5),
    'Booléen' => new LiteralExpression(true),
    'Chaîne' => new LiteralExpression('capteur_principal'),
    'Entier' => new LiteralExpression(100),
    'Null' => new LiteralExpression(null)
];

foreach ($expressions as $nom => $expr) {
    echo "  {$nom}:\n";
    echo "    - Type: " . $expr->getValueType() . "\n";
    echo "    - Numérique: " . ($expr->isNumeric() ? 'Oui' : 'Non') . "\n";
    echo "    - Booléen: " . ($expr->isBoolean() ? 'Oui' : 'Non') . "\n";
    echo "    - String: " . ($expr->isString() ? 'Oui' : 'Non') . "\n";
    echo "    - Null: " . ($expr->isNull() ? 'Oui' : 'Non') . "\n";
    echo "    - Valide: " . ($expr->validate() ? 'Oui' : 'Non') . "\n\n";
}

// Simulation de déclenchement d'événements basés sur les règles
echo "4. Déclenchement d'événements basés sur les règles\n";
echo "----------------------------------------------------\n";

// Règle: Si température < seuil ET présence = true
$tempSalon = $context->get('temperature_salon');
$seuilTemp = $seuilConfort->interpret($context);
$presence = $context->get('presence');

if ($tempSalon > $seuilTemp && $presence) {
    $context->triggerEvent('augmenter_chauffage', [
        'temperature_actuelle' => $tempSalon,
        'seuil' => $seuilTemp,
        'piece' => 'salon'
    ]);
}

// Règle: Si mode auto ET heure dans plage
$mode = $context->get('mode_chauffage');
$heure = $context->get('heure_actuelle');
$debut = $heureDebut->interpret($context);
$fin = $heureFin->interpret($context);

if ($mode === 'auto' && $heure >= $debut && $heure <= $fin) {
    $context->triggerEvent('mode_auto_actif', [
        'heure' => $heure,
        'plage' => "{$debut}h-{$fin}h"
    ]);
}

// Gestion d'expressions avec erreurs
echo "\n5. Gestion des erreurs et validation\n";
echo "-------------------------------------\n";

try {
    // Tentative de création d'une expression avec type invalide
    $invalide = new LiteralExpression([1, 2, 3]); // Devrait échouer
} catch (Exception $e) {
    echo "Erreur attendue: " . $e->getMessage() . "\n";
}

try {
    // Tentative d'utilisation de la méthode number() avec une chaîne
    $invalideNumber = LiteralExpression::number('pas_un_nombre');
} catch (Exception $e) {
    echo "Erreur attendue: " . $e->getMessage() . "\n";
}

// Parsing de valeurs complexes
echo "\n6. Parsing de valeurs complexes\n";
echo "--------------------------------\n";

$valeursComplexes = [
    '"true"' => 'Booléen dans guillemets',
    "'false'" => 'Booléen dans apostrophes',
    '42.0' => 'Flottant sans décimales',
    '  123  ' => 'Nombre avec espaces',
    '"null"' => 'Null sous forme de chaîne',
    'null' => 'Null réel'
];

foreach ($valeursComplexes as $valeur => $description) {
    $expr = new LiteralExpression($valeur);
    echo "  {$description}: '{$valeur}' -> " . var_export($expr->getValue(), true) . " (" . $expr->getValueType() . ")\n";
}

// Performance et optimisation
echo "\n7. Informations de performance\n";
echo "-------------------------------\n";

$start = microtime(true);

// Simulation de nombreuses opérations
for ($i = 0; $i < 1000; $i++) {
    $expr = new LiteralExpression($i);
    $result = $expr->interpret($context);
    $expr->validate();
    $expr->optimize();
}

$end = microtime(true);
$duration = ($end - $start) * 1000;

echo "Création et évaluation de 1000 expressions: " . number_format($duration, 2) . " ms\n";
echo "Performance moyenne: " . number_format($duration / 1000, 4) . " ms par expression\n";

// Rapport final du contexte
echo "\n8. Rapport final du contexte\n";
echo "-----------------------------\n";

$report = $context->getDetailedReport();
echo "Durée de vie totale: " . number_format($report['execution_time'] * 1000, 2) . " ms\n";
echo "Variables définies: " . $report['variables']['count'] . "\n";
echo "Événements déclenchés: " . $report['events']['count'] . "\n";

if (!empty($report['events']['list'])) {
    echo "Liste des événements:\n";
    foreach ($report['events']['list'] as $event) {
        echo "  - {$event['name']}";
        if (isset($event['data']) && $event['data'] !== null) {
            echo " (avec données)";
        }
        echo "\n";
    }
}

echo "\n===========================================\n";
echo "  Exemple d'usage avancé terminé !\n";
echo "===========================================\n";