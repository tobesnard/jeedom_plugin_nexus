<?php

namespace Nexus\AI\AIClient\Abstracts;

use Exception;

abstract class BaseAIClient
{
    protected array $config;
    protected ?string $apiKey = null;
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

        $json = json_decode(file_get_contents($configPath), true);

        if (!isset($json['providers'][$provider])) {
            throw new Exception("Configuration manquante pour le provider : $provider");
        }

        $this->config = $json['providers'][$provider];
        $apiKey = $this->config['api_key'];
        // Support du format 'env:VARNAME' pour la clé API
            if (is_string($apiKey) && strpos($apiKey, 'env:') === 0) {
                $envVar = substr($apiKey, 4);
                $envValue = getenv($envVar) ?: ($_ENV[$envVar] ?? '');
                $this->apiKey = $envValue !== false ? $envValue : '';
            } else {
                $this->apiKey = $apiKey;
            }
        $this->model  = $this->config['model'];
        $this->apiUrl = $this->config['api_url'];
    }

    /**
     * Méthode à implémenter par chaque client
     */
    abstract public function query(string $prompt): string;
}
