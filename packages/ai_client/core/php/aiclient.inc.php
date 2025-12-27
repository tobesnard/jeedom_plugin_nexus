<?php

require_once __DIR__ . '/../../../../vendor/autoload.php';
require_once "/var/www/html/core/php/core.inc.php";

use Nexus\AI\AIClient\AIFactory;
use Nexus\Utils\Helpers;

/**
 * Méthode Proxy : Exécute une requête vers un service IA (Gemini par défaut)
 */
function aiclient_query(string $prompt, string $provider = 'gemini'): string
{
    return Helpers::execute(function () use ($prompt, $provider) {
        $aiClient = AIFactory::create($provider);
        return (string) $aiClient->query($prompt);
    }, "Erreur AI ({$provider}) : consultez les logs nexus");
}

/**
 * Méthode Proxy : Requête auprès de Gemini
 */
function aiclient_gemini(string $prompt): string
{
    return Helpers::execute(function () use ($prompt) {
        return aiclient_query($prompt, 'gemini');
    });
}

/**
 * Méthode Proxy : Requête auprès de ChatGPT
 */
function aiclient_chatgpt(string $prompt): string
{
    return Helpers::execute(function () use ($prompt) {
        return aiclient_query($prompt, 'chatgpt');
    });
}

/**
 * Méthode Proxy : Requête auprès de GitHub Copilot
 */
function aiclient_copilot(string $prompt): string
{
    return Helpers::execute(function () use ($prompt) {
        return aiclient_query($prompt, 'copilot');
    });
}
