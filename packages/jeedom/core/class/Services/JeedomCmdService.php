<?php

namespace Nexus\Jeedom\Services;

use Nexus\Jeedom\Services\ICmdService;

use InvalidArgumentException;
use RuntimeException;
use Nexus\Utils\Helpers;
use Nexus\Utils\CacheService;
use Nexus\Jeedom\Services\JeedomLogService;

/**
 * Service de gestion des commandes Jeedom - Singleton
 * * Centralise l'exécution sécurisée et la mise en cache intelligente.
 * * @author Tony <tobesnard@gmail.com>
 * @since 1.0.0
 */
class JeedomCmdService implements ICmdService
{
    /** @var self|null Instance unique */
    private static $instance = null;

    /** @var CacheService Instance du cache */
    private CacheService $cacheProvider;

    /** @var int TTL par défaut (60s) pour les infos */
    private int $defaultInfoTimeout = 30;

    // --- Initialisation ---

    private function __construct()
    {
        $this->cacheProvider = CacheService::getInstance();
    }

    private function __clone() {}

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    // --- API Publique : Exécution ---

    /**
     * Exécute une commande via sa syntaxe #[Objet][Equipement][Commande]#
     */
    public function execByString(string $cmdString, array $options = [])
    {
        $this->validateCmdString($cmdString);

        return Helpers::execute(function () use ($cmdString, $options) {
            $cmd = \cmd::byString($cmdString);
            if (!is_object($cmd)) {
                throw new RuntimeException("Commande introuvable : $cmdString");
            }
            return $this->processExecution($cmd, $cmdString, $options);
        });
    }

    /**
     * Exécute une commande via son ID numérique
     */
    public function execById(int $id, array $options = [])
    {
        $this->validateCmdId($id);

        return Helpers::execute(function () use ($id, $options) {
            $cmd = \cmd::byId($id);
            if (!is_object($cmd)) {
                throw new RuntimeException("ID de commande introuvable : $id");
            }
            return $this->processExecution($cmd, (string) $id, $options);
        });
    }

    // --- API Publique : Événements ---

    /**
     * Met à jour la valeur d'une commande par string
     */
    public function eventByString(string $cmd, $value): bool
    {
        $this->validateCmdString($cmd);
        return (bool) Helpers::execute(function () use ($cmd, $value) {
            $jeedomCmd = \cmd::byString($cmd);
            if (is_object($jeedomCmd)) {
                $this->invalidateCache('info', $cmd);
                return $jeedomCmd->event($value);
            }
            return false;
        }, false);
    }

    /**
     * Met à jour la valeur d'une commande par ID
     */
    public function eventById(int $id, $value): bool
    {
        $this->validateCmdId($id);
        return (bool) Helpers::execute(function () use ($id, $value) {
            $jeedomCmd = \cmd::byId($id);
            if (is_object($jeedomCmd)) {
                $this->invalidateCache('info', (string) $id);
                return $jeedomCmd->event($value);
            }
            return false;
        }, false);
    }

    // --- Traitement Interne & Cache ---

    /**
     * Pivot d'exécution : gère la distinction entre Info (caché) et Action (direct)
     */
    private function processExecution($cmd, string $identifier, array $options)
    {
        $isInfo = ($cmd->getType() === 'info');
        $cacheKey = $this->cacheProvider->generateKey($cmd->getType(), $identifier, $options);

        // Lecture du cache si c'est une info
        if ($isInfo && $this->cacheProvider->isValid($cacheKey)) {
            return $this->cacheProvider->get($cacheKey);
        }

        // Exécution réelle
        $result = $cmd->execCmd($options);

        // Mise en cache si c'est une info
        if ($isInfo) {
            $this->cacheProvider->set($cacheKey, $result, $this->defaultInfoTimeout);
        }

        JeedomLogService::getInstance()->logExecution($cmd->getType(), $identifier, $options, true);

        return $result;
    }

    /**
     * Nettoyage du cache lors d'une modification de valeur
     */
    private function invalidateCache(string $type, string $id): void
    {
        $key = $this->cacheProvider->generateKey($type, $id, []);
        // Nécessite CacheService::delete($key)
    }

    // --- Validation ---

    private function validateCmdString(string $cmd): void
    {
        if (empty(trim($cmd))) {
            throw new InvalidArgumentException("La commande textuelle est vide.");
        }
    }

    private function validateCmdId(int $id): void
    {
        if ($id <= 0) {
            throw new InvalidArgumentException("L'ID doit être un entier positif.");
        }
    }
}
