<?php

require __DIR__ . '/../../../vendor/autoload.php';

use Nexus\Communication\TelegramBot;

/**
 * Script de test interactif pour le systeme Nexus Communication.
 * Supporte le Polling (getUpdates) et le Webhook dynamique.
 */

// Configuration des couleurs ANSI pour la console
$green  = "\033[32m";
$blue   = "\033[34m";
$yellow = "\033[33m";
$cyan   = "\033[36m";
$red    = "\033[31m";
$reset  = "\033[0m";

try {
    $bot = new TelegramBot();

    // Verification de l'identite du chat cible
    if (!$bot->getChatId()) {
        echo $yellow . "[!] Aucun ChatID detecte. Initialisation de la procedure de decouverte..." . $reset . "\n";
        $bot->discoverAndSaveId();
    }

    // Affichage du menu principal
    echo "\n" . $cyan . "--- NEXUS COMMUNICATION INTERFACE ---" . $reset . "\n";
    echo "1. Mode Texte simple (ask/Pooling)\n";
    echo "2. Mode Boutons Inline (askWithInlineKeyboard/Pooling)\n";
    echo "3. Mode Clavier Physique (askWithReplyKeyboard/Pooling)\n";
    echo "4. Test Webhook (Cycle complet : Set -> Ask -> Delete)\n";
    echo "5. Sortie\n";
    echo $cyan . "--------------------------------------" . $reset . "\n";
    echo "Action requise : ";

    $input = trim(fgets(STDIN));

    switch ($input) {
        case '1':
            echo $green . "[>] Execution : ask()..." . $reset . "\n";
            $bot->ask("Nexus : Protocole de test texte active. En attente de reponse...");

            echo $yellow . "[-] Ecoute (Polling)..." . $reset . "\n";
            $res = $bot->waitForResponse(30);
            echo $blue . "[+] Recu : " . $reset . ($res ?? "TIMEOUT") . "\n";
            break;

        case '2':
            echo $green . "[>] Execution : askWithInlineKeyboard()..." . $reset . "\n";
            $options = ["ACCEPTER" => "confirm_ok", "REFUSER" => "confirm_no"];
            $bot->askWithInlineKeyboard("Nexus : Validation requise via Inline Keyboard :", $options);

            echo $yellow . "[-] Attente clic bouton..." . $reset . "\n";
            $res = $bot->waitForResponse(30);
            echo $blue . "[+] Callback Data recue : " . $reset . ($res ?? "TIMEOUT") . "\n";
            break;

        case '3':
            echo $green . "[>] Execution : askWithReplyKeyboard()..." . $reset . "\n";
            $choices = ["Option A", "Option B", "Annuler"];
            $bot->askWithReplyKeyboard("Nexus : Selectionnez une option sur votre clavier :", $choices);

            echo $yellow . "[-] Attente interaction clavier..." . $reset . "\n";
            $res = $bot->waitForResponse(30);
            echo $blue . "[+] Texte recu : " . $reset . ($res ?? "TIMEOUT") . "\n";
            break;

        case '4':
            echo $cyan . "[*] Initialisation du pont Webhook..." . $reset . "\n";

            // 1. Suppression preventive du fichier de stockage AVANT toute action
            $tempFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'nexus_bot_last_response.txt';
            if (file_exists($tempFile)) {
                unlink($tempFile);
            }

            if ($bot->setWebhook()) {
                echo $green . "[+] Flux Webhook etabli." . $reset . "\n";

                // 2. On attend 1 ou 2 secondes pour laisser Telegram "purger" les vieux messages
                // vers notre webhook sans que waitForResponse ne les traite comme la reponse.
                echo $yellow . "[-] Stabilisation du flux..." . $reset . "\n";
                sleep(2);
                if (file_exists($tempFile)) {
                    unlink($tempFile);
                }

                $bot->ask("Nexus : Test Webhook. Tapez votre reponse maintenant.");
                echo $yellow . "[-] Lecture du fichier temporaire systeme..." . $reset . "\n";

                $res = $bot->waitForResponse(60);
                echo $blue . "[+] Recu via Webhook : " . $reset . ($res ?? "TIMEOUT") . "\n";

                if ($bot->deleteWebhook()) {
                    echo $yellow . "[-] Webhook detruit." . $reset . "\n";
                }
            }
            break;

        case '5':
            echo $yellow . "[-] Arret du systeme." . $reset . "\n";
            exit;

        default:
            echo $red . "[!] Entree invalide." . $reset . "\n";
            break;
    }

} catch (Exception $e) {
    echo $red . "[!!!] FATAL : " . $e->getMessage() . $reset . "\n";
}
