<?php

namespace Nexus\Interpreter\Application\Services;

use Nexus\Jeedom\Services\JeedomCmdService;
use Nexus\Jeedom\Services\JeedomLogService;

/**
 * Classe JeedomService - Nexus Framework
 *
 * Proxy applicatif centralisant l'accès aux services Jeedom.
 *
 * @author Tony <tobesnard@gmail.com>
 */
class JeedomService implements ICmdService
{
    /** @var self|null */
    private static $instance = null;

    private function __construct() {}

    private function __clone() {}

    /**
     * @return self
     */
    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * @inheritDoc
     */
    public function execByString(string $cmd, array $options = [])
    {
        return JeedomCmdService::getInstance()->execByString($cmd, $options);
    }

    /**
     * @inheritDoc
     */
    public function execById(int $id, array $options = [])
    {
        return JeedomCmdService::getInstance()->execById($id, $options);
    }

    /**
     * @inheritDoc
     */
    public function eventByString(string $cmd, $value): bool
    {
        return JeedomCmdService::getInstance()->eventByString($cmd, $value);
    }

    /**
     * @inheritDoc
     */
    public function eventById(int $id, $value): bool
    {
        return JeedomCmdService::getInstance()->eventById($id, $value);
    }

    /**
     * @inheritDoc
     */
    public function log(string $logMessage, string $level = 'info'): bool
    {
        JeedomLogService::getInstance()->log($logMessage, $level);
        return true;
    }

    /**
     * @return JeedomLogService
     */
    public function getLogger(): JeedomLogService
    {
        return JeedomLogService::getInstance();
    }

    /**
     * @return array
     */
    public function getStats(): array
    {
        return JeedomCmdService::getInstance()->getStats();
    }
}
