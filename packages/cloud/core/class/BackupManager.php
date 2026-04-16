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
        echo sprintf("[%s] [INFO] Démarrage de l'upload : %s vers %s\n", date('Y-m-d H:i:s'), $this->source, $this->remotePath);

        // --tpslimit adapté au quota utilisateur (12 par défaut)
        $tpslimit = Config::get('rclone_tpslimit', 12);
        $command = sprintf(
            "rclone copy %s %s --config %s --tpslimit %d 2>&1",
            escapeshellarg($this->source),
            escapeshellarg($this->remotePath),
            escapeshellarg($this->configFile),
            $tpslimit
        );

        // Ajout de 2>&1 pour capturer les erreurs standard (stderr) dans $output
        exec($command, $output, $returnCode);

        if ($returnCode !== 0) {
            $errorDetails = implode("\n", $output);
            echo sprintf("[%s] [ERROR] Échec du transfert (Code: %d)\n", date('Y-m-d H:i:s'), $returnCode);
            echo "[DÉTAILS] " . $errorDetails . "\n";

            $this->handleFailure("[Backup Cloud] Échec rclone copy. Code: $returnCode. Détails: " . $errorDetails);
            return false;
        }

        echo sprintf("[%s] [SUCCESS] Upload terminé avec succès.\n", date('Y-m-d H:i:s'));
        return true;
    }

    /**
     * Nettoie les anciennes sauvegardes sur le Cloud
     */
    private function cleanup(): void
    {

        $tpslimit = Config::get('rclone_tpslimit', 12);
        $delay = Config::get('rclone_delete_delay', 2); // délai en secondes entre suppressions
        $listCommand = sprintf(
            "rclone lsjson %s --config %s --tpslimit %d",
            escapeshellarg($this->remotePath),
            escapeshellarg($this->configFile),
            $tpslimit
        );

        Helpers::log('[Backup Cloud] Commande de listing exécutée : ' . $listCommand);
        Helpers::log('[Backup Cloud] Fichier rclone.conf utilisé : ' . $this->configFile);

        exec($listCommand, $jsonOutput, $returnCode);

        if ($returnCode !== 0) {
            Helpers::log('[Backup Cloud] Retour commande : ' . implode("\n", $jsonOutput));
            Helpers::log('[Backup Cloud] Code retour : ' . $returnCode);
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
            $fullPath = $this->remotePath . $file['Name'];
            // Vérification existence fichier sur le cloud
            $checkCommand = sprintf(
                "rclone lsjson %s --config %s --tpslimit %d",
                escapeshellarg($fullPath),
                escapeshellarg($this->configFile),
                $tpslimit
            );
            $checkOutput = [];
            $checkReturn = 0;
            exec($checkCommand, $checkOutput, $checkReturn);
            Helpers::log("[Backup Cloud][DEBUG] Résultat existence fichier (lsjson) : code $checkReturn, output : " . implode(' | ', $checkOutput), 'debug');
            if ($checkReturn !== 0) {
                Helpers::log("[Backup Cloud] Fichier déjà supprimé ou inexistant sur le cloud : $fullPath", 'debug');
                continue;
            }
            $deleteCommand = sprintf(
                "rclone deletefile %s --config %s --tpslimit %d",
                escapeshellarg($fullPath),
                escapeshellarg($this->configFile),
                $tpslimit
            );
            Helpers::log("[Backup Cloud][DEBUG] Suppression : Commande exécutée : $deleteCommand", 'debug');
            Helpers::log("[Backup Cloud][DEBUG] Fichier cible : $fullPath", 'debug');
            Helpers::log("[Backup Cloud][DEBUG] rclone.conf utilisé : " . $this->configFile, 'debug');
            $out = [];
            $code = 0;
            exec($deleteCommand, $out, $code);
            Helpers::log("[Backup Cloud][DEBUG] Résultat suppression : code $code, output : " . implode(' | ', $out), 'debug');
            if ($code === 0) {
                Helpers::log("[Backup Cloud] Supprimé du cloud : {$file['Name']}", 'debug');
            } else {
                $this->handleFailure("[Backup Cloud] Échec suppression cloud : {$file['Name']} (erreur suppression, mais fichier existant confirmé)");
            }
            // Ajout d'un délai pour éviter le dépassement de quota (configurable)
            sleep($delay);
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
        //Notification::emergencyThreadNotification($message);

        // 3. Centre de messages Jeedom
        Helpers::message("Backup Cloud", $message);

        echo $message;
    }
}

