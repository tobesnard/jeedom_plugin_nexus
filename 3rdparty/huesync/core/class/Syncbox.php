<?php

namespace Nexus;

class Syncbox
{
    private static $baseDir = '/var/www/html/plugins/nexus/3rdparty/huesync/core/';
    private static $scriptFile = 'syncbox.py';                  // Le script principal qui gère les commandes
    private static $configFile = 'syncbox_config.json';


    public static function action(string $commandName)
    {
        echo self::execute("action", $commandName);
    }

    public static function info(string $infoName)
    {
        $ret = self::execute("info", $infoName);
        echo $ret;
        return $ret;
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
