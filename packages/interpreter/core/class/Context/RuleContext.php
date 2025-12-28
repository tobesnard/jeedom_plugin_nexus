<?php

namespace Nexus\Interpreter\Context;

use Nexus\Interpreter\Application\Services\ICmdService;
use InvalidArgumentException;

/**
 * Classe RuleContext (Context du GoF)
 *
 * Gère l'état (données variables) et les opérations d'exécution (événements).
 * Fournit un contexte d'exécution riche avec gestion des variables, historique
 * des événements, et capacités de débogage.
 *
 * @author  Tony <tobesnard@gmail.com>
 * @since   1.0.0
 */
class RuleContext
{
    /** @var array Données/variables stockées dans le contexte */
    private array $data = [];

    /** @var array Historique des événements déclenchés */
    private array $triggeredEvents = [];

    /** @var array Pile d'exécution pour le débogage */
    private array $executionStack = [];

    /** @var array Historique des modifications de variables */
    private array $variableHistory = [];

    /** @var bool Mode débogage activé */
    private bool $debugMode = false;

    /** @var float Timestamp de création du contexte */
    private float $createdAt;

    /** @var ICmdService|null Service de commande pour exécution */
    private ?ICmdService $cmdService = null;

    /**
     * Constructeur du contexte
     *
     * @param bool $debugMode Active le mode débogage si true
     * @param ICmdService|null $cmdService Service de commande optionnel
     */
    public function __construct(bool $debugMode = false, ?ICmdService $cmdService = null)
    {
        $this->debugMode = $debugMode;
        $this->cmdService = $cmdService;
        $this->createdAt = microtime(true);
    }

    // --- Gestion des Données (Variables) ---

    /**
     * Obtient le service de commande
     *
     * @return ICmdService|null
     */
    public function getCmdService(): ?ICmdService
    {
        return $this->cmdService;
    }

    /**
     * Définit le service de commande
     *
     * @param ICmdService $cmdService
     * @return self
     */
    public function setCmdService(ICmdService $cmdService): self
    {
        $this->cmdService = $cmdService;

        return $this;
    }

    /**
     * Définit une variable dans le contexte
     *
     * @param string $key Clé de la variable
     * @param mixed $value Valeur à assigner
     *
     * @throws InvalidArgumentException Si la clé est invalide
     *
     * @return self Pour chaînage fluide
     */
    public function set(string $key, $value): self
    {
        $this->validateKey($key);

        // Nettoyage de la clé : suppression des caractères spéciaux
        $cleanKey = $this->cleanKey($key);

        // Sauvegarde de l'ancienne valeur pour l'historique
        if ($this->debugMode && isset($this->data[$cleanKey])) {
            $this->variableHistory[] = [
                'key' => $cleanKey,
                'old_value' => $this->data[$cleanKey],
                'new_value' => $value,
                'timestamp' => microtime(true),
                'action' => 'update',
            ];
        }

        $this->data[$cleanKey] = $value;

        if ($this->debugMode) {
            $this->log("Variable '{$cleanKey}' définie avec la valeur: " . $this->formatValue($value));
        }

        return $this;
    }

    /**
     * Récupère une variable du contexte
     *
     * @param string $key Clé de la variable
     * @param mixed $default Valeur par défaut si non trouvée
     *
     * @return mixed La valeur de la variable ou la valeur par défaut
     *
     * @throws InvalidArgumentException Si la clé est invalide
     */
    public function get(string $key, $default = null)
    {
        $this->validateKey($key);

        // Nettoyage de la clé
        $cleanKey = $this->cleanKey($key);

        $value = $this->data[$cleanKey] ?? $default;

        // Conversion de type si nécessaire
        $convertedValue = $this->convertType($value);

        if ($this->debugMode && $value !== $default) {
            $this->log("Variable '{$cleanKey}' récupérée: " . $this->formatValue($convertedValue));
        }

        return $convertedValue;
    }

    /**
     * Vérifie si une variable existe
     *
     * @param string $key Clé à vérifier
     *
     * @return bool True si la variable existe
     */
    public function has(string $key): bool
    {
        $cleanKey = $this->cleanKey($key);

        return array_key_exists($cleanKey, $this->data);
    }

    /**
     * Supprime une variable
     *
     * @param string $key Clé à supprimer
     *
     * @return self Pour chaînage fluide
     */
    public function remove(string $key): self
    {
        $cleanKey = $this->cleanKey($key);

        if (isset($this->data[$cleanKey])) {
            if ($this->debugMode) {
                $this->variableHistory[] = [
                    'key' => $cleanKey,
                    'old_value' => $this->data[$cleanKey],
                    'new_value' => null,
                    'timestamp' => microtime(true),
                    'action' => 'remove',
                ];
            }

            unset($this->data[$cleanKey]);

            if ($this->debugMode) {
                $this->log("Variable '{$cleanKey}' supprimée");
            }
        }

        return $this;
    }

    /**
     * Retourne toutes les variables
     *
     * @return array Tableau associatif des variables
     */
    public function getAllData(): array
    {
        return $this->data;
    }

    /**
     * Vide toutes les variables
     *
     * @return self Pour chaînage fluide
     */
    public function clearData(): self
    {
        if ($this->debugMode && ! empty($this->data)) {
            $this->log("Suppression de " . count($this->data) . " variable(s)");
        }

        $this->data = [];

        return $this;
    }

    // --- Gestion des Opérations (Événements) ---

    /**
     * Déclenche un événement
     *
     * @param string $eventKey Clé de l'événement
     * @param mixed $data Données associées à l'événement
     *
     * @return self Pour chaînage fluide
     */
    public function triggerEvent(string $eventKey, $data = null): self
    {
        // Nettoyage de l'événement: retire les dièses et les guillemets simples
        $eventName = trim($eventKey, "#'");

        if (empty($eventName)) {
            throw new InvalidArgumentException('Le nom de l\'événement ne peut pas être vide');
        }

        $event = [
            'name' => $eventName,
            'data' => $data,
            'timestamp' => microtime(true),
            'context_age' => microtime(true) - $this->createdAt,
        ];

        $this->triggeredEvents[] = $event;

        $message = "Événement déclenché : " . $eventName;
        if ($data !== null) {
            $message .= " avec données : " . $this->formatValue($data);
        }

        $this->log($message);
        echo "--> [EVENT] {$message}\n";

        return $this;
    }

    /**
     * Retourne l'historique des événements
     *
     * @return array Liste des événements déclenchés
     */
    public function getTriggeredEvents(): array
    {
        return $this->triggeredEvents;
    }

    /**
     * Compte le nombre d'événements déclenchés
     *
     * @return int Nombre d'événements
     */
    public function getEventCount(): int
    {
        return count($this->triggeredEvents);
    }

    /**
     * Vérifie si un événement spécifique a été déclenché
     *
     * @param string $eventName Nom de l'événement
     *
     * @return bool True si l'événement a été déclenché
     */
    public function wasEventTriggered(string $eventName): bool
    {
        foreach ($this->triggeredEvents as $event) {
            if ($event['name'] === $eventName) {
                return true;
            }
        }

        return false;
    }

    // --- Gestion du Debug et de l'Exécution ---

    /**
     * Ajoute une entrée à la pile d'exécution
     *
     * @param string $operation Description de l'opération
     * @param array $context Contexte additionnel
     *
     * @return self Pour chaînage fluide
     */
    public function pushExecution(string $operation, array $context = []): self
    {
        if ($this->debugMode) {
            $this->executionStack[] = [
                'operation' => $operation,
                'context' => $context,
                'timestamp' => microtime(true),
                'variables_count' => count($this->data),
                'events_count' => count($this->triggeredEvents),
            ];
        }

        return $this;
    }

    /**
     * Retire la dernière entrée de la pile d'exécution
     *
     * @return array|null L'entrée retirée ou null
     */
    public function popExecution(): ?array
    {
        return array_pop($this->executionStack);
    }

    /**
     * Retourne le log d'exécution complet
     *
     * @return array Log d'exécution formaté
     */
    public function getExecutionLog(): array
    {
        $log = [];

        // Ajout des messages d'événements (compatibilité)
        foreach ($this->triggeredEvents as $event) {
            $log[] = "Événement déclenché : " . $event['name'];
        }

        return $log;
    }

    /**
     * Retourne un rapport détaillé du contexte
     *
     * @return array Rapport complet
     */
    public function getDetailedReport(): array
    {
        return [
            'created_at' => $this->createdAt,
            'execution_time' => microtime(true) - $this->createdAt,
            'debug_mode' => $this->debugMode,
            'variables' => [
                'count' => count($this->data),
                'data' => $this->data,
            ],
            'events' => [
                'count' => count($this->triggeredEvents),
                'list' => $this->triggeredEvents,
            ],
            'execution_stack' => $this->executionStack,
            'variable_history' => $this->variableHistory,
        ];
    }

    // --- Méthodes privées utilitaires ---

    /**
     * Nettoie une clé en supprimant les caractères spéciaux
     *
     * @param string $key Clé à nettoyer
     *
     * @return string Clé nettoyée
     */
    private function cleanKey(string $key): string
    {
        return trim($key, "#'");
    }

    /**
     * Valide une clé
     *
     * @param string $key Clé à valider
     *
     * @throws InvalidArgumentException Si invalide
     */
    private function validateKey(string $key): void
    {
        if (empty(trim($key, "#'"))) {
            throw new InvalidArgumentException('La clé ne peut pas être vide');
        }
    }

    /**
     * Convertit automatiquement le type d'une valeur
     *
     * @param mixed $value Valeur à convertir
     *
     * @return mixed Valeur convertie
     */
    private function convertType($value)
    {
        // Si c'est déjà un type primitif, pas de conversion
        if (is_bool($value) || is_int($value) || is_float($value) || is_null($value)) {
            return $value;
        }

        // Conversion des chaînes
        if (is_string($value)) {
            $trimmed = trim($value);

            // Booléens
            if (in_array(strtolower($trimmed), ['true', 'false'])) {
                return strtolower($trimmed) === 'true';
            }

            // Nombres
            if (is_numeric($trimmed)) {
                return strpos($trimmed, '.') !== false ? (float) $trimmed : (int) $trimmed;
            }
        }

        return $value;
    }

    /**
     * Formate une valeur pour l'affichage
     *
     * @param mixed $value Valeur à formater
     *
     * @return string Valeur formatée
     */
    private function formatValue($value): string
    {
        if (is_null($value)) {
            return 'null';
        }
        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }
        if (is_array($value)) {
            return 'Array[' . count($value) . ']';
        }
        if (is_object($value)) {
            return 'Object(' . get_class($value) . ')';
        }

        return (string) $value;
    }

    /**
     * Ajoute un message au log de débogage
     *
     * @param string $message Message à logger
     */
    private function log(string $message): void
    {
        if ($this->debugMode) {
            $timestamp = date('Y-m-d H:i:s', (int) microtime(true));
            error_log("[RuleContext {$timestamp}] {$message}");
        }
    }
}
