<?php

// Inclusion de la fonction proxy définie précédemment
require_once __DIR__ . "/../core/php/communication.inc.php";

/**
 * Script de test pour communication_askTelegram
 */

// Couleurs pour le rendu CLI
$green  = "\033[0;32m";
$yellow = "\033[1;33m";
$red    = "\033[0;31m";
$reset  = "\033[0m";

// Paramètres de test
$question = "Nexus : Validation de l'acces root demandee ?";
$reponses = ["Ouvrir", "Refuser"];
$timeout  = 30;

echo $yellow . "[*] Lancement du test de la Methode Proxy..." . $reset . "\n";

// Appel de la fonction
$resultat = communication_askTelegram($question, $reponses, $timeout);

// Analyse du retour
if (strpos($resultat, 'error:') === 0) {
    echo $red . "[!] Echec du test : " . $resultat . $reset . "\n";
} elseif ($resultat === "timeout") {
    echo $yellow . "[!] Test termine : Delai d'attente depasse (Timeout)." . $reset . "\n";
} else {
    echo $green . "[+] Succes : Reponse recue -> " . $reset . $resultat . "\n";
}

exit(0);
