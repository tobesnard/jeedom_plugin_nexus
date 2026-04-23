<?php

namespace Nexus\Cloud;

require_once __DIR__ . "/../../../../vendor/autoload.php";

use Nexus\Jeedom\Notification;
use Nexus\Utils\Helpers;
use Nexus\Utils\Config;

/**
 * Classe BackupManager - Nexus Framework
 * Gère la synchronisation et la rotation des sauvegardes Jeedom vers Google Drive via rclone.
 */
class BackupManager
{
    private string $remotePath;
    private string $configFile;
    private string $source;
    private int $keepCount;
    private string $cacheDir;

    /**
     * @param string|null $remotePath Chemin rclone distant
     * @param string|null $configFile Chemin du fichier rclone.conf
     * @param int $keepCount Nombre de sauvegardes à conserver
     */
    public function __construct(?string $remotePath = null, ?string $configFile = null, int $keepCount = 10)
    {
        $this->remotePath = $remotePath ?? Config::get('rclone_remote_path', 'google-drive:Jeedom/Sauvegardes/');
        $this->configFile = $configFile ?? Config::get('rclone_config_file', __DIR__ . '/../config/rclone.conf');
        $this->source     = Config::get('backup_source_path', '/var/www/html/backup/');
        $this->keepCount  = $keepCount;
        // Définition d'un dossier de cache accessible en écriture
        $this->cacheDir   = '/tmp/rclone-cache';
    }

    /**
     * Exécute le cycle complet : Synchronisation puis Nettoyage
     */
    public function run(): void
    {
        if (php_sapi_name() !== 'cli') {
            sleep(30);
        }

        if ($this->upload()) {
            Helpers::log("[Backup Cloud] Synchronisation réussie.");
            $this->cleanup();
        }
    }

    /**
     * Copie les fichiers locaux vers le cloud
     */
    private function upload(): bool
    {
        try {
            echo sprintf("[%s] [INFO] Démarrage de l'upload : %s vers %s\n", date('Y-m-d H:i:s'), $this->source, $this->remotePath);

            // Réduction drastique pour éviter l'erreur 403 Rate Limit
            $tpslimit = Config::get('rclone_tpslimit', 3);

            $command = sprintf(
                "rclone copy %s %s --config %s --cache-dir %s --tpslimit %d --transfers 1 --checkers 1 2>&1",
                escapeshellarg($this->source),
                escapeshellarg($this->remotePath),
                escapeshellarg($this->configFile),
                escapeshellarg($this->cacheDir),
                $tpslimit
            );

            exec($command, $output, $returnCode);

            if ($returnCode !== 0) {
                $errorDetails = implode("\n", $output);
                echo sprintf("[%s] [ERROR] Échec du transfert (Code: %d)\n", date('Y-m-d H:i:s'), $returnCode);
                $this->handleFailure("[Backup Cloud] Échec rclone copy. Code: $returnCode. Détails: " . $errorDetails);
                return false;
            }

            echo sprintf("[%s] [SUCCESS] Upload terminé avec succès.\n", date('Y-m-d H:i:s'));
            return true;
        } catch (\Throwable $e) {
            $errorMsg = sprintf("[%s] [EXCEPTION] %s in %s:%d\nStack trace:\n%s", date('Y-m-d H:i:s'), $e->getMessage(), $e->getFile(), $e->getLine(), $e->getTraceAsString());
            echo $errorMsg;
            $this->handleFailure("[Backup Cloud] Exception lors de l'upload : " . $e->getMessage());
            return false;
        }
    }

    /**
     * Nettoie les anciennes sauvegardes sur le Cloud
     */
    private function cleanup(): void
    {
        $tpslimit = Config::get('rclone_tpslimit', 3);
        $delay = Config::get('rclone_delete_delay', 2);
        
        $listCommand = sprintf(
            "rclone lsjson %s --config %s --cache-dir %s --tpslimit %d",
            escapeshellarg($this->remotePath),
            escapeshellarg($this->configFile),
            escapeshellarg($this->cacheDir),
            $tpslimit
        );

        exec($listCommand, $jsonOutput, $returnCode);

        if ($returnCode !== 0) {
            $this->handleFailure("[Backup Cloud] Nettoyage : Impossible de lister les fichiers distants.");
            return;
        }

        $files = json_decode(implode("\n", $jsonOutput), true);
        if (!is_array($files)) {
            return;
        }

        $backups = array_filter($files, function ($file) {
            return preg_match('/backup-eDOM-.*\.tar\.gz$/', $file['Name']);
        });

        usort($backups, function ($a, $b) {
            return strcmp($b['Name'], $a['Name']);
        });

        $toDelete = array_slice($backups, $this->keepCount);

        foreach ($toDelete as $file) {
            $fullPath = $this->remotePath . $file['Name'];
            
            $deleteCommand = sprintf(
                "rclone deletefile %s --config %s --cache-dir %s --tpslimit %d",
                escapeshellarg($fullPath),
                escapeshellarg($this->configFile),
                escapeshellarg($this->cacheDir),
                $tpslimit
            );

            $out = [];
            $code = 0;
            exec($deleteCommand, $out, $code);
            
            if ($code === 0) {
                Helpers::log("[Backup Cloud] Supprimé du cloud : {$file['Name']}", 'debug');
            } else {
                $this->handleFailure("[Backup Cloud] Échec suppression cloud : {$file['Name']}");
            }
            
            sleep($delay);
        }
    }

    private function handleFailure(string $message): void
    {
        Helpers::log($message, 'error');
        Helpers::message("Backup Cloud", $message);
        echo $message;
    }
}