<?php

namespace Nexus\AI\AIClient;

use Nexus\AI\AIClient\Abstracts\BaseAIClient;

/**
 * Client pour GitHub Copilot (via GitHub Models API)
 * Compatible avec les tokens ghp_ (PAT) et ghu_ (Copilot User)
 */
class CopilotClient extends BaseAIClient
{
    public function __construct()
    {
        // Charge la section "copilot" du fichier config.json
        parent::__construct('copilot');
    }

    /**
     * Exécute une requête vers l'API d'inférence de GitHub
     */
    public function query(string $prompt): string
    {
        $payload = [
            "model" => $this->model,
            "messages" => [
                [
                    "role" => "user",
                    "content" => $prompt,
                ],
            ],
            "temperature" => $this->config['temperature'] ?? 0.7,
            "max_tokens" => $this->config['max_tokens'] ?? 1024,
        ];

        return $this->execute($this->apiUrl, $payload);
    }

    /**
     * Logique d'exécution cURL optimisée pour GitHub
     */
    private function execute(string $url, array $payload): string
    {
        $ch = curl_init($url);

        $headers = [
            "Content-Type: application/json",
            "Authorization: Bearer {$this->apiKey}",
            "User-Agent: Nexus-AI-Client", // Obligatoire pour GitHub
        ];

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_TIMEOUT => 30,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if (curl_errno($ch)) {
            $error = curl_error($ch);
            curl_close($ch);
            return "Erreur cURL (Copilot) : $error";
        }

        curl_close($ch);
        $decoded = json_decode($response, true);

        if ($httpCode !== 200) {
            $errorMsg = $decoded['error']['message'] ?? $decoded['message'] ?? 'Erreur inconnue';
            return "Erreur API Copilot ({$httpCode}) : {$errorMsg}";
        }

        return $decoded['choices'][0]['message']['content'] ?? "Réponse vide.";
    }
}
