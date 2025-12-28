<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require 'vendor/autoload.php'; // Générer avec `composer dump-autoload`

use Nexus\Jeedom\Services\CmdServiceMock;
use Nexus\Interpreter\Context\RuleContext;
use Nexus\Interpreter\Parser\BashRuleParser;

$cmdService = new CmdServiceMock();
$parser = new BashRuleParser($cmdService);
$context = new RuleContext(false, $cmdService); // Mode debug = false, avec service

$rules = [
    "if 21 -gt 20 : log '-gt ✅' ",
    "if false -eq false : log '-eq ✅'",
    "if -not false : log '-not ✅'",
    "if 14 -lt 15 : log '-lt ✅' ",
    "if true -ne false : log '-ne ✅'",
    "if true -or false : log '-or ✅'",
    "if true -and true: log '-and ✅:with'",
    "if #[edom][edom][temperature]# -eq 18.5: log 'value byString ✅'",
    "if #18# -eq 18.5: log 'value byId ✅' ",
    "if 19 -ne #18# : log 'right operand'",
    "if true: exec #[edom][cmd][on]# ",
    "if -not true -and false: log not->and",
    "if 21 -gt 20 : log 'Température supérieure à la limite'",
    "if true: exec #[edom][cmd][with args]# hello 'how are you'",
    "if false: log 'true clause'; log 'clause false ✅'",
    "exec #[edom][cmd][with args array]# message->'hello world' options->'silent:true' argx 'arg y' ",
    "exec #133# message->'hello world' options->'silent:true' argx 'arg y' ",
    "if true: log a, log b, log c ",
    "log 'hello world', log \"l'histore est belle\" ",
    "event #[edom][event][set]# 'on' ",
    "log 'hello', log 'comment ça va ?', log #15137# ",

];

foreach ($rules as $i => $ruleString) {
    echo "\n[Règle " . ($i + 1) . "]\t \"" . $ruleString . "\" ";

    try {
        // Parsing (Construction de l'AST)
        $abstractSyntaxTree = $parser->parse($ruleString);

        // Interprétation (Exécution de l'AST)
        $abstractSyntaxTree->interpret($context);
    } catch (Exception $e) {
        echo "Erreur de Parsing/Interprétation : " . $e->getMessage() . "\n";
    }
}
