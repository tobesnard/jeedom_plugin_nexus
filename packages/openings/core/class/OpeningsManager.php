<?php

declare(strict_types=1);

namespace Nexus\Openings;

use Nexus\AI\AIClient\AIFactory;
use Nexus\Utils\CacheService;
use RuntimeException;
use Exception;

class OpeningsManager
{
    private const CONFIG_PATH = __DIR__ . '/../config/prompt.json';

    /**
     * Génère une synthèse humaine basée sur la configuration IA et les données fournies.
     * * @param array $houseData Structure complète de la maison (floors, rooms, openings)
     * @param int $cacheTTL Durée de vie du cache en secondes (défaut: 300s / 5min)
     * @return string Synthèse textuelle
     */
    public static function getStatusText(array $houseData, int $cacheTTL = 300): string
    {
        try {
            // Génération d'une clé de cache basée sur les données d'entrée
            $cache = CacheService::getInstance();
            $cacheKey = $cache->generateKey('openings_status', $houseData);

            // Vérification du cache
            $cachedResult = $cache->get($cacheKey);
            if ($cachedResult !== null) {
                return $cachedResult;
            }

            $config = self::loadConfig();

            // Extraction des instructions systèmes
            $systemInstructions = json_encode($config['prompt_configuration'], JSON_UNESCAPED_UNICODE);

            // Préparation des données d'entrée (User Context)
            $inputData = json_encode($houseData, JSON_UNESCAPED_UNICODE);

            $aiClient = AIFactory::create("copilot");

            // Formatage du prompt pour une séparation claire entre règles et données
            $prompt = "### SYSTEM_RULES\n{$systemInstructions}\n\n";
            $prompt .= "### INPUT_DATA_TO_SYNTHESIZE\n{$inputData}";

            $output = (string) $aiClient->query($prompt);

            // Supprime les caractères de contrôle ASCII (0 à 31 et 127)
            $output = preg_replace('/[\x00-\x1F\x7F]/', '', $output);

            // Mise en cache du résultat
            $cache->set($cacheKey, $output, $cacheTTL);

            return $output;
        } catch (Exception $e) {
            // En tant qu'expert, on prévoit un fallback en cas de fail API
            return "Erreur lors de la génération du statut : " . $e->getMessage();
        }
    }

    private static function loadConfig(): array
    {
        if (!file_exists(self::CONFIG_PATH)) {
            throw new RuntimeException("Fichier de configuration introuvable.");
        }

        $json = file_get_contents(self::CONFIG_PATH);
        return json_decode($json, true, 512, JSON_THROW_ON_ERROR);
    }
}
