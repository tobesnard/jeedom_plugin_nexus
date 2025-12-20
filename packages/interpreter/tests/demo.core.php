<?php

require 'vendor/autoload.php'; // Générer avec `composer dump-autoload`

use Nexus\Interpreter\Application\Services\JeedomCmdService;
use Nexus\Interpreter\Context\RuleContext;
use Nexus\Interpreter\Parser\BashRuleParser;

$cmdService = new JeedomCmdService();
$parser = new BashRuleParser($cmdService);
$context = new RuleContext(false, $cmdService); // Mode debug = false, avec service

$rules = [
    // "if true : exec #[Sécurité][Sirènes Heiman][Tweet]#",
    // "if true : exec #[Do][Not][Exist]#",
    // "if true : exec #6840#", // 6840 = #[Sécurité][Sirènes Heiman][Tweet]#
    // "if #[Multimédia][Philips TV][Power]# -eq  0  : exec #[Scripts][Philips TV][Power On]#",
    // "if true: exec #[Télécommunication][Telegram][Tony]# message->bonjour",
    // "if true: event #[DevZone][Test][Event]# 'hello eventByString'",
    // "if true: event #15137# 'hello eventById'",
    // "exec #6840#, event #15137# yo, log #15137# ",
    // "log 'hello from ...'",
    "if #[Multimédia][Galets][Volume]# -lt 75 : exec #[Multimédia][Galets][Volume Set]# slider->80 ",
];

foreach ($rules as $i => $ruleString) {
    $start = microtime(true);
    echo "\n[Règle " . ($i + 1) . "]\t \"" . $ruleString . "\" ";

    try {
        $abstractSyntaxTree = $parser->parse($ruleString);
        $abstractSyntaxTree->interpret($context);
    } catch (Exception $e) {
        echo "Erreur de Parsing/Interprétation : " . $e->getMessage() . "\n";
    } finally {
        $end = microtime(true);
        $executionTime = $end - $start;
        echo "\n[Execution Time] : {$executionTime}";
    }
}
