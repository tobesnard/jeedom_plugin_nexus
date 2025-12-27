<?php

namespace Nexus\HueSync;

class Syncbox
{

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
        $baseDir = defined('HUESYNC_ROOT') ? HUESYNC_ROOT : JEEDOM_ROOT . '/plugins/nexus/packages/huesync';
        $python  = defined('PYTHON_EXECUTABLE')  ? PYTHON_EXECUTABLE  : '/home/jeedom/.pyenv/shims/python3';

        $scriptPath = $baseDir . '/core/syncbox.py';
        $configPath = $baseDir . '/core/config/syncbox_config.json';

        $commande = escapeshellcmd("$python $scriptPath --config $configPath --type $type --command $commandName");

        return shell_exec($commande);
    }
}
