<?php

namespace Nexus\HueSync;

class Syncbox
{
    private static $baseDir = '/var/www/html/plugins/nexus/packages/huesync/core/';
    private static $scriptFile = 'syncbox.py';                  // Le script principal qui gère les commandes
    private static $configFile = 'config/syncbox_config.json';


    public static function action(string $commandName)
    {
        return self::execute("action", $commandName);
    }

    public static function info(string $infoName)
    {
        return self::execute("info", $infoName);
    }

    public static function test()
    {
        return "hello from test";
    }

    private static function execute(string $type, string $commandName)
    {
        $configFilePath = self::$baseDir . self::$configFile;
        $scriptFilePath = self::$baseDir . self::$scriptFile;
        $commande = escapeshellcmd("/home/jeedom/.pyenv/shims/python3 $scriptFilePath --config $configFilePath --type $type --command $commandName ");
        return shell_exec($commande);
    }
}
