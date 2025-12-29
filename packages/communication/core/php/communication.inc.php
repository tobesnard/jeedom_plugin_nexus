<?php

require_once __DIR__ . "/../../../../vendor/autoload.php";

use Nexus\Utils\Helpers;
use Nexus\Communication\TelegramBot;

/**
 * Méthode Proxy : communication_askTelegram
 * * Utilise askWithReplyKeyboard pour forcer l'affichage du clavier personnalisé
 * et bascule en mode Webhook pour capturer la réponse de manière synchrone.
 * * @param string $question Le message texte à envoyer.
 * @param array $reponses Liste des libellés pour les boutons du clavier.
 * @param int $timeout Temps d'attente maximum en secondes.
 * @return string Réponse utilisateur, "timeout" ou "error: message".
 */
function communication_askTelegram(string $question, array $reponses, int $timeout): string
{
    return Helpers::execute(function () use ($question, $reponses, $timeout) {
        try {
            $bot = new TelegramBot();

            // Activation du Webhook
            if (!$bot->setWebhook()) {
                throw new Exception("Impossible d'activer le Webhook.");
            }

            // Envoi de la question avec le ReplyKeyboard (boutons physiques sur Telegram)
            if ($bot->askWithReplyKeyboard($question, $reponses)) {
                // Attente de la réponse via l'entrée Webhook
                $reponse = $bot->waitForResponse($timeout);

                if ($reponse !== null) {
                    $bot->removeKeyboard("Vous avez choisi : " . $reponse);
                    return $reponse;
                }
            }
            return "timeout";
        } finally {
            if (isset($bot)) {
                // Important : Supprime le Webhook pour restaurer le mode polling/standard
                $bot->deleteWebhook();
            }
        }
    }, "");
}
