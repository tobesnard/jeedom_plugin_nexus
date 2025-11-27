<?php

namespace Interpreter\Application\Services;

/**
 * Service mock pour les commandes - Implémentation de test
 *
 * Cette classe fournit une implémentation mock de l'interface ICmdService
 * pour les tests et le développement. Elle simule le comportement
 * des commandes Jeedom avec des réponses prédéfinies.
 *
 * @author  Tony <tobesnard@gmail.com>
 * @since   1.0.0
 */
class CmdServiceMock implements ICmdService
{
    /** @var array Historique des commandes exécutées */
    private array $executionHistory = [];

    /** @var array Historique des événements déclenchés */
    private array $eventHistory = [];

    /**
     * {@inheritDoc}
     */
    public function execByString(string $cmd, array $options = [])
    {
        $this->logExecution('string', $cmd, $options);

        switch ($cmd) {
            // Règle 6 (comparaison)
            case '#[edom][edom][temperature]#':
                return 18.5; // Retourne float

                // Règle 14 (exécution avec arguments)
            case '#[edom][cmd][with args]#':
                $args = implode(' ', array_map(fn ($s) => "'$s'", $options));
                echo "\033[32m \n[EXEC] {$cmd} {$args}\033[0m\n";

                return true; // Retourne bool

                // Règle 9 (exécution simple)
            case '#[edom][cmd][on]#':
                echo "\033[32m \n[EXEC] {$cmd} \033[0m\n";

                return true; // Retourne bool

            case '#[edom][cmd][with args array]#':
                $stringify = json_encode($options);
                echo "\033[32m \n[EXEC] {$cmd} {$stringify}\033[0m\n";

                return true; // Retourne bool

            default:
                echo "\033[33m \n[MOCK] Commande inconnue: {$cmd}\033[0m\n";

                return false;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function execById(int $id, array $options = [])
    {
        $this->logExecution('id', (string)$id, $options);

        switch ($id) {
            // Règle 7 & 8 (comparaison)
            case 18:
                return 18.5; // Retourne float

            case 15137:
                return 'hello from 15137';

            case 132:
                $args = implode(' ', array_map(fn ($s) => "'$s'", $options));
                echo "\033[32m\n[EXEC] #{$id}# \033[0m\n";
                if (! empty($options)) {
                    echo "\033[32m\n[EXEC] Arguments reçus: {$args}\033[0m\n";
                }

                return true; // Retourne bool

            case 133:
                $stringify = json_encode($options);
                echo "\033[32m \n[EXEC] {$id} {$stringify} \033[0m\n";

                return true; // Retourne bool

            default:
                echo "\033[33m \n[MOCK] Commande ID inconnue: {$id}\033[0m\n";

                return false;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function eventByString(string $cmd, $value): bool
    {
        $this->logEvent('string', $cmd, $value);
        echo "\033[32m \n[EVENT] {$cmd} '" . $this->formatValue($value) . "' \033[0m\n";

        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function eventById(int $id, $value): bool
    {
        $this->logEvent('id', $id, $value);
        echo "\033[32m \n[EVENT] {$id} '" . $this->formatValue($value) . "' \033[0m\n";

        return true;
    }


    public function log(string $logMessage): bool
    {
        echo "\033[32m \n[LOG] {$logMessage} \033[0m\n";

        return true;
    }


    /**
     * Enregistre une exécution de commande dans l'historique
     *
     * @param string $type Type de commande ('string' ou 'id')
     * @param string $identifier Identifiant de la commande
     * @param array $options Options d'exécution
     */
    private function logExecution(string $type, string $identifier, array $options): void
    {
        $this->executionHistory[] = [
            'type' => $type,
            'identifier' => $identifier,
            'options' => $options,
            'timestamp' => microtime(true),
        ];
    }

    /**
     * Enregistre un événement dans l'historique
     *
     * @param string $type Type d'événement ('string' ou 'id')
     * @param string $cmd Commande de l'événement
     * @param mixed $value Valeur de l'événement
     */
    private function logEvent(string $type, string $cmd, $value): void
    {
        $this->eventHistory[] = [
            'type' => $type,
            'cmd' => $cmd,
            'value' => $value,
            'timestamp' => microtime(true),
        ];
    }

    /**
     * Formate une valeur pour l'affichage
     *
     * @param mixed $value La valeur à formater
     *
     * @return string La valeur formatée
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
            return json_encode($value);
        }
        if (is_object($value)) {
            return get_class($value) . '()';
        }

        return (string)$value;
    }

    /**
     * Retourne l'historique des exécutions
     *
     * @return array L'historique des commandes exécutées
     */
    public function getExecutionHistory(): array
    {
        return $this->executionHistory;
    }

    /**
     * Retourne l'historique des événements
     *
     * @return array L'historique des événements déclenchés
     */
    public function getEventHistory(): array
    {
        return $this->eventHistory;
    }

    /**
     * Vide l'historique des exécutions et événements
     *
     * @return self Pour chaînage fluide
     */
    public function clearHistory(): self
    {
        $this->executionHistory = [];
        $this->eventHistory = [];

        return $this;
    }

    /**
     * Retourne des statistiques d'utilisation
     *
     * @return array Statistiques d'usage du mock
     */
    public function getStats(): array
    {
        return [
            'executions' => count($this->executionHistory),
            'events' => count($this->eventHistory),
            'total_operations' => count($this->executionHistory) + count($this->eventHistory),
        ];
    }
}
