<?php

require_once __DIR__ . '/../../../vendor/autoload.php';

use Nexus\Interpreter\Application\Services\JeedomCmdService;
use Nexus\Interpreter\Context\RuleContext;
use Nexus\Interpreter\Parser\BashRuleParser;

/**
 * Méthode Proxy, Exécute l'interpréteur maison avec une commande de type 'if #123# -eq true : exec #[edom][obj][cmd]#'
 **/

function interpret($args)
{
    $instruction = implode(',', func_get_args());
    $cmdService = new JeedomCmdService();
    $parser = new BashRuleParser($cmdService);
    $context = new RuleContext(false, $cmdService); // Mode debug = false, avec service

    // Log la l'instruction reçu
    $date = date("Y-m-d H:i:s");
    $log = "[{$date}] [Intepret] {$instruction}\n";
    file_put_contents("/tmp/interpret.log", $log, FILE_APPEND | LOCK_EX);

    // Interprétation et exécution de la commande
    try {
        $abstractSyntaxTree = $parser->parse($instruction);
        $abstractSyntaxTree->interpret($context); // Passage du contexte avec service
    } catch (Exception $e) {
        echo "Erreur de Parsing/Interprétation : " . $e->getMessage() . "\n";
    }
}
