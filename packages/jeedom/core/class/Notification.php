<?php

namespace Nexus\Jeedom;

require_once __DIR__ . "/../../../../vendor/autoload.php";

use Nexus\Utils\Helpers;

class Notification
{
    private static $configCache = null;

    private static function getConfigFile(): string
    {
        return __DIR__ . '/../config/config.json';
    }

    private static function get(string $key, $default = null)
    {
        if (self::$configCache === null) {
            $configPath = self::getConfigFile();

            if (!is_readable($configPath)) {
                throw new \RuntimeException("Fichier de configuration illisible : " . $configPath);
            }

            self::$configCache = json_decode(file_get_contents($configPath), true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \RuntimeException("Erreur JSON : " . json_last_error_msg());
            }
        }

        return self::$configCache[$key] ?? $default;
    }

    public static function emergencyThreadNotification(string $message): bool
    {
        if (empty($message)) {
            return false;
        }

        return Helpers::execute(function () use ($message) {
            $cmdString = self::get('emergency_thread_notification');

            if (!$cmdString) {
                return false;
            }

            $cmd = \cmd::byString($cmdString);
            if (is_object($cmd)) {
                $cmd->execCmd(['message' => $message]);
                return true;
            }

            return false;
        }, false);
    }
}
