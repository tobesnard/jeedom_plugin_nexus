<?php

namespace Nexus\Communication;

require __DIR__ . '/../../../../vendor/autoload.php';

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Exception;

/**
 * Classe TelegramBot : Gestion hybride Polling/Webhook pour l'API Telegram.
 * * Cette classe permet l'envoi de messages et la récupération synchrone de réponses
 * en basculant dynamiquement entre l'écoute active (Polling) et passive (Webhook).
 * Le chemin du fichier de stockage est désormais récupéré dynamiquement depuis la configuration.
 */
class TelegramBot
{
    /** @var string Token d'authentification de l'API Bot Telegram */
    private string $token;

    /** @var int|null Identifiant unique du chat Telegram cible */
    private ?int $chatId;

    /** @var string Chemin absolu vers le fichier de configuration JSON */
    private string $configPath;

    /** @var array Contenu du fichier de configuration */
    private array $config;

    /** @var Client Instance du client HTTP Guzzle */
    private Client $client;

    /** @var string Chemin du fichier tampon pour la communication inter-processus */
    private string $storageFile;

    /**
     * Initialise le bot, charge la configuration et définit le stockage temporaire.
     * * @throws Exception Si le fichier de configuration est introuvable.
     */
    public function __construct()
    {
        $this->configPath = __DIR__ . "/../config/config.json";

        if (!file_exists($this->configPath)) {
            throw new Exception("Configuration introuvable : {$this->configPath}");
        }

        $this->config = json_decode(file_get_contents($this->configPath), true);

        // Récupération dynamique du chemin de stockage depuis le JSON
        $this->storageFile = $this->config['telegram']['settings']['storage_file'] ?? '/tmp/nexus/nexus_bot_last_response.txt';

        // Priorité aux variables d'environnement, puis config.json
        $this->token  = $_ENV['TELEGRAM_BOT_TOKEN'] ?? $this->config['telegram']['token'] ?? '';
        $this->chatId = $_ENV['TELEGRAM_CHAT_ID'] ?? $this->config['telegram']['chat_id'] ?? null;

        $this->client = new Client([
            'base_uri' => "https://api.telegram.org/bot{$this->token}/",
            'timeout'  => 35.0,
        ]);
    }

    /**
     * Envoie un message avec un clavier de réponse (Reply Keyboard).
     * * @param string $message Le texte de la question.
     * @param array $options Liste de textes pour les boutons.
     * @param bool $oneTime Si vrai, le clavier se masque après l'usage.
     * @return bool True en cas de succès.
     */
    public function askWithReplyKeyboard(string $message, array $options, bool $oneTime = true): bool
    {
        $keyboard = [];
        foreach ($options as $text) {
            $keyboard[] = [['text' => $text]];
        }

        return $this->sendRequest('sendMessage', [
            'chat_id'      => $this->chatId,
            'text'         => $message,
            'reply_markup' => [
                'keyboard'          => $keyboard,
                'resize_keyboard'   => true,    // Adapte la taille des boutons
                'one_time_keyboard' => $oneTime, // Masque le clavier après appui
            ],
        ]);
    }

    /**
     * Supprime le clavier physique (Reply Keyboard) de l'écran de l'utilisateur.
     * @param string $text Message à afficher lors de la suppression.
     */
    public function removeKeyboard(string $text = "Traitement terminé."): bool
    {
        return $this->sendRequest('sendMessage', [
            'chat_id'      => $this->chatId,
            'text'         => $text,
            'reply_markup' => [
                'remove_keyboard' => true,
            ],
        ]);
    }

    /**
     * Envoie un message simple.
     * @param string $message
     * @return bool
     */
    public function ask(string $message): bool
    {
        return $this->sendRequest('sendMessage', [
            'chat_id' => $this->chatId,
            'text'    => $message,
        ]);
    }

    /**
     * Envoie un message avec un clavier de boutons Inline.
     * @param string $message
     * @param array $options Tableau associatif ["Label" => "callback_data"]
     * @return bool
     */
    public function askWithInlineKeyboard(string $message, array $options): bool
    {
        $inline_keyboard = [];
        foreach ($options as $text => $callback_data) {
            $inline_keyboard[] = [['text' => $text, 'callback_data' => $callback_data]];
        }

        return $this->sendRequest('sendMessage', [
            'chat_id'      => $this->chatId,
            'text'         => $message,
            'reply_markup' => [
                'inline_keyboard' => [$inline_keyboard],
            ],
        ]);
    }


    /**
     * Attend une réponse en détectant automatiquement le mode (Webhook ou Polling).
     * * @param int $maxWait Temps d'attente maximum en secondes.
     * @return string|null La réponse reçue ou null en cas de dépassement de délai.
     */
    public function waitForResponse(int $maxWait = 60): ?string
    {
        $isWebhook = false;
        try {
            $response = $this->client->get('getWebhookInfo');
            $info = json_decode($response->getBody(), true);
            $isWebhook = !empty($info['result']['url']);
        } catch (GuzzleException $e) {
        }

        return $isWebhook ? $this->waitForWebhookFile($maxWait) : $this->waitForPolling($maxWait);
    }

    /**
     * Écoute passive : surveille les modifications du fichier généré par le webhook.
     */
    private function waitForWebhookFile(int $maxWait): ?string
    {
        $startTime = time();
        $referenceTime = time();

        while ((time() - $startTime) < $maxWait) {
            // Indispensable pour rafraîchir les métadonnées (mtime) lues par PHP
            clearstatcache(true, $this->storageFile);

            if (file_exists($this->storageFile)) {
                $fileMTime = filemtime($this->storageFile);

                // On ne traite le fichier que s'il a été mis à jour après l'envoi de la question
                if ($fileMTime >= $referenceTime) {
                    $data = trim(file_get_contents($this->storageFile));

                    if (!empty($data)) {
                        // Vidage propre par écrasement
                        @file_put_contents($this->storageFile, '');
                        return $data;
                    }
                }
            }
            usleep(500000);
        }
        return null;
    }

    /**
     * Écoute active : interroge l'API via getUpdates.
     */
    private function waitForPolling(int $maxWait): ?string
    {
        $startTime = time();
        $offset = $this->getNextOffset();

        while ((time() - $startTime) < $maxWait) {
            try {
                $response = $this->client->post('getUpdates', [
                    'json' => ['offset' => $offset, 'timeout' => 10],
                ]);
                $updates = json_decode($response->getBody(), true)['result'] ?? [];

                foreach ($updates as $update) {
                    if (isset($update['message']) && $update['message']['chat']['id'] == $this->chatId) {
                        return $update['message']['text'] ?? null;
                    }
                    if (isset($update['callback_query']) && $update['callback_query']['message']['chat']['id'] == $this->chatId) {
                        $this->client->post('answerCallbackQuery', [
                            'json' => ['callback_query_id' => $update['callback_query']['id']],
                        ]);
                        return $update['callback_query']['data'];
                    }
                    $offset = $update['update_id'] + 1;
                }
            } catch (GuzzleException $e) {
            }
            usleep(500000);
        }
        return null;
    }

    /**
     * Envoie une requête HTTP à l'API Telegram.
     */
    private function sendRequest(string $method, array $params): bool
    {
        if (!$this->chatId && !in_array($method, ['setWebhook', 'deleteWebhook'])) {
            $this->discoverAndSaveId();
        }
        try {
            $response = $this->client->post($method, ['json' => $params]);
            return json_decode($response->getBody(), true)['ok'] ?? false;
        } catch (GuzzleException $e) {
            return false;
        }
    }

    /**
     * Détermine l'offset pour ne récupérer que les nouveaux messages.
     */
    private function getNextOffset(): int
    {
        try {
            $res = $this->client->post('getUpdates', ['json' => ['limit' => 1, 'offset' => -1]]);
            $data = json_decode($res->getBody(), true);
            return isset($data['result'][0]['update_id']) ? $data['result'][0]['update_id'] + 1 : 0;
        } catch (GuzzleException $e) {
            return 0;
        }
    }

    /**
     * Analyse les messages entrants pour identifier l'ID via un mot-clé.
     */
    public function discoverAndSaveId(): int
    {
        $keyword = $this->config['telegram']['settings']['discovering_keyword'];
        echo "[-] En attente du mot-clé : {$keyword}\n";

        while (true) {
            try {
                $response = $this->client->post('getUpdates', [
                    'json' => ['limit' => 1, 'offset' => -1, 'timeout' => 20],
                ]);
                $data = json_decode($response->getBody(), true);

                if (!empty($data['result'])) {
                    $update = $data['result'][0];
                    if (isset($update['message']['text']) && str_contains($update['message']['text'], $keyword)) {
                        $this->chatId = $update['message']['chat']['id'];
                        $this->saveIdToConfig($this->chatId);
                        return $this->chatId;
                    }
                }
            } catch (GuzzleException $e) {
            }
            sleep(1);
        }
    }

    public function setWebhook(): bool
    {
        $url = $this->config['telegram']['settings']['webhook_url'];
        return $this->sendRequest('setWebhook', ['url' => $url]);
    }

    public function deleteWebhook(): bool
    {
        return $this->sendRequest('deleteWebhook', []);
    }

    /**
     * Retourne l'identifiant du chat Telegram configuré.
     * @return int|null
     */
    public function getChatId(): ?int
    {
        return $this->chatId;
    }

    private function saveIdToConfig(int $id): void
    {
        $this->config['telegram']['chat_id'] = $id;
        file_put_contents($this->configPath, json_encode($this->config, JSON_PRETTY_PRINT));
    }
}
