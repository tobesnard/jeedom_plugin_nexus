<?php

require 'vendor/autoload.php';

use Interpreter\Application\Services\CmdServiceMop;
use Interpreter\Context\RuleContext;
use Interpreter\Parser\BashRuleParser;

$cmdService = new CmdServiceMop();
$parser = new BashRuleParser($cmdService);
$context = new RuleContext(false, $cmdService);

// Test simple avec 'or'
$testRule = "if true -or false : log 'or'";

echo "Test de parsing pour: $testRule\n\n";

try {
    $ast = $parser->parse($testRule);

    echo "AST créé: " . get_class($ast) . "\n";
    echo "toString(): " . $ast->__toString() . "\n\n";

    echo "Exécution:\n";
    $ast->interpret($context);

} catch (Exception $e) {
    echo "Erreur: " . $e->getMessage() . "\n";
}

