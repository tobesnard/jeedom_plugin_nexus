<?php

require __DIR__ . '/../../../vendor/autoload.php';

use Nexus\Communication\TelegramBot;

function askWithInlineKeyboard()
{
    $bot = new TelegramBot();

    $question = "Quel environnement déployer ?";
    $options = [
        "Production" => "env_prod",
        "Staging"    => "env_staging",
        "Dev"        => "env_dev",
    ];

    if ($bot->askWithButtons($question, $options)) {
        echo "Question avec boutons envoyée...\n";
        $choix = $bot->waitForResponse(30);

        if ($choix) {
            echo "L'utilisateur a choisi : " . $choix . "\n";
        } else {
            echo "Délai expiré.\n";
        }
    }
}


function askWithReplyKeyboard()
{
    $bot = new TelegramBot();

    $question = "Voulez-vous redémarrer les services ?";
    $boutons = ["✅ Oui", "❌ Non", "⏳ Plus tard"];

    if ($bot->askWithReplyKeyboard($question, $boutons)) {
        echo "Clavier de réponse envoyé...\n";

        // On utilise waitForResponse normalement car ce clavier envoie du TEXTE
        $reponse = $bot->waitForResponse(30);

        if ($reponse) {
            echo "L'utilisateur a cliqué sur : " . $reponse . "\n";
        }
    }
}

function ask()
{
    try {
        $bot = new TelegramBot();

        // 1. Vérification / Découverte de l'identifiant
        if (!$bot->getChatId()) {
            $bot->discoverAndSaveId();
        }

        $question = "Nexus System : Souhaitez-vous purger les logs temporaires ? (Oui/Non)";

        // 2. Envoi de la question
        if ($bot->ask($question)) {
            echo "[>] Question envoyée : $question\n";
            echo "[-] En attente de la réponse de l'utilisateur...\n";

            // 3. Récupération de la réponse
            // On récupère le timeout depuis la config ou 60s par défaut
            $config = json_decode(file_get_contents(__DIR__ . "/../config/config.json"), true);
            $timeout = $config['telegram']['settings']['timeout'] ?? 60;

            $reponse = $bot->waitForResponse($timeout);

            if ($reponse !== null) {
                echo "[+] Réponse reçue : " . $reponse . "\n";

                // Logique conditionnelle basée sur la réponse
                if (strtolower($reponse) === 'oui') {
                    echo "[!] Action confirmée par l'utilisateur.\n";
                } else {
                    echo "[i] Action annulée ou refusée.\n";
                }
            } else {
                echo "[!] Délai d'attente dépassé (Timeout de {$timeout}s).\n";
            }
        } else {
            echo "[x] Erreur lors de l'envoi du message.\n";
        }

    } catch (Exception $e) {
        echo "[-] Erreur critique : " . $e->getMessage() . "\n";
    }


}
