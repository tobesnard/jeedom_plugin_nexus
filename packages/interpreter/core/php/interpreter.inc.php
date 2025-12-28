<?php

require_once __DIR__ . '/../../../../vendor/autoload.php';

use Nexus\Jeedom\Services\JeedomCmdService;
use Nexus\Interpreter\Context\RuleContext;
use Nexus\Interpreter\Parser\BashRuleParser;
use Nexus\Utils\Helpers;

/**
 * Méthode Proxy : Exécute l'interpréteur avec une instruction de type 'if #123# -eq true : exec #[cmd]#'
 **/
function interpret($args)
{
    $instruction = implode(',', func_get_args());

    // Log de l'instruction reçue dans un fichier temporaire
    $date = date("Y-m-d H:i:s");
    $log = "[{$date}] [Interpret] {$instruction}\n";
    file_put_contents("/tmp/interpret.log", $log, FILE_APPEND | LOCK_EX);

    return Helpers::execute(function () use ($instruction) {
        $cmdService = JeedomCmdService::getInstance();
        $parser = new BashRuleParser($cmdService);
        $context = new RuleContext(false, $cmdService); // Mode debug = false

        $abstractSyntaxTree = $parser->parse($instruction);
        return $abstractSyntaxTree->interpret($context);
    }, "Erreur de Parsing/Interprétation de l'instruction.");
}
