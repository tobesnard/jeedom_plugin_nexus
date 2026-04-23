<?php

namespace Nexus\Cloud;

require_once __DIR__ . "/../../../../vendor/autoload.php";

use Nexus\Utils\Helpers;
use Nexus\Utils\Config;

class BackupManager
{
    private string $remotePath;
    private string $source;
    private int $keepCount;
    private string $cacheDir;
    private string $remoteName;

    public function __construct(?string $remotePath = null, int $keepCount = 10)
    {
        $this->remoteName = 'google-drive';
        $this->remotePath = $remotePath ?? Config::get('rclone_remote_path', $this->remoteName . ':Jeedom/Sauvegardes/');
        $this->source     = Config::get('backup_source_path', '/var/www/html/backup/');
        $this->keepCount  = $keepCount;
        $this->cacheDir   = '/tmp/rclone-cache';

        if (!is_dir($this->cacheDir)) {
            mkdir($this->cacheDir, 0755, true);
        }
    }

    /**
     * Log les variables d'environnement rclone pour débogage
     */
    private function debugEnv(): void
    {
        $vars = [
            'RCLONE_CONFIG_GOOGLE_DRIVE_TOKEN',
            'RCLONE_CONFIG_GOOGLE_DRIVE_CLIENT_ID',
            'RCLONE_CONFIG_GOOGLE_DRIVE_CLIENT_SECRET'
        ];

        echo "--- DEBUG ENV ---\n";
        foreach ($vars as $var) {
            $val = getenv($var);
            echo sprintf("[%s] %s\n", $var, $val ? "Chargée (longueur: " . strlen($val) . ")" : "VIDE / NON TROUVÉE");
        }
        echo "-----------------\n";
    }

    public function run(): void
    {
        // Debug au démarrage
        $this->debugEnv();

        if (php_sapi_name() !== 'cli') {
            sleep(30);
        }

        if ($this->upload()) {
            Helpers::log("[Backup Cloud] Synchronisation réussie.");
            $this->cleanup();
        }
    }

    private function upload(): bool
    {
        try {
            $tpslimit = Config::get('rclone_tpslimit', 3);
            $envPrefix = "RCLONE_CONFIG_GOOGLE_DRIVE_TYPE=drive ";

            $command = sprintf(
                $envPrefix . "rclone copy %s %s --cache-dir %s --tpslimit %d --transfers 1 --checkers 1 2>&1",
                escapeshellarg($this->source),
                escapeshellarg($this->remotePath),
                escapeshellarg($this->cacheDir),
                $tpslimit
            );

            exec($command, $output, $returnCode);

            if ($returnCode !== 0) {
                $this->handleFailure("[Backup Cloud] Échec rclone copy. Code: $returnCode. Détails: " . implode("\n", $output));
                return false;
            }

            return true;
        } catch (\Throwable $e) {
            $this->handleFailure("[Backup Cloud] Exception : " . $e->getMessage());
            return false;
        }
    }

    private function cleanup(): void
    {
        $tpslimit = Config::get('rclone_tpslimit', 3);
        $delay = (int)Config::get('rclone_delete_delay', 2);
        
        $envPrefix = "RCLONE_CONFIG_GOOGLE_DRIVE_TYPE=drive ";

        $listCommand = sprintf(
            $envPrefix . "rclone lsjson %s --cache-dir %s --tpslimit %d --files-only",
            escapeshellarg($this->remotePath),
            escapeshellarg($this->cacheDir),
            $tpslimit
        );

        exec($listCommand, $jsonOutput, $returnCode);

        if ($returnCode !== 0) {
            $this->handleFailure("[Backup Cloud] Nettoyage : Impossible de lister les fichiers.");
            return;
        }

        $files = json_decode(implode("", $jsonOutput), true);
        if (!is_array($files)) return;

        $backups = array_filter($files, function ($file) {
            return isset($file['Name']) && preg_match('/backup-eDOM-.*\.tar\.gz$/', $file['Name']);
        });

        usort($backups, function ($a, $b) {
            return strcmp($b['Name'], $a['Name']);
        });

        $toDelete = array_slice($backups, $this->keepCount);

        foreach ($toDelete as $file) {
            $deleteCommand = sprintf(
                $envPrefix . "rclone deletefile %s --cache-dir %s --tpslimit %d 2>&1",
                escapeshellarg($this->remotePath . $file['Name']),
                escapeshellarg($this->cacheDir),
                $tpslimit
            );

            exec($deleteCommand, $out, $code);
            
            if ($code === 0) {
                Helpers::log("[Backup Cloud] Supprimé : {$file['Name']}", 'debug');
            } else {
                $this->handleFailure("[Backup Cloud] Échec suppression : {$file['Name']}");
            }
            
            if ($delay > 0) sleep($delay);
        }
    }

    private function handleFailure(string $message): void
    {
        Helpers::log($message, 'error');
        Helpers::message("Backup Cloud", $message);
        echo sprintf("[%s] [ERROR] %s\n", date('Y-m-d H:i:s'), $message);
    }
}