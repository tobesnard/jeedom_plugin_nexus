<?php

/**
 * Test pour récupérer les capacités de la caméra Reolink
 */

$ip = "192.168.1.244";
$user = "admin";
$password = "L1mp3rm@n3nce";
$baseUrl = "http://{$ip}/cgi-bin/api.cgi";

echo "=== Récupération des capacités de la caméra ===\n\n";

// 1. Test GetAbility
echo "1. GetAbility (Liste des commandes supportées)...\n";
$url = $baseUrl . "?cmd=GetAbility&user={$user}&password={$password}";
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
$result = curl_exec($ch);
curl_close($ch);
echo $result . "\n\n";

// 2. Test GetMdState (État actuel de la détection de mouvement)
echo "2. GetMdState (État de la détection de mouvement)...\n";
$url = $baseUrl . "?cmd=GetMdState&user={$user}&password={$password}";
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
$result = curl_exec($ch);
curl_close($ch);
echo $result . "\n\n";

// 3. Test GetPush (État des notifications)
echo "3. GetPush (État des notifications push)...\n";
$url = $baseUrl . "?cmd=GetPush&user={$user}&password={$password}";
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
$result = curl_exec($ch);
curl_close($ch);
echo $result . "\n\n";

echo "=== Fin du test ===\n";
