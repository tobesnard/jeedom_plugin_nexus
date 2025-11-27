<?php

function cleanFile($filePath)
{
    $content = file_get_contents($filePath);

    // Trouve la première occurrence de "namespace" après <?php
    $firstNamespacePos = strpos($content, 'namespace ');
    if ($firstNamespacePos === false) {
        return false;
    }

    // Trouve la seconde occurrence de "namespace"
    $secondNamespacePos = strpos($content, 'namespace ', $firstNamespacePos + 1);
    if ($secondNamespacePos === false) {
        return false;
    } // Pas de duplication

    // Garde seulement la partie jusqu'à la première occurrence
    $cleanContent = substr($content, 0, $secondNamespacePos);

    // S'assure que le fichier se termine proprement
    $cleanContent = rtrim($cleanContent) . "\n";

    file_put_contents($filePath, $cleanContent);
    echo "Nettoyé: $filePath\n";

    return true;
}

$files = [
    'src/Expression/NonTerminal/Action/EventExpression.php',
    'src/Expression/NonTerminal/Action/ExecExpression.php',
    'src/Expression/NonTerminal/Comparison/GeExpression.php',
    'src/Expression/NonTerminal/Comparison/NeExpression.php',
    'src/Expression/NonTerminal/Comparison/LeExpression.php',
    'src/Expression/NonTerminal/SequenceExpression.php',
    'src/Expression/NonTerminal/Logical/OrExpression.php',
    'src/Expression/NonTerminal/Logical/AndExpression.php',
    'src/Expression/Terminal/CmdByStringExpression.php',
    'src/Expression/Terminal/CmdByIdExpression.php',
];

foreach ($files as $file) {
    if (file_exists($file)) {
        cleanFile($file);
    }
}

echo "Nettoyage terminé!\n";
