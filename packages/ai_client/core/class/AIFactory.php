<?php

namespace Nexus\AI\AIClient;

use Nexus\AI\AIClient\Abstracts\BaseAIClient;
use Nexus\AI\AIClient\GeminiClient;
use Nexus\AI\AIClient\ChatGPTClient;
use Nexus\AI\AIClient\CopilotClient;
use Exception;

class AIFactory
{
    public static function create(string $provider): BaseAIClient
    {
        switch (strtolower($provider)) {
            case 'gemini':
                return new GeminiClient();
            case 'chatgpt':
                return new ChatGPTClient();
            case 'copilot':
                return new CopilotClient();
            default:
                throw new Exception("Provider AI non supporté : $provider");
        }
    }
}
