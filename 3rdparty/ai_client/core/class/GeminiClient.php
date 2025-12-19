<?php

namespace Nexus\AI\AIClient;

use Nexus\AI\AIClient\Abstracts\BaseAIClient;

class GeminiClient extends BaseAIClient
{
    // protected string $apiKey;
    // protected string $apiUrl = "https://generativelanguage.googleapis.com/v1beta";
    // protected string $model;

    public function __construct()
    {
        parent::__construct('gemini');
        // $this->model = (substr($model, 0, 7) === "models/") ? $model : "models/" . $model;
    }

    /**
     * Liste les modèles disponibles pour vérifier les droits de la clé API
     */
    public function listAvailableModels(): array
    {
        $url = "{$this->apiUrl}/models?key={$this->apiKey}";
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);

        $response = curl_exec($ch);
        curl_close($ch);

        return json_decode($response, true) ?? [];
    }

    public function query(string $prompt): string
    {
        $endpoint = "{$this->apiUrl}/{$this->model}:generateContent?key={$this->apiKey}";

        $payload = [
            "contents" => [
                ["parts" => [["text" => $prompt]]],
            ],
            "generationConfig" => [
                "temperature" => 0.7,
                "maxOutputTokens" => 2048,
            ],
        ];

        return $this->execute($endpoint, $payload);
    }

    private function execute(string $url, array $payload): string
    {
        $ch = curl_init($url);

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_TIMEOUT => 30,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if (curl_errno($ch)) {
            $error = curl_error($ch);
            curl_close($ch);
            return "Erreur cURL : $error";
        }

        curl_close($ch);
        $decoded = json_decode($response, true);

        if ($httpCode === 429) {
            return "Erreur 429 : Quota dépassé. Trop de requêtes par minute ou par jour.";
        }

        if ($httpCode !== 200) {
            return "Erreur API ({$httpCode}) : " . ($decoded['error']['message'] ?? 'Réponse inconnue');
        }
        return $decoded['candidates'][0]['content']['parts'][0]['text'] ?? "Aucune réponse.";
    }
}
