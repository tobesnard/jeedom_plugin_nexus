<?php

namespace Nexus\Hydrao;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

/**
 * Implémentation de l'API Hydrao Version 2 via Guzzle
 * Optimisée pour éviter l'erreur "Limit Exceeded" via persistance du token.
 */
class HydraoAPI
{
    private const BASE_URL = 'https://api.hydrao.com';

    private string $apiKey;
    private string $token = "";
    private Client $httpClient;
    private string $cacheFile;

    /**
     * @param string $apiKey Clé API fournie par Hydrao
     */
    public function __construct(string $apiKey)
    {
        $this->apiKey = $apiKey;

        // Emplacement du cache (répertoire temporaire système)
        $this->cacheFile = sys_get_temp_dir() . '/hydrao_token_' . md5($this->apiKey) . '.txt';

        $this->httpClient = new Client([
            'base_uri' => self::BASE_URL,
            'timeout'  => 10.0,
            'headers'  => [
                'x-api-key'    => $this->apiKey,
                'Content-Type' => 'application/json',
                'Accept'       => 'application/json',
            ],
        ]);

        // Chargement du token persistant au démarrage
        if (file_exists($this->cacheFile)) {
            $this->token = file_get_contents($this->cacheFile);
        }
    }

    /**
     * Authentification avec gestion de persistance (Doc Section 2.4)
     * Évite de consommer le quota horaire inutilement.
     */
    public function login(string $email, string $password, bool $force = false): bool
    {
        // 1. Si on a un token en cache, on vérifie s'il est encore valide
        if (!$force && !empty($this->token)) {
            try {
                // Un simple appel à /me permet de valider le token actuel
                $this->getUserMe();
                return true;
            } catch (\Exception $e) {
                // Token expiré ou invalide, on procède à une nouvelle session
            }
        }

        // 2. Appel à l'endpoint /sessions
        try {
            $data = $this->request('POST', '/sessions', [
                'json' => [
                    'email'    => $email,
                    'password' => $password,
                ],
            ], false);

            if (isset($data->access_token)) {
                $this->token = $data->access_token;
                // Sauvegarde pour les prochaines exécutions
                file_put_contents($this->cacheFile, $this->token);
                return true;
            }
        } catch (\Exception $e) {
            throw new \Exception("Auth failed: " . $e->getMessage());
        }

        return false;
    }

    public function getUserMe()
    {
        return $this->request('GET', '/users/me');
    }

    public function getShowerHeads(int $limit = 100)
    {
        return $this->request('GET', "/shower-heads", [
            'query' => ['Limit' => $limit],
        ]);
    }

    public function getEvents(string $uuid, string $type = 'liveShower')
    {
        return $this->request('GET', "/private-devices/$uuid/events/", [
            'query' => ['type' => $type],
        ]);
    }

    public function getMeters()
    {
        return $this->request('GET', '/meters');
    }

    public function getMeterHistory(string $uuid)
    {
        return $this->request('GET', "/meters/$uuid/history");
    }

    /**
     * Moteur de requête centralisé avec Guzzle
     */
    private function request(string $method, string $endpoint, array $options = [], bool $authRequired = true)
    {
        try {
            if ($authRequired) {
                if (empty($this->token)) {
                    throw new \Exception("Token manquant.");
                }
                $options['headers']['Authorization'] = "Bearer {$this->token}";
            }

            $response = $this->httpClient->request($method, $endpoint, $options);
            $body = (string) $response->getBody();

            // Correction spécifique du bug de double quotes mentionné dans le PDF
            $cleanBody = str_replace('""', '"', $body);

            return json_decode($cleanBody);

        } catch (GuzzleException $e) {
            $statusCode = $e->hasResponse() ? $e->getResponse()->getStatusCode() : 0;
            $errorBody = $e->hasResponse() ? (string) $e->getResponse()->getBody() : $e->getMessage();

            if ($statusCode === 429) {
                throw new \Exception("RATE LIMIT EXCEEDED: Trop de requêtes envoyées à l'API Hydrao.");
            }

            throw new \Exception("API Error [$statusCode]: " . $errorBody);
        }
    }

    
}
