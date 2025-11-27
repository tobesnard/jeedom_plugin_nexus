<?php

/**
 * Script de démonstration de l'Interpréteur Jeedom refactorisé
 * 
 * Ce script montre l'utilisation des nouvelles fonctionnalités
 * et des améliorations apportées au code.
 */

require_once __DIR__ . '/vendor/autoload.php';

use Interpreter\Context\RuleContext;
use Interpreter\Expression\Terminal\LiteralExpression;
use Interpreter\Application\Services\JeedomCmdService;

echo "\n";
echo "===========================================\n";
echo "  Démonstration Interpréteur Jeedom 1.0\n";
echo "  Refactorisation PHP 7.4+ avec bonnes pratiques\n";
echo "===========================================\n\n";

// Test 1 : Contexte avec mode debug
echo "1. Test du contexte avec mode debug activé\n";
echo "-------------------------------------------\n";

$context = new RuleContext(true);

// Test des variables avec types différents
echo "Définition de variables...\n";
$context->set('temperature', 22.5);
$context->set('mode', 'automatique');
$context->set('actif', true);
$context->set('compteur', 42);

echo "Variables définies :\n";
foreach ($context->getAllData() as $key => $value) {
    $type = gettype($value);
    echo "  - {$key} = ";
    if (is_bool($value)) {
        echo $value ? 'true' : 'false';
    } else {
        echo $value;
    }
    echo " ({$type})\n";
}

echo "\nRécupération avec valeurs par défaut :\n";
echo "  - température : " . $context->get('temperature', 0) . "\n";
echo "  - humidité (non définie) : " . $context->get('humidite', 50) . "\n";

// Test 2 : Expressions littérales améliorées
echo "\n2. Test des expressions littérales améliorées\n";
echo "----------------------------------------------\n";

$expressions = [
    ['Chaîne simple', new LiteralExpression('bonjour')],
    ['Nombre entier', new LiteralExpression(42)],
    ['Nombre flottant', new LiteralExpression(3.14)],
    ['Booléen true', new LiteralExpression(true)],
    ['Booléen false', new LiteralExpression(false)],
    ['Null', new LiteralExpression(null)],
    ['Chaîne avec guillemets', new LiteralExpression('"message important"')],
    ['Booléen depuis string', new LiteralExpression('true')],
    ['Nombre depuis string', new LiteralExpression('123')]
];

foreach ($expressions as [$description, $expression]) {
    echo "  {$description}:\n";
    echo "    - Valeur: " . var_export($expression->getValue(), true) . "\n";
    echo "    - Type: " . $expression->getValueType() . "\n";
    echo "    - String: " . $expression . "\n";
    echo "    - Valide: " . ($expression->validate() ? 'Oui' : 'Non') . "\n";
    echo "    - Résultat: " . var_export($expression->interpret($context), true) . "\n\n";
}

// Test 3 : Méthodes statiques de création
echo "3. Test des méthodes statiques de création\n";
echo "-------------------------------------------\n";

$staticExpressions = [
    ['fromString()', LiteralExpression::fromString('test')],
    ['boolean(true)', LiteralExpression::boolean(true)],
    ['number(100)', LiteralExpression::number(100)],
    ['null()', LiteralExpression::null()]
];

foreach ($staticExpressions as [$method, $expr]) {
    echo "  {$method}: {$expr} -> " . var_export($expr->getValue(), true) . "\n";
}

// Test 4 : Événements avec données
echo "\n4. Test des événements avec données\n";
echo "------------------------------------\n";

$context->triggerEvent('alarme_temperature', [
    'valeur' => 25.8,
    'seuil' => 25.0,
    'capteur' => 'salon'
]);

$context->triggerEvent('changement_mode', 'economie');
$context->triggerEvent('maintenance_requise');

echo "Événements déclenchés :\n";
foreach ($context->getTriggeredEvents() as $event) {
    echo "  - {$event['name']} ";
    if ($event['data'] !== null) {
        echo "(données: " . json_encode($event['data']) . ")";
    }
    echo " à " . date('H:i:s', (int)$event['timestamp']) . "\n";
}

// Test 5 : Service de commandes avec statistiques
echo "\n5. Test du service de commandes amélioré\n";
echo "-----------------------------------------\n";

$cmdService = new JeedomCmdService();

// Simulation d'exécutions
echo "Simulation d'exécutions de commandes...\n";
try {
    // Ces appels vont échouer car Jeedom n'est pas chargé, mais on verra la gestion d'erreur
    $cmdService->execById(123, ['test' => true]);
    $cmdService->execByString('test_cmd');
    $cmdService->eventByString('test_event', 'valeur');
} catch (Exception $e) {
    echo "Erreur attendue : " . $e->getMessage() . "\n";
}

echo "Statistiques du service :\n";
$stats = $cmdService->getStats();
foreach ($stats as $key => $value) {
    echo "  - {$key}: {$value}\n";
}

// Test 6 : Rapport détaillé du contexte
echo "\n6. Rapport détaillé du contexte\n";
echo "--------------------------------\n";

$report = $context->getDetailedReport();
echo "Temps d'exécution : " . number_format($report['execution_time'] * 1000, 2) . " ms\n";
echo "Mode debug : " . ($report['debug_mode'] ? 'Activé' : 'Désactivé') . "\n";
echo "Variables : " . $report['variables']['count'] . "\n";
echo "Événements : " . $report['events']['count'] . "\n";

// Test 7 : Validation et optimisation
echo "\n7. Test de validation et optimisation\n";
echo "--------------------------------------\n";

$testExpr = new LiteralExpression(42);
echo "Expression : {$testExpr}\n";
echo "Valide : " . ($testExpr->validate() ? 'Oui' : 'Non') . "\n";

$optimized = $testExpr->optimize();
echo "Optimisée : " . ($optimized === $testExpr ? 'Identique (déjà optimale)' : 'Modifiée') . "\n";

$cloned = $testExpr->deepClone();
echo "Clonage : " . ($cloned !== $testExpr && $cloned->getValue() === $testExpr->getValue() ? 'Réussi' : 'Échec') . "\n";

echo "\n";
echo "===========================================\n";
echo "  Démonstration terminée avec succès !\n";
echo "  ✅ Types stricts PHP 7.4+\n";
echo "  ✅ Documentation PHPDoc complète\n";
echo "  ✅ Gestion d'erreurs robuste\n";
echo "  ✅ Cache et optimisations\n";
echo "  ✅ Debug et profiling\n";
echo "  ✅ Validation et tests\n";
echo "===========================================\n";