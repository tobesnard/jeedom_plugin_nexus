<?php

require __DIR__ . '/../../vendor/autoload.php';

$configPath = __DIR__ . "/core/config/config.json";

if (!file_exists($configPath)) {
    exit;
}

$config = json_decode(file_get_contents($configPath), true);
// Recupere le chemin depuis la config ou utilise le defaut
$storageFile = $config['telegram']['settings']['storage_file'] ?? '/tmp/nexus/nexus_bot_last_response.txt';

$content = file_get_contents("php://input");
$update = json_decode($content, true);

if (!$update) {
    exit;
}

$responseData = null;

// Extraction du message texte
if (isset($update['message']['text'])) {
    $responseData = $update['message']['text'];
}
// Extraction des donnees de boutons (Inline Keyboard)
elseif (isset($update['callback_query']['data'])) {
    $responseData = $update['callback_query']['data'];

    // Accuse de reception pour Telegram
    $token = $config['telegram']['token'] ?? null;
    if ($token) {
        $cbId = $update['callback_query']['id'];
        @file_get_contents("https://api.telegram.org/bot$token/answerCallbackQuery?callback_query_id=$cbId");
    }
}

if ($responseData !== null) {
    // S'assurer que le dossier existe
    $dir = dirname($storageFile);
    if (!is_dir($dir)) {
        mkdir($dir, 0777, true);
    }

    // Ecriture avec verrouillage et droits universels
    file_put_contents($storageFile, $responseData, LOCK_EX);
    touch($storageFile);
    chmod($storageFile, 0666);
}
