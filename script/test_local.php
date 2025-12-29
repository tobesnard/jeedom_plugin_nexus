<?php

$storageFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'nexus_bot_last_response.txt';

echo "Tentative d'ecriture dans : $storageFile\n";

if (file_put_contents($storageFile, "TEST_INTERNE", LOCK_EX)) {
    echo "[+] Reussi. Contenu actuel : " . file_get_contents($storageFile) . "\n";
    touch($storageFile);
    chmod($storageFile, 0666);
} else {
    echo "[!] ECHEC CRITIQUE : Impossible d'ecrire dans le dossier temporaire.\n";
}
