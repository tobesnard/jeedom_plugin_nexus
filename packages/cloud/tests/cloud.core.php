<?php

namespace Nexus\Cloud;

// 1. Simulation de l'environnement Jeedom
if (!class_exists('log')) {
    class log
    {
        public static function add($name, $level, $message)
        {
            echo sprintf("[%s][%s] %s\n", strtoupper($level), $name, $message);
        }
    }
}

if (!class_exists('message')) {
    class message
    {
        public static function add($type, $message)
        {
            echo "[MESSAGE CENTER] $type : $message\n";
        }
    }
}

// 2. Inclusion des dépendances
require_once __DIR__ . "/../../../vendor/autoload.php";
// require_once __DIR__ . "/../core/class/Notification.php"; // Nécessaire pour notifyError
// require_once __DIR__ . "/BackupManager.php";

// 3. Configuration de test
// Utilise un dossier temporaire local comme destination "Cloud" pour tester rclone sans risque
$testSource = __DIR__ . '/test_source/';
$testRemote = __DIR__ . '/test_remote/';
$testConfig = __DIR__ . '/../core/config/rclone.conf';

// Création des dossiers de test si inexistants
if (!is_dir($testSource)) {
    mkdir($testSource, 0777, true);
}
if (!is_dir($testRemote)) {
    mkdir($testRemote, 0777, true);
}

// Création d'un faux fichier de backup
$fakeBackup = "backup-eDOM-test-" . date('Ymd-His') . ".tar.gz";
file_put_contents($testSource . $fakeBackup, "Données de backup simulées");

try {
    echo "--- DÉBUT DU TEST BACKUP MANAGER ---\n";

    // Instanciation avec les chemins de test
    // On passe le dossier local comme remote pour que rclone travaille en local
    $bm = new BackupManager($testRemote, $testConfig, 2);

    // Reflection pour modifier la source privée (uniquement pour le test)
    $reflector = new \ReflectionClass($bm);
    $sourceProp = $reflector->getProperty('source');
    $sourceProp->setAccessible(true);
    $sourceProp->setValue($bm, $testSource);

    echo "1. Lancement du Run (Upload)...\n";
    $bm->run();

    echo "\n2. Vérification du fichier dans la destination...\n";
    if (file_exists($testRemote . $fakeBackup)) {
        echo "✅ SUCCÈS : Le fichier a été copié par rclone.\n";
    } else {
        echo "❌ ÉCHEC : Le fichier est introuvable dans la destination.\n";
    }

    echo "\n3. Test du Cleanup (Simulation)...\n";
    // Pour tester le cleanup, on force l'appel car il est commenté dans ton run()
    $method = $reflector->getMethod('cleanup');
    $method->setAccessible(true);
    $method->invoke($bm);

} catch (\Throwable $e) {
    echo "ERREUR FATALE : " . $e->getMessage() . "\n";
    echo "TRACE : " . $e->getTraceAsString() . "\n";
}

echo "\n--- FIN DU TEST ---\n";
