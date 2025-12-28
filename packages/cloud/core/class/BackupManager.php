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
    }

    /**
     * Exécute le cycle complet : Synchronisation puis Nettoyage
     */
    public function run(): void
    {
        // Si lancé hors CLI (ex: via bouton dashboard), on temporise pour laisser le backup local finir
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
            $this->handleFailure("[Backup Cloud] Échec rclone copy. Code: $returnCode. Détails: " . implode(' ', $output));
            return false;
        }

        return true;
    }

    /**
     * Nettoie les anciennes sauvegardes sur le Cloud
     */
    private function cleanup(): void
    {
        $listCommand = sprintf(
            "rclone lsjson %s --config %s",
            escapeshellarg($this->remotePath),
            escapeshellarg($this->configFile),
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

        // Filtrage des fichiers de backup eDOM
        $backups = array_filter($files, function ($file) {
            return preg_match('/backup-eDOM-.*\.tar\.gz$/', $file['Name']);
        });

        // Tri décroissant par nom (date)
        usort($backups, function ($a, $b) {
            return strcmp($b['Name'], $a['Name']);
        });

        // Identification des fichiers en trop
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
                $this->handleFailure("[Backup Cloud] Échec suppression cloud : {$file['Name']}");
            }
        }
    }

    /**
     * Gestion centralisée des alertes en cas d'échec
     */
    private function handleFailure(string $message): void
    {
        // 1. Log dans le fichier Nexus
        Helpers::log($message, 'error');

        // 2. Notification d'urgence (SMS/Telegram via Config)
        Notification::emergencyThreadNotification($message);

        // 3. Centre de messages Jeedom
        Helpers::message("Backup Cloud", $message);
    }
}
