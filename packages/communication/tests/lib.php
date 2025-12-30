<?php

require __DIR__ . '/../../../vendor/autoload.php';

use Nexus\Communication\TelegramBot;

/**
 * Test de communication via Webhook.
 * Active le Webhook, pose une question, attend la reponse via fichier temporaire systeme,
 * puis restaure le mode Polling.
 */
function askWebhook(): void
{
    $green  = "\033[32m";
    $yellow = "\033[33m";
    $red    = "\033[31m";
    $reset  = "\033[0m";

    try {
        $bot = new TelegramBot();

        if (!$bot->setWebhook()) {
            throw new Exception("Impossible d'activer le Webhook.");
        }
        echo $green . "[+] Webhook active sur Telegram." . $reset . "\n";

        $question = "Nexus : Operation confirmee via Webhook ?";
        if ($bot->ask($question)) {
            echo $yellow . "[-] Question envoyee. Attente du flux Webhook..." . $reset . "\n";

            $reponse = $bot->waitForResponse(60);

            if ($reponse !== null) {
                echo $green . "[+] Reponse recue : " . $reset . $reponse . "\n";
            } else {
                echo $red . "[!] Timeout : Aucune reponse recue." . $reset . "\n";
            }
        }
    } catch (Exception $e) {
        echo $red . "[!] Erreur : " . $e->getMessage() . $reset . "\n";
    } finally {
        if (isset($bot) && $bot->deleteWebhook()) {
            echo $yellow . "[-] Webhook desactive. Retour au mode Polling." . $reset . "\n";
        }
    }
}

/**
 * Test des boutons Inline (sous le message).
 * Envoie des donnees de callback invisibles pour l'utilisateur.
 */
function askWithInlineKeyboard(): void
{
    $bot = new TelegramBot();

    $question = "Quel environnement deployer ?";
    $options = [
        "Production" => "env_prod",
        "Staging"    => "env_staging",
        "Dev"        => "env_dev",
    ];

    if ($bot->askWithInlineKeyboard($question, $options)) {
        echo "[>] Question Inline envoyee. En attente du clic...\n";
        $choix = $bot->waitForResponse(30);

        echo $choix
            ? "[+] L'utilisateur a choisi (Data) : " . $choix . "\n"
            : "[!] Delai expire.\n";
    }
}

/**
 * Test du Reply Keyboard (remplace le clavier systeme).
 * Envoie du texte simple lors du clic sur un bouton.
 */
function askWithReplyKeyboard(): void
{
    $bot = new TelegramBot();

    $question = "Voulez-vous redemarrer les services ?";
    $boutons = ["Oui", "Non", "Plus tard"];

    if ($bot->askWithReplyKeyboard($question, $boutons)) {
        echo "[>] Clavier de reponse envoye...\n";
        $reponse = $bot->waitForResponse(30);

        if ($reponse) {
            echo "[+] L'utilisateur a clique sur : " . $reponse . "\n";
        }
    }
}

/**
 * Test standard en mode texte simple.
 * Utilise le polling classique et gere la decouverte d'ID.
 */
function ask(): void
{
    try {
        $bot = new TelegramBot();

        if (!$bot->getChatId()) {
            $bot->discoverAndSaveId();
        }

        $question = "Nexus System : Souhaitez-vous purger les logs ? (Oui/Non)";

        if ($bot->ask($question)) {
            echo "[>] Message envoye. Attente de texte...\n";

            $reponse = $bot->waitForResponse(30);

            if ($reponse !== null) {
                echo "[+] Reponse recue : " . $reponse . "\n";
                echo (strtolower($reponse) === 'oui')
                    ? "[!] Action confirmee.\n"
                    : "[i] Action annulee.\n";
            } else {
                echo "[!] Delai d'attente depasse.\n";
            }
        }
    } catch (Exception $e) {
        echo "[-] Erreur critique : " . $e->getMessage() . "\n";
    }
}
