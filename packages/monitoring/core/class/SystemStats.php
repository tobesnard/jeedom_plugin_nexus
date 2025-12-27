<?php

namespace Nexus\Monitoring;

require_once __DIR__ . '/../../../../vendor/autoload.php';

use Nexus\Utils\Utils; // Assurez-vous que Utils::escapeChar existe et est accessible statiquement.

/**
 * Classe utilitaire pour récupérer diverses statistiques système Linux.
 * Toutes les méthodes sont désormais STATIQUES.
 * * @package Monitoring
 * @version 1.1 (Compatible PHP 7.4)
 * @author Gemini (Adaptation et correction du code initial)
 * * Dépendances:
 * - La classe Utils::escapeChar doit être disponible.
 * - Le binaire 'speedtest-cli' est requis pour la fonction speedTest().
 */
class SystemStats
{
    /**
     * Exécute une commande shell en toute sécurité et retourne le résultat brut.
     * Centralise la gestion d'erreur de shell_exec.
     * * @param string $command La commande shell à exécuter.
     * @return string|null Le résultat de la commande ou null en cas d'échec ou de commande vide.
     */
    private static function safeShellExec(string $command): ?string
    {
        if (empty($command)) {
            return null;
        }

        // Capture stdout et stderr dans le même flux pour le débogage et vérification
        $result = shell_exec($command . ' 2>&1');

        if ($result === null) {
            error_log("Erreur critique lors de l'exécution de la commande shell: " . $command);
            return null;
        }

        return trim($result);
    }

    // --- Statistiques Générales ---

    /**
     * Récupère le temps écoulé depuis le dernier redémarrage (uptime).
     * * @return string L'uptime sous la forme "X jour(s), Y heures et Z minutes" ou une chaîne d'erreur.
     */
    public static function upTime(): string
    {
        // Remplacement de $this->safeShellExec par self::safeShellExec
        $btime = self::safeShellExec("sed -n '/^btime /s///p' /proc/stat");

        if (empty($btime) || !is_numeric($btime)) {
            return Utils::escapeChar("Erreur: Temps de démarrage non disponible.");
        }

        try {
            // Utilisation des objets DateTime pour un calcul fiable. Le '@' indique un timestamp UNIX.
            $d0 = new \DateTime("@$btime");
            $dNow = new \DateTime('now');
            $diffDates = $dNow->diff($d0);

            // %a pour le nombre total de jours (plus pertinent pour un uptime).
            $data = $diffDates->format('%a jour(s), %h heures et %i minutes');

            return Utils::escapeChar($data);

        } catch (\Exception $e) { // Ajout de l'antislash pour la classe globale Exception
            error_log("Erreur DateTime dans upTime: " . $e->getMessage());
            return Utils::escapeChar("Erreur: Calcul d'uptime impossible.");
        }
    }

    /**
     * Récupère les informations sur la distribution et l'architecture du système.
     * * @return string Infos sous la forme "Debian GNU/Linux X (codename) Ybits (arch)" ou une chaîne d'erreur.
     */
    public static function distribution(): string
    {
        // 1. Nom de la distribution via /etc/os-release (standard moderne)
        $namedistri = self::safeShellExec("grep '^PRETTY_NAME' /etc/os-release 2>/dev/null | cut -d'=' -f2 | tr -d '\"'");

        // 2. Architecture (e.g., x86_64, aarch64)
        $arch = self::safeShellExec("uname -m");

        // 3. Bits de l'OS (32 ou 64)
        $bitdistri = self::safeShellExec("getconf LONG_BIT");

        if (empty($namedistri) || empty($arch) || empty($bitdistri)) {
            error_log("distribution: Infos manquantes (nom: $namedistri, arch: $arch, bits: $bitdistri).");
            return Utils::escapeChar("Erreur: Infos distribution incomplètes.");
        }

        // 4. Assemblage de la chaîne
        $data = "$namedistri " . (int) $bitdistri . "bits ($arch)";
        return Utils::escapeChar($data);
    }

    // --- CPU (Processeur) ---

    /**
     * Récupère le nombre de coeurs CPU et la fréquence maximale.
     * * @return string Le nombre de coeurs et la fréquence max sous la forme "X - Y.YYGhz".
     */
    public static function cpu(): string
    {
        // 1. Nombre de cœurs (Processeur(s))
        $coresNumberStr = self::safeShellExec("lscpu | grep 'Processeur(s)' | awk '{print \$NF}'");

        // 2. Fréquence maximale en MHz
        $maxFrequencyStr = self::safeShellExec("lscpu | grep 'Vitesse maximale du processeur en MHz' | awk '{print \$NF}'");

        if (empty($coresNumberStr) || !is_numeric($coresNumberStr) || empty($maxFrequencyStr)) {
            error_log("cpu: Infos CPU incomplètes ou non numériques. Cores: $coresNumberStr, Freq: $maxFrequencyStr");
            return "0 Cœur - 0.00Ghz";
        }

        // CORRECTION MAJEURE PHP 7.4 : Remplacer la virgule par un point pour la conversion float
        $maxFrequencyStr = str_replace(',', '.', $maxFrequencyStr);

        $CPUsCoresNumber = (int) $coresNumberStr;
        $CPUsMaxFrequency = (float) $maxFrequencyStr;

        // Conversion de MHz en GHz (division par 1000) et formatage à 2 décimales
        $frequencyGhz = number_format($CPUsMaxFrequency / 1000, 2);

        // Résultat réel attendu : 20 - 5.90Ghz
        return $CPUsCoresNumber . " Cœurs - " . $frequencyGhz . "Ghz";
    }

    /**
     * Récupère la température du CPU (pour les systèmes compatibles).
     * * @return float La température en degrés Celsius, arrondie à l'entier le plus proche.
     */
    public static function cpuTemperature(): float
    {
        // 1. Tentative de lecture de la zone thermique 0 (milli-Celsius)
        $command = "cat /sys/class/thermal/thermal_zone0/temp 2>/dev/null";
        $milli_temp = self::safeShellExec($command);

        if (!empty($milli_temp) && is_numeric($milli_temp)) {
            $CPUsTemperature = (float) $milli_temp / 1000.0;
            return round($CPUsTemperature, 0);
        }

        // 2. Fallback pour les systèmes utilisant 'sensors' (si installé)
        $command_fallback = "sensors | grep 'Core 0' | awk '{print \$3}' | sed 's/+//;s/°C//'";
        $fallback_temp = self::safeShellExec($command_fallback);

        if (!empty($fallback_temp) && is_numeric($fallback_temp)) {
            return (float) round((float) $fallback_temp, 0);
        }

        error_log("cpuTemperature: Lecture de température impossible.");
        return 0.0;
    }

    /**
     * Récupère la charge moyenne du CPU sur 1 minute.
     * * @return float La charge moyenne arrondie à 2 décimales.
     */
    public static function loadAverage1min(): float
    {
        // Remplacement de $this->getLoadAverage par self::getLoadAverage
        return self::getLoadAverage(1);
    }

    /**
     * Récupère la charge moyenne du CPU sur 5 minutes.
     * * @return float La charge moyenne arrondie à 2 décimales.
     */
    public static function loadAverage5min(): float
    {
        // Remplacement de $this->getLoadAverage par self::getLoadAverage
        return self::getLoadAverage(5);
    }

    /**
     * Récupère la charge moyenne du CPU sur 15 minutes.
     * * @return float La charge moyenne arrondie à 2 décimales.
     */
    public static function loadAverage15min(): float
    {
        // Remplacement de $this->getLoadAverage par self::getLoadAverage
        return self::getLoadAverage(15);
    }

    /**
     * Méthode interne pour récupérer la charge moyenne du CPU.
     * Utilise /proc/loadavg (plus fiable que parser 'uptime').
     * * @param int $minutes La période (1, 5 ou 15).
     * @return float La charge moyenne arrondie à 2 décimales.
     */
    private static function getLoadAverage(int $minutes): float
    {
        $loadAvgStr = self::safeShellExec("cat /proc/loadavg");

        if (empty($loadAvgStr)) {
            error_log("getLoadAverage: Impossible de lire /proc/loadavg.");
            return 0.00;
        }

        $parts = explode(' ', $loadAvgStr);

        // Correction pour PHP 7.4 : Utilisation de 'switch' au lieu de l'expression 'match' (PHP 8.0+)
        $index = -1;
        switch ($minutes) {
            case 1:
                $index = 0;
                break;
            case 5:
                $index = 1;
                break;
            case 15:
                $index = 2;
                break;
        }

        if ($index === -1 || !isset($parts[$index]) || !is_numeric($parts[$index])) {
            error_log("getLoadAverage: Index ou valeur invalide pour $minutes minutes.");
            return 0.00;
        }

        $loadAvg = (float) $parts[$index];

        return round($loadAvg, 2);
    }

    // --- Stockage & Mémoire ---

    /**
     * Récupère les statistiques d'utilisation du disque pour le point de montage racine (/).
     * * @return string Stats sous la forme "Total : XGo - Utilisé : YGo (Z%)" ou une chaîne d'erreur.
     */
    public static function hddStats(): string
    {
        // Commande : 'df -h' pour des tailles lisibles. 'grep /$' pour la racine.
        // Formatage dans awk pour la structure de la chaîne.
        $command = "df -h | grep ' /$' | awk '{print \"Total : \" \$2 \" - Utilisé : \" \$3 \" (\" \$5 \")\" }'";
        $hddStats = self::safeShellExec($command);

        if (empty($hddStats)) {
            error_log("hddStats: Impossible de récupérer les statistiques de disque.");
            return Utils::escapeChar("Erreur: Stats disque indisponibles.");
        }

        return Utils::escapeChar($hddStats);
    }

    /**
     * Récupère les statistiques de mémoire (RAM et Swap).
     * * @return array|null Tableau associatif des statistiques mémoire (Total, Libre, Utilisé) en Mo ou null.
     */
    public static function memoryStats(): ?array
    {
        $memInfo = self::safeShellExec("grep -E 'MemTotal|MemAvailable|SwapTotal|SwapFree' /proc/meminfo 2>/dev/null");

        if (empty($memInfo)) {
            error_log("memoryStats: Impossible de lire /proc/meminfo.");
            return null;
        }

        $stats = [];
        $lines = explode("\n", $memInfo);

        foreach ($lines as $line) {
            if (preg_match('/^(\w+):\s+(\d+)\s+kB$/i', trim($line), $matches)) {
                $key = $matches[1];
                $value_kb = (int) $matches[2];
                // Conversion de Ko en Mo (1 Mo = 1024 Ko), arrondi à l'entier pour la lisibilité
                $stats[$key] = round($value_kb / 1024, 0);
            }
        }

        $memTotal = $stats['MemTotal'] ?? 0;
        $memAvailable = $stats['MemAvailable'] ?? 0;
        $memUsed = $memTotal - $memAvailable;
        $memUsagePercent = $memTotal > 0 ? round(($memUsed / $memTotal) * 100, 0) : 0;

        $swapTotal = $stats['SwapTotal'] ?? 0;
        $swapFree = $stats['SwapFree'] ?? 0;
        $swapUsed = $swapTotal - $swapFree;
        $swapUsagePercent = $swapTotal > 0 ? round(($swapUsed / $swapTotal) * 100, 0) : 0;

        return [
            'ram' => [
                'total_mo' => $memTotal,
                'available_mo' => $memAvailable,
                'used_mo' => $memUsed,
                'used_percent' => $memUsagePercent,
            ],
            'swap' => [
                'total_mo' => $swapTotal,
                'used_mo' => $swapUsed,
                'used_percent' => $swapUsagePercent,
            ],
        ];
    }

    // --- Réseau ---

    /**
     * Effectue un test de débit descendant (nécessite speedtest-cli).
     * * @return float La vitesse de téléchargement en Mbit/s arrondie à 2 décimales.
     */
    public static function speedTest(): float
    {
        // Commande pour le test de débit descendant uniquement (champ 6 en CSV).
        $command = "speedtest-cli --no-upload --csv 2>/dev/null | awk -F',' '{print \$6}'";
        $result = self::safeShellExec($command);

        if (empty($result) || !is_numeric($result)) {
            // error_log("speedTest: Résultat non numérique ou speedtest-cli non trouvé/échoué.");
            return 0.00;
        }

        // Le résultat de speedtest-cli est généralement un float avec un point comme séparateur.
        $speedTest = (float) $result;

        return round($speedTest, 2);
    }
}
