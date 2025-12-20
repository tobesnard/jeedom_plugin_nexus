<?php

require_once __DIR__ . '/../../../../vendor/autoload.php';

use Nexus\AI\AIClient\AIFactory;

/**
 * Méthode Proxy : Exécute une requête vers un service IA (Gemini par défaut)
 */
function aiclient_execute(string $prompt, string $provider = 'gemini'): string
{
    try {
        $aiClient = AIFactory::create($provider);
        return (string) $aiClient->query($prompt);
    } catch (Exception $e) {
        return "Erreur AI ({$provider}): " . $e->getMessage();
    }
}

/**
 * Méthode Proxy : Requête auprès de Gemini
 */
function aiclient_gemini(string $prompt): string
{
    return aiclient_execute($prompt, 'gemini');
}

/**
 * Méthode Proxy : Requête auprès de ChatGPT
 */
function aiclient_chatgpt(string $prompt): string
{
    return aiclient_execute($prompt, 'chatgpt');
}

/**
 * Méthode Proxy : Requête auprès de GitHub Copilot
 */
function aiclient_copilot(string $prompt): string
{
    return aiclient_execute($prompt, 'copilot');
}
