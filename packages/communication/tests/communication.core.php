<?php

require __DIR__ . '/../../../vendor/autoload.php';

use Nexus\Communication\TelegramBot;

// Definition des codes couleurs ANSI
$green  = "\033[32m";
$blue   = "\033[34m";
$yellow = "\033[33m";
$cyan   = "\033[36m";
$red    = "\033[31m";
$reset  = "\033[0m";

try {
    $bot = new TelegramBot();

    // Verifie si le Chat ID est configure
    if (!$bot->getChatId()) {
        echo $yellow . "[!] Aucun ChatID detecte. En attente de decouverte..." . $reset . "\n";
        $bot->discoverAndSaveId();
    }

    echo $cyan . "--- NEXUS COMMUNICATION TESTER ---" . $reset . "\n";
    echo "1. Tester ask() (Texte simple)\n";
    echo "2. Tester askWithInlineKeyboard() (Boutons sous message)\n";
    echo "3. Tester askWithReplyKeyboard() (Clavier de reponse)\n";
    echo "4. Quitter\n";
    echo $cyan . "----------------------------------" . $reset . "\n";
    echo "Votre choix : ";

    $input = trim(fgets(STDIN));

    switch ($input) {
        case '1':
            echo $green . "[>] Lancement de ask()..." . $reset . "\n";
            $bot->ask("Nexus : Question test en mode texte. Repondez n'importe quoi.");
            echo $yellow . "[-] En attente de reponse sur Telegram..." . $reset . "\n";
            $res = $bot->waitForResponse(30);
            echo $blue . "[+] Recu : " . $reset . ($res ?? "TIMEOUT") . "\n";
            break;

        case '2':
            echo $green . "[>] Lancement de askWithInlineKeyboard()..." . $reset . "\n";
            $boutons = [
                "Accepter" => "exec_ok",
                "Refuser"  => "exec_no",
            ];
            $bot->askWithInlineKeyboard("Nexus : Validation via Inline Keyboard ?", $boutons);
            echo $yellow . "[-] Cliquez sur un bouton..." . $reset . "\n";
            $res = $bot->waitForResponse(30);
            echo $blue . "[+] Data recue : " . $reset . ($res ?? "TIMEOUT") . "\n";
            break;

        case '3':
            echo $green . "[>] Lancement de askWithReplyKeyboard()..." . $reset . "\n";
            $options = ["Ping", "Pong", "Statut"];
            $bot->askWithReplyKeyboard("Nexus : Choisissez une commande clavier :", $options);
            echo $yellow . "[-] En attente d'un clic clavier..." . $reset . "\n";
            $res = $bot->waitForResponse(30);
            echo $blue . "[+] Commande recue : " . $reset . ($res ?? "TIMEOUT") . "\n";
            break;

        case '4':
            echo $red . "Arret du script." . $reset . "\n";
            exit;

        default:
            echo $red . "Choix non reconnu." . $reset . "\n";
            break;
    }

} catch (Exception $e) {
    echo $red . "[!] Erreur : " . $e->getMessage() . $reset . "\n";
}

