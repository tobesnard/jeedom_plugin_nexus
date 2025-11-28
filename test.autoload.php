<?php

require_once "vendor/autoload.php";

// Test l'autoload pour la syncbox
echo \Nexus\HueSync\Syncbox::test();

// Test l'autoload pour l'interpreter
use Nexus\Interpreter\Application\Services\CmdServiceMock;
use Nexus\Interpreter\Context\RuleContext;
use Nexus\Interpreter\Parser\BashRuleParser;

$cmdService = new CmdServiceMock();
$parser = new BashRuleParser($cmdService);
$context = new RuleContext(false, $cmdService); // Mode debug = false, avec service

try {
    $abstractSyntaxTree = $parser->parse("log 'hello autoload'");
    $abstractSyntaxTree->interpret($context);
} catch (Exception $e) {
    echo "Erreur de Parsing/Interprétation : " . $e->getMessage() . "\n";
}
