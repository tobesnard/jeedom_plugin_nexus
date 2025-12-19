<?php

namespace Nexus\AI\AIClient;

use Nexus\AI\AIClient\Abstracts\BaseAIClient;

class ChatGPTClient extends BaseAIClient
{
    public function __construct()
    {
        parent::__construct("chatgpt");
    }

    public function query(string $prompt): string
    {
        $ch = curl_init("https://api.openai.com/v1/chat/completions");
        $payload = [
            "model" => $this->model,
            "messages" => [["role" => "user", "content" => $prompt]],
        ];

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                "Content-Type: application/json",
                "Authorization: Bearer {$this->apiKey}",
            ],
            CURLOPT_POSTFIELDS => json_encode($payload),
        ]);

        $response = json_decode(curl_exec($ch), true);
        curl_close($ch);

        return $response['choices'][0]['message']['content'] ?? "Erreur OpenAI";
    }
}
