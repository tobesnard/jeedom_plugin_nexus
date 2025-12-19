<?php

// Test de la regex de parsing
$argString = "'or'";
$pattern = "/(\#.+?\#|'[^']+'|\"[^\"]+\"|\S+)/";

echo "Test de la regex sur: $argString\n";

if (preg_match_all($pattern, $argString, $matches)) {
    echo "Matches trouvés:\n";
    var_dump($matches[0]);
} else {
    echo "Aucun match trouvé!\n";
}

echo "\nTest de l'extraction de quotes:\n";
$testString = "'or'";
$isQuoted = preg_match("/^(['\"])(.*)\\1$/s", $testString, $quoteMatches);

if ($isQuoted) {
    echo "String quotée détectée\n";
    echo "Quote character: " . $quoteMatches[1] . "\n";
    echo "Contenu: '" . $quoteMatches[2] . "'\n";
    echo "Longueur: " . strlen($quoteMatches[2]) . "\n";
} else {
    echo "Pas de quote détectée\n";
}