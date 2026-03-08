<?php

namespace Nexus\AI\AIClient\Abstracts;

use Exception;

abstract class BaseAIClient
{
    protected array $config;
    protected string $apiKey;
    protected string $model;
    protected string $apiUrl;

    public function __construct(string $providerName)
    {
        $this->loadConfiguration($providerName);
    }

    /**
     * Charge la configuration spécifique depuis le JSON
     */
    private function loadConfiguration(string $provider): void
    {
        $configPath = __DIR__ . '/../../config/config.json'; // Ajuste le path selon ton arborescence

        if (!file_exists($configPath)) {
            throw new Exception("Fichier de configuration introuvable.");
        }

        $content = file_get_contents($configPath);

        // Remplacement dynamique des variables d'environnement {{VAR}}
        $content = preg_replace_callback('/\{\{([^}]+)\}\}/', function ($matches) {
            $envValue = getenv($matches[1]);
            return $envValue !== false ? $envValue : $matches[0];
        }, $content);

        $json = json_decode($content, true);

        if (!isset($json['providers'][$provider])) {
            throw new Exception("Configuration manquante pour le provider : $provider");
        }

        $this->config = $json['providers'][$provider];
        $this->apiKey = $this->config['api_key'];
        $this->model  = $this->config['model'];
        $this->apiUrl = $this->config['api_url'];
    }

    /**
     * Méthode à implémenter par chaque client
     */
    abstract public function query(string $prompt): string;
}
