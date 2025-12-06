<?php

// Permet de lancer un script python en utilisant une version indépendante de Jeedom
// La version python de Jeedom ne correspond pas avec certaines dépendance
// usage: php script_launcher.php nom_du_script.py

$script = '/var/www/html/data/php/philipsTV/pylips/pylips.py';
$command = $argv[1]; // Récupère le nom du script à lancer

$commande = escapeshellcmd("/home/jeedom/.pyenv/shims/python3 $script --command $command");
$output = shell_exec($commande);

echo $commande;
