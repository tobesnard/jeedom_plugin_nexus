<?php

namespace Nexus\Cloud;

require_once __DIR__ . "/../../../../vendor/autoload.php";

use Nexus\Jeedom\Notification;
use Nexus\Utils\Helpers;

/**
 * Classe BackupManager - Nexus Framework
 * Gère la synchronisation et la rotation des sauvegardes Jeedom vers Google Drive via rclone.
 */
class BackupManager
{
    private $remotePath;
    private $configFile;
    private $source = '/var/www/html/backup/';
    private $keepCount;

    public function __construct(string $remotePath = null, string $configFile = null, int $keepCount = 10)
    {
        $this->remotePath = $remotePath ?? (defined('RCLONE_REMOTE_PATH') ? RCLONE_REMOTE_PATH : 'google-drive:Jeedom/Sauvegardes/');
        $this->configFile = $configFile ?? __DIR__ . '/../config/rclone.conf';
        $this->keepCount  = $keepCount;
    }

    /**
     * Exécute le cycle complet : Synchronisation puis Nettoyage
     */
    public function run()
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
        $command = sprintf(
            "rclone copy %s %s --config %s",
            escapeshellarg($this->source),
            escapeshellarg($this->remotePath),
            escapeshellarg($this->configFile),
        );

        exec($command, $output, $returnCode);

        if ($returnCode !== 0) {
            $this->notifyError("[Backup Cloud] Échec rclone copy. Code: $returnCode. Détails: " . implode(' ', $output));
            return false;
        }

        return true;
    }

    /**
     * Nettoie les anciennes sauvegardes sur le Cloud
     */
    private function cleanup()
    {
        Helpers::execute(function () {
            $listCommand = sprintf(
                "rclone lsjson %s --config %s",
                escapeshellarg($this->remotePath),
                escapeshellarg($this->configFile),
            );

            exec($listCommand, $jsonOutput, $returnCode);

            if ($returnCode !== 0) {
                $this->notifyError("[Backup Cloud] Nettoyage : Impossible de lister les fichiers distants.");
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
                $deleteCommand = sprintf(
                    "rclone deletefile %s --config %s",
                    escapeshellarg($this->remotePath . $file['Name']),
                    escapeshellarg($this->configFile),
                );

                exec($deleteCommand, $out, $code);

                if ($code === 0) {
                    Helpers::log("[Backup Cloud] Supprimé du cloud : {$file['Name']}", 'debug');
                } else {
                    $this->notifyError("[Backup Cloud] Échec suppression cloud : {$file['Name']}");
                }
            }
        });
    }

    /**
     * Notification via Helpers, Notification et Jeedom Message
     */
    private function notifyError(string $message)
    {
        Helpers::log($message, 'error');

        // Notification d'urgence
        Notification::emergencyThreadNotification($message);

        // Ajout au centre de message Jeedom
        if (class_exists('\message')) {
            \message::add("Backup Cloud Error", $message);
        }
    }
}
