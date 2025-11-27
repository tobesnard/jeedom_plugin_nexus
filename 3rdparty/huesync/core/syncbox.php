<?php

// Permet de lancer un script python en utilisant une version indépendante de Jeedom
// La version python de Jeedom ne correspond pas avec certaines dépendance
// usage: php syncbox.php commande_name [arg]

$baseDir = '/var/www/html/data/php/HueSync/src/';
$script = $baseDir . 'syncbox.py';                  // Le script principal qui gère les commandes
$configFile = $baseDir . 'syncbox_config.json';
$type = $argv[1];
$commandName = $argv[2];                          // Récupère le nom de la commande python à lancer
// $arg = $argv[2] ?? null;                              // Récupère un argument optionnel

$commande = escapeshellcmd("/home/jeedom/.pyenv/shims/python3 $script --config $configFile --type $type --command $commandName ");
$output = shell_exec($commande);

echo $output;
