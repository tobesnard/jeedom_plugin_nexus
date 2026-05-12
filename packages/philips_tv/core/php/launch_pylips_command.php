<?php

// Permet de lancer un script python en utilisant une version indépendante de Jeedom
// La version python de Jeedom ne correspond pas avec certaines dépendance
// usage: php script_launcher.php nom_du_script.py

$script = __DIR__ . '/../../pylips/pylips.py';
$command = $argv[1]; // Récupère le nom du script à lancer

$host = getenv('PHILIPS_TV_IP');
$user = getenv('PHILIPS_TV_USERNAME');
$pass = getenv('PHILIPS_TV_PASSWORD');

$commande = escapeshellcmd("/home/jeedom/.pyenv/shims/python3 $script --host $host --user $user --pass $pass --command $command");
$output = shell_exec($commande);

echo $commande;
