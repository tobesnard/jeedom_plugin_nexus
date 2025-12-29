<?php

namespace Nexus\Communication;

require __DIR__ . '/../../../../vendor/autoload.php';

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Exception;

/**
 * Classe TelegramBot pour l'interaction via l'API Bot Telegram.
 * Supporte le polling, la decouverte d'ID et les claviers personnalises.
 */
class TelegramBot
{
    private string $token;
    private ?int $chatId;
    private string $configPath;
    private array $config;
    private Client $client;

    /**
     * Initialise la configuration et le client HTTP Guzzle.
     * @throws Exception Si le fichier de configuration est absent.
     */
    public function __construct()
    {
        $this->configPath = __DIR__ . "/../config/config.json";

        if (!file_exists($this->configPath)) {
            throw new Exception("Fichier de configuration manquant : {$this->configPath}");
        }

        $this->config = json_decode(file_get_contents($this->configPath), true);

        $this->token = $this->config['telegram']['token'] ?? '';
        $this->chatId = $this->config['telegram']['chat_id'] ?? null;

        $this->client = new Client([
            'base_uri' => "https://api.telegram.org/bot{$this->token}/",
            'timeout'  => 35.0,
        ]);
    }

    /**
     * Retourne l'identifiant de chat actuel charge depuis la config.
     * @return int|null
     */
    public function getChatId(): ?int
    {
        return $this->chatId;
    }

    /**
     * Envoie une question avec des boutons integres au message (Inline Keyboard).
     * @param string $message Le texte de la question.
     * @param array $buttons Tableau associatif ['Texte' => 'callback_data'].
     * @return bool Succes de l'envoi.
     */
    public function askWithInlineKeyboard(string $message, array $buttons): bool
    {
        if (!$this->chatId) {
            $this->discoverAndSaveId();
        }

        $keyboard = [];
        foreach ($buttons as $text => $callbackData) {
            $keyboard[] = [['text' => $text, 'callback_data' => $callbackData]];
        }

        try {
            $response = $this->client->post('sendMessage', [
                'json' => [
                    'chat_id'      => $this->chatId,
                    'text'         => $message,
                    'reply_markup' => [
                        'inline_keyboard' => $keyboard,
                    ],
                ],
            ]);
            $res = json_decode($response->getBody(), true);
            return $res['ok'] ?? false;
        } catch (GuzzleException $e) {
            error_log($e->getMessage());
            return false;
        }
    }

    /**
     * Envoie une question en remplacant le clavier physique de l'utilisateur.
     * @param string $message Le texte de la question.
     * @param array $options Liste simple de textes pour les boutons.
     * @param bool $oneTime Masquer le clavier apres usage.
     * @return bool Succes de l'envoi.
     */
    public function askWithReplyKeyboard(string $message, array $options, bool $oneTime = true): bool
    {
        if (!$this->chatId) {
            $this->discoverAndSaveId();
        }

        $keyboard = [];
        foreach ($options as $text) {
            $keyboard[] = [['text' => $text]];
        }

        try {
            $response = $this->client->post('sendMessage', [
                'json' => [
                    'chat_id'      => $this->chatId,
                    'text'         => $message,
                    'reply_markup' => [
                        'keyboard'          => $keyboard,
                        'resize_keyboard'   => true,
                        'one_time_keyboard' => $oneTime,
                    ],
                ],
            ]);
            $res = json_decode($response->getBody(), true);
            return $res['ok'] ?? false;
        } catch (GuzzleException $e) {
            error_log($e->getMessage());
            return false;
        }
    }

    /**
     * Envoie un message texte simple.
     * @param string $message
     * @return bool
     */
    public function ask(string $message): bool
    {
        if (!$this->chatId) {
            $this->discoverAndSaveId();
        }

        try {
            $response = $this->client->post('sendMessage', [
                'json' => [
                    'chat_id' => $this->chatId,
                    'text'    => $message,
                ],
            ]);
            $res = json_decode($response->getBody(), true);
            return $res['ok'] ?? false;
        } catch (GuzzleException $e) {
            error_log($e->getMessage());
            return false;
        }
    }

    /**
     * Boucle d'attente bloquante pour recuperer la prochaine reponse.
     * Gere les messages textes et les clics sur Inline Keyboards.
     * @param int $maxWait Temps maximum d'attente en secondes.
     * @return string|null La reponse ou null en cas de timeout.
     */
    public function waitForResponse(int $maxWait = 60): ?string
    {
        $startTime = time();
        $offset = $this->getNextOffset();

        while ((time() - $startTime) < $maxWait) {
            try {
                $response = $this->client->post('getUpdates', [
                    'json' => [
                        'offset' => $offset,
                        'timeout' => 10,
                    ],
                ]);
                $data = json_decode($response->getBody(), true);

                if (!empty($data['result'])) {
                    foreach ($data['result'] as $update) {
                        // Traitement message texte
                        if (isset($update['message']) && $update['message']['chat']['id'] == $this->chatId) {
                            return $update['message']['text'] ?? null;
                        }

                        // Traitement clic bouton Inline
                        if (isset($update['callback_query']) && $update['callback_query']['message']['chat']['id'] == $this->chatId) {
                            $this->client->post('answerCallbackQuery', [
                                'json' => ['callback_query_id' => $update['callback_query']['id']],
                            ]);
                            return $update['callback_query']['data'];
                        }

                        $offset = $update['update_id'] + 1;
                    }
                }
            } catch (GuzzleException $e) {
            }
            usleep(500000);
        }
        return null;
    }

    /**
     * Ecoute les messages entrants pour identifier l'ID via un mot-cle et le sauve en config.
     * @return int L'ID decouvert.
     */
    public function discoverAndSaveId(): int
    {
        $keyword = $this->config['telegram']['settings']['discovering_keyword'];
        echo "[-] Attente du mot-cle '" . $keyword . "'...\n";

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

    /**
     * Recupere l'ID du prochain update pour eviter de traiter d'anciens messages.
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
     * Persiste l'ID utilisateur dans le fichier de configuration JSON.
     */
    private function saveIdToConfig(int $id): void
    {
        $this->config['telegram']['chat_id'] = $id;
        file_put_contents($this->configPath, json_encode($this->config, JSON_PRETTY_PRINT));
    }
}

