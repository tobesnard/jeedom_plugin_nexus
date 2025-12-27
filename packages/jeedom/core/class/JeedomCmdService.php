<?php

namespace Nexus\Jeedom;

require_once __DIR__ . '/../../../../vendor/autoload.php';

use Exception;
use InvalidArgumentException;
use RuntimeException;

/**
 * Service de gestion des commandes Jeedom
 *
 * Implémentation concrète de l'interface ICmdService pour l'intégration
 * avec l'écosystème Jeedom. Gère l'exécution des commandes et événements
 * avec cache, validation et journalisation.
 *
 * @author  Tony <tobesnard@gmail.com>
 * @since   1.0.0
 */
class JeedomCmdService implements ICmdService
{
    /** @var array Cache des résultats de commandes */
    private array $cache = [];

    /** @var int Durée de vie du cache en secondes */
    private int $cacheTimeout = 300; // 5 minutes

    /** @var array Statistiques d'exécution */
    private array $stats = [
        'executions' => 0,
        'cache_hits' => 0,
        'errors' => 0,
    ];

    /**
     * Exécute une commande par sa chaîne de caractères
     *
     * @param string $cmdString La chaîne de commande à exécuter
     * @param array $options Options d'exécution
     *
     * @return mixed Le résultat de l'exécution
     *
     * @throws InvalidArgumentException Si la commande est invalide
     * @throws RuntimeException Si l'exécution échoue
     */
    public function execByString(string $cmdString, array $options = [])
    {
        $this->validateCmdString($cmdString);
        $this->stats['executions']++;

        // Vérification du cache
        $cacheKey = $this->generateCacheKey('string', $cmdString, $options);
        if ($this->isCacheValid($cacheKey)) {
            $this->stats['cache_hits']++;

            return $this->cache[$cacheKey]['value'];
        }

        try {
            $cmd = \cmd::byString($cmdString);

            if (! is_object($cmd)) {
                throw new RuntimeException("Commande introuvable: {$cmdString}");
            }

            $result = $cmd->execCmd($options);
            $this->setCacheValue($cacheKey, $result);
            $this->logExecution('string', $cmdString, $options, $result, true);

            return $result;

        } catch (Exception $e) {
            $this->stats['errors']++;
            $this->handleException('execByString', $cmdString, $e);

            return null;
        }
    }

    /**
     * Exécute une commande par son identifiant
     *
     * @param int $id L'identifiant de la commande
     * @param array $options Options d'exécution
     *
     * @return mixed Le résultat de l'exécution
     *
     * @throws InvalidArgumentException Si l'ID est invalide
     * @throws RuntimeException Si l'exécution échoue
     */
    public function execById(int $id, array $options = [])
    {
        $this->validateCmdId($id);
        $this->stats['executions']++;

        // Vérification du cache
        $cacheKey = $this->generateCacheKey('id', (string) $id, $options);
        if ($this->isCacheValid($cacheKey)) {
            $this->stats['cache_hits']++;

            return $this->cache[$cacheKey]['value'];
        }

        try {
            $cmd = \cmd::byId($id);

            if (! is_object($cmd)) {
                throw new RuntimeException("Commande introuvable avec l'ID: {$id}");
            }

            $result = $cmd->execCmd($options);
            $this->setCacheValue($cacheKey, $result);
            $this->logExecution('id', (string) $id, $options, $result, true);

            return $result;

        } catch (Exception $e) {
            $this->stats['errors']++;
            $this->handleException('execById', (string) $id, $e);

            return null;
        }
    }

    /**
     * Déclenche un événement par sa chaîne de caractères
     *
     * @param string $cmd La chaîne de l'événement
     * @param mixed $value La valeur associée
     *
     * @return bool True si succès
     */
    public function eventByString(string $cmdString, $value): bool
    {
        $this->validateCmdString($cmdString);
        $this->stats['executions']++;

        // Vérification du cache
        $cacheKey = $this->generateCacheKey('string', $cmdString, [$value]);
        if ($this->isCacheValid($cacheKey)) {
            $this->stats['cache_hits']++;

            return $this->cache[$cacheKey]['value'];
        }

        try {
            $cmd = \cmd::byString($cmdString);

            if (! is_object($cmd)) {
                throw new RuntimeException("Commande introuvable: {$cmdString}");
            }

            $result = $cmd->event($value);
            $this->setCacheValue($cacheKey, $result);
            $this->logExecution('string', $cmdString, [$value], $result, true);

            return true;

        } catch (Exception $e) {
            $this->stats['errors']++;
            $this->handleException('eventByString', $cmdString, $e);

            return false;
        }
    }

    /**
     * Déclenche un événement par son identifiant
     *
     * @param string $cmd L'identifiant de l'événement
     * @param mixed $value La valeur associée
     *
     * @return bool True si succès
     */
    public function eventById(int $id, $value): bool
    {
        $this->validateCmdId($id);
        $this->stats['executions']++;

        // Vérification du cache
        $cacheKey = $this->generateCacheKey('id', (string) $id, [$value]);
        if ($this->isCacheValid($cacheKey)) {
            $this->stats['cache_hits']++;

            return $this->cache[$cacheKey]['value'];
        }

        try {
            $cmd = \cmd::byId($id);

            if (! is_object($cmd)) {
                throw new RuntimeException("Commande introuvable avec l'ID: {$id}");
            }

            $result = $cmd->event($value);
            $this->setCacheValue($cacheKey, $result);
            $this->logExecution('id', (string) $id, [$value], $result, true);

            return true;

        } catch (Exception $e) {
            $this->stats['errors']++;
            $this->handleException('eventById', (string) $id, $e);

            return false;
        }
    }

    public function log($logMessage): bool
    {
        \log::add('nexus', 'INFO', $logMessage);

        return true;
    }


    /**
     * Valide une chaîne de commande
     *
     * @param string $cmdString La chaîne à valider
     *
     * @throws InvalidArgumentException Si invalide
     */
    private function validateCmdString(string $cmdString): void
    {
        if (empty(trim($cmdString))) {
            throw new InvalidArgumentException('La chaîne de commande ne peut pas être vide');
        }

        if (strlen($cmdString) > 255) {
            throw new InvalidArgumentException('La chaîne de commande est trop longue (max: 255 caractères)');
        }
    }

    /**
     * Valide un identifiant de commande
     *
     * @param int $id L'ID à valider
     *
     * @throws InvalidArgumentException Si invalide
     */
    private function validateCmdId(int $id): void
    {
        if ($id <= 0) {
            throw new InvalidArgumentException('L\'identifiant de commande doit être positif');
        }
    }

    /**
     * Génère une clé de cache
     *
     * @param string $type Type de commande ('string' ou 'id')
     * @param string $identifier Identifiant
     * @param array $options Options
     *
     * @return string La clé de cache
     */
    private function generateCacheKey(string $type, string $identifier, array $options): string
    {
        $optionsHash = md5(serialize($options));

        return "cmd_{$type}_{$identifier}_{$optionsHash}";
    }

    /**
     * Vérifie si le cache est valide
     *
     * @param string $cacheKey La clé de cache
     *
     * @return bool True si valide
     */
    private function isCacheValid(string $cacheKey): bool
    {
        if (! isset($this->cache[$cacheKey])) {
            return false;
        }

        $entry = $this->cache[$cacheKey];

        return (time() - $entry['timestamp']) < $this->cacheTimeout;
    }

    /**
     * Met en cache une valeur
     *
     * @param string $cacheKey La clé de cache
     * @param mixed $value La valeur
     */
    private function setCacheValue(string $cacheKey, $value): void
    {
        $this->cache[$cacheKey] = [
            'value' => $value,
            'timestamp' => time(),
        ];
    }

    /**
     * Gère les exceptions de manière centralisée
     *
     * @param string $method La méthode où l'exception s'est produite
     * @param string $identifier L'identifiant concerné
     * @param Exception $e L'exception
     */
    private function handleException(string $method, string $identifier, Exception $e): void
    {
        $message = "[Nexus::Jeedom::{$method}] Erreur pour '{$identifier}': {$e->getMessage()}";

        if (function_exists('\\log::add')) {
            \log::add('Edom', 'ERROR', $message);
        }

        if (function_exists('\\message::add')) {
            \message::add('Edom', $message);
        }

        // En mode debug, on peut aussi logger la stack trace
        // error_log($message . "\nStack trace: " . $e->getTraceAsString());
        error_log($message);
    }

    /**
     * Journalise l'exécution d'une commande
     *
     * @param string $type Type de commande
     * @param string $identifier Identifiant
     * @param array $options Options
     * @param mixed $result Résultat
     * @param bool $success Succès ou échec
     */
    private function logExecution(string $type, string $identifier, array $options, $result, bool $success): void
    {
        if (function_exists('\\log::add')) {
            $status = $success ? 'SUCCESS' : 'FAILED';
            $message = "[Interpreteur] Exécution {$type} '{$identifier}' [{$status}]";

            if (! empty($options)) {
                $message .= " Options: " . json_encode($options);
            }

            \log::add('Edom', 'DEBUG', $message);
        }
    }

    /**
     * Journalise un événement
     *
     * @param string $type Type d'événement
     * @param string $identifier Identifiant
     * @param mixed $value Valeur
     */
    private function logEvent(string $type, string $identifier, $value): void
    {
        if (function_exists('\\log::add')) {
            $message = "[Interpreteur] Événement {$type} '{$identifier}' déclenché";

            if ($value !== null) {
                $message .= " avec valeur: " . json_encode($value);
            }

            \log::add('Edom', 'INFO', $message);
        }
    }

    /**
     * Retourne les statistiques d'utilisation
     *
     * @return array Les statistiques
     */
    public function getStats(): array
    {
        return array_merge($this->stats, [
            'cache_entries' => count($this->cache),
            'cache_hit_ratio' => $this->stats['executions'] > 0
                ? round(($this->stats['cache_hits'] / $this->stats['executions']) * 100, 2)
                : 0,
        ]);
    }

    /**
     * Vide le cache
     *
     * @return self Pour chaînage fluide
     */
    public function clearCache(): self
    {
        $this->cache = [];

        return $this;
    }

    /**
     * Définit la durée de vie du cache
     *
     * @param int $seconds Durée en secondes
     *
     * @return self Pour chaînage fluide
     */
    public function setCacheTimeout(int $seconds): self
    {
        $this->cacheTimeout = max(0, $seconds);

        return $this;
    }
}
