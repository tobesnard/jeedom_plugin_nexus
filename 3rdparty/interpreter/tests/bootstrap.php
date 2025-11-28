<?php

/**
 * Bootstrap pour les tests PHPUnit
 */

// Chargement de l'autoloader Composer
require_once __DIR__ . '/../vendor/autoload.php';

// Inclusion des stubs Jeedom pour les tests
require_once __DIR__ . '/../stubs/jeedom.stub';

// Configuration des erreurs pour les tests
error_reporting(E_ALL);
ini_set('display_errors', '1');

// Définition de constantes utiles pour les tests
if (! defined('JEEDOM_TEST_MODE')) {
    define('JEEDOM_TEST_MODE', true);
}

// Fonction helper pour les tests
function createTestContext(bool $debugMode = false): \Nexus\Interpreter\Context\RuleContext
{
    return new \Nexus\Interpreter\Context\RuleContext($debugMode);
}

function createMockCmdService(): \Nexus\Interpreter\Application\Services\ICmdService
{
    return new class () implements \Nexus\Interpreter\Application\Services\ICmdService {
        private array $commands = [];
        private array $events = [];

        public function execByString(string $cmd, array $options = [])
        {
            $this->commands[] = ['type' => 'string', 'cmd' => $cmd, 'options' => $options];

            return "result_for_{$cmd}";
        }

        public function execById(int $id, array $options = [])
        {
            $this->commands[] = ['type' => 'id', 'cmd' => $id, 'options' => $options];

            return "result_for_id_{$id}";
        }

        public function eventByString(string $cmd, $value): bool
        {
            $this->events[] = ['type' => 'string', 'cmd' => $cmd, 'value' => $value];

            return true;
        }

        public function eventById(string $cmd, $value): bool
        {
            $this->events[] = ['type' => 'id', 'cmd' => $cmd, 'value' => $value];

            return true;
        }

        public function getExecutedCommands(): array
        {
            return $this->commands;
        }

        public function getTriggeredEvents(): array
        {
            return $this->events;
        }

        public function reset(): void
        {
            $this->commands = [];
            $this->events = [];
        }
    };
}

