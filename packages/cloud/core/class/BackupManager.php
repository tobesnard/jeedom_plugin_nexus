<?php

namespace Nexus\Cloud;

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
        // Utilisation des constantes centralisées ou valeurs par défaut
        $this->remotePath = $remotePath ?? (defined('RCLONE_REMOTE_PATH') ? RCLONE_REMOTE_PATH : 'google-drive:Jeedom/Sauvegardes/');
        $this->configFile = $configFile ?? __DIR__ . '/../config/rclone.conf';
        $this->keepCount  = $keepCount;
    }

    /**
     * Exécute le cycle complet : Synchronisation puis Nettoyage
     */
    public function run()
    {
        // Attente pour laisser Jeedom finaliser l'écriture du tar.gz
        if (php_sapi_name() !== 'cli') {
            sleep(30);
        }

        if ($this->upload()) {
            \log::add('nexus', 'info', "✅ Backup Cloud : Synchronisation réussie.");
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
            $this->notifyError("❌ Échec rclone copy. Code: $returnCode. Détails: " . implode(' ', $output));
            return false;
        }

        return true;
    }

    /**
     * Nettoie les anciennes sauvegardes sur le Cloud
     */
    private function cleanup()
    {
        $listCommand = sprintf(
            "rclone lsjson %s --config %s",
            escapeshellarg($this->remotePath),
            escapeshellarg($this->configFile),
        );

        exec($listCommand, $jsonOutput, $returnCode);

        if ($returnCode !== 0) {
            $this->notifyError("❌ Nettoyage : Impossible de lister les fichiers distants.");
            return;
        }

        $files = json_decode(implode("\n", $jsonOutput), true);
        if (!is_array($files)) {
            return;
        }

        // Filtrage des backups Jeedom
        $backups = array_filter($files, function ($file) {
            return preg_match('/backup-eDOM-.*\.tar\.gz$/', $file['Name']);
        });

        // Tri décroissant par date extraite du nom de fichier
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
                \log::add('nexus', 'debug', "🗑️ Supprimé du cloud : {$file['Name']}");
            } else {
                $this->notifyError("⚠️ Échec suppression cloud : {$file['Name']}");
            }
        }
    }

    /**
     * Notification centralisée
     */
    private function notifyError(string $message)
    {
        \log::add('nexus', 'error', $message);

        // Notification via commande Jeedom
        $cmd = \cmd::byString("#[Télécommunication][Notification Manager][Jeedom Alerte]#");
        if (is_object($cmd)) {
            $cmd->execCmd(['message' => $message]);
        }

        // Message centre de message Jeedom
        \message::add("Backup Cloud Error", $message);
    }
}
