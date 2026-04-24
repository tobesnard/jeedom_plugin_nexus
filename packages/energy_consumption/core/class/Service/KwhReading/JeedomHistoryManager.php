<?php

namespace Nexus\Energy\Electricity;

require_once __DIR__ . '/../../../../vendor/autoload.php';

use DateTimeImmutable;
use history;
use config;
use DB;
use cmd;
use Exception;
use RuntimeException;
use Nexus\Utils\Helpers;
use Nexus\Energy\Electricity\Service\KwhReading\JeedomKwhReading;

/**
 * Gestionnaire de réécriture massive d'historique.
 * * Cette classe permet de recalculer et d'injecter des données de consommation
 * (kWh) et de coûts (Euros) dans les tables d'historique de Jeedom.
 */
class JeedomHistoryManager
{
    /** @var Consumption Moteur de calcul des coûts et consommations */
    protected Consumption $consumption;

    /** @var int Seuil de basculement entre les tables history et historyArch (Timestamp) */
    protected int $archiveThreshold;

    /** @var array Liste des commandes cibles à mettre à jour issue du JSON */
    protected array $mapping = [];

    /** @var int ID de la commande Jeedom source servant de base au calcul */
    protected int $sourceCmdId;

    /** @var bool Si true, simule les écritures sans modifier la base de données */
    protected bool $dryRun;

    /** @var array Stockage temporaire des données pour l'affichage du mode simulation */
    protected array $dryRunResults = [];

    /**
     * @param Consumption|null $_consumption Instance moteur. Si null, créée via config.json
     * @param bool $_dryRun Active le mode simulation (pas d'écriture SQL)
     */
    public function __construct(?Consumption $_consumption = null, bool $_dryRun = false)
    {
        $this->dryRun = $_dryRun;

        // Récupère la configuration Jeedom pour savoir quand archiver (défaut 2h)
        $hours = (int) config::byKey('historyArchiveTime', 'core', 2);
        $this->archiveThreshold = strtotime("- $hours hours");

        $this->loadConfiguration();

        if ($_consumption === null) {
            $contractsPath = __DIR__ . '/../config/contrats.json';
            $contracts = ContractFactory::createFromConfigFile($contractsPath);
            $readingService = new JeedomKwhReading($this->sourceCmdId);
            $this->consumption = new Consumption($readingService, $contracts);
        } else {
            $this->consumption = $_consumption;
        }
    }

    /**
     * Charge le mapping des commandes et l'ID source depuis le fichier JSON.
     * * @throws RuntimeException Si le fichier est manquant ou le JSON corrompu
     */
    protected function loadConfiguration(): void
    {
        $configPath = __DIR__ . "/../config/config.json";
        if (!file_exists($configPath)) {
            throw new RuntimeException("Config absente : $configPath");
        }

        $data = json_decode(file_get_contents($configPath), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new RuntimeException("JSON invalide");
        }

        // ID de la commande Linky/Compteur servant de base aux calculs
        $this->sourceCmdId = (int) ($data['source_cmd_id'] ?? 0);
        if ($this->sourceCmdId === 0) {
            throw new RuntimeException("source_cmd_id manquant");
        }

        // Extraction du mapping des commandes virtuelles cibles
        foreach (($data['history_mapping'] ?? []) as $item) {
            $this->mapping[$item['id']] = [
                'type' => $item['type'],
                'period' => $item['period'],
                'label' => $item['label'],
            ];
        }
    }

    /**
     * Lance la réécriture de l'historique sur une période donnée.
     * * @param string $start Date de début (Y-m-d)
     * @param string $end Date de fin (Y-m-d)
     */
    public function rewriteAll(string $start, string $end): void
    {
        $current = new \DateTimeImmutable($start . ' 00:00:00');
        $limit   = new \DateTimeImmutable($end . ' 23:59:59');
        $this->dryRunResults = [];

        // Itération jour par jour sur la période
        while ($current <= $limit) {
            $dayEnd = $current->setTime(23, 59, 59);

            // Traitement de chaque commande définie dans le mapping
            foreach ($this->mapping as $cmdId => $config) {
                try {
                    $value = $this->calculateValue($dayEnd, $config);
                    $this->forceInsert((int) $cmdId, $dayEnd->format('Y-m-d H:i:s'), $value, $config['label']);
                } catch (Exception $e) {
                    $msg = "[Rewrite] Erreur CMD $cmdId : " . $e->getMessage();
                    if ($this->dryRun) {
                        echo "\033[31m$msg\033[0m\n";
                    } else {
                        Helpers::log($msg, 'error');
                    }
                }
            }
            $current = $current->modify('+1 day');
        }

        if ($this->dryRun) {
            $this->displayDryRunTable();
        }
    }

    /**
     * Supprime les entrées existantes et insère la nouvelle valeur dans la table appropriée.
     * Gère également le déclenchement des événements Jeedom pour mettre à jour l'UI.
     * * @param int $cmdId ID de la commande cible
     * @param string $datetime Date de la valeur
     * @param float $value Valeur calculée
     * @param string $label Libellé pour le log/affichage
     */
    protected function forceInsert(int $cmdId, string $datetime, float $value, string $label): void
    {
        // Choix de la table selon l'ancienneté (historyArch pour les vieilles données)
        $tableName = strtotime($datetime) < $this->archiveThreshold ? 'historyArch' : 'history';

        if ($this->dryRun) {
            $this->dryRunResults[] = [
                'table' => $tableName,
                'id' => $cmdId,
                'date' => $datetime,
                'val' => $value,
                'label' => $label,
            ];
            return;
        }

        $params = ['id' => $cmdId, 'dt' => $datetime];

        // Nettoyage pour éviter les doublons sur le même couple ID/Date
        DB::Prepare("DELETE FROM history WHERE cmd_id=:id AND datetime=:dt", $params, DB::FETCH_TYPE_ROW);
        DB::Prepare("DELETE FROM historyArch WHERE cmd_id=:id AND datetime=:dt", $params, DB::FETCH_TYPE_ROW);

        // Insertion via l'objet history natif de Jeedom
        $history = new history();
        $history->setCmd_id($cmdId);
        $history->setValue($value);
        $history->setDatetime($datetime);
        $history->setTableName($tableName);
        $history->save();

        // Met à jour la valeur "temps réel" de la commande
        $cmd = cmd::byId($cmdId);
        if (is_object($cmd)) {
            $cmd->event($value);
        }
    }

    /**
     * Affiche un tableau formaté en console des opérations simulées.
     */
    protected function displayDryRunTable(): void
    {
        // Tri des résultats par ID puis par Date
        usort($this->dryRunResults, function ($a, $b) {
            return ($a['id'] <=> $b['id']) ?: strcmp($a['date'], $b['date']);
        });

        echo "\n\033[1m--- [DRY-RUN] SIMULATION (Source CMD: {$this->sourceCmdId}) ---\033[0m\n";
        $mask = "| %-12s | %-6s | %-19s | %-10s | %-20s |\n";
        $line = str_repeat("-", 81) . "\n";
        $currentId = null;

        foreach ($this->dryRunResults as $res) {
            if ($currentId !== $res['id']) {
                if ($currentId !== null) {
                    echo $line . "\n";
                }
                $currentId = $res['id'];
                echo "\033[1;34m" . str_pad("ID: $currentId - " . $res['label'], 81, " ", STR_PAD_BOTH) . "\033[0m\n";
                echo $line;
                printf($mask, "TABLE", "ID", "DATE", "VALEUR", "LABEL");
                echo $line;
            }
            $color = ($res['table'] === 'historyArch') ? "\033[33m" : "\033[32m";
            printf("| " . $color . "%-12s\033[0m | %-6d | %-19s | %-10.2f | %-20s |\n", $res['table'], $res['id'], $res['date'], $res['val'], $res['label']);
        }
        echo $line . "\n";
    }

    /**
     * Calcule la consommation ou le coût pour une date donnée selon la période demandée.
     * * @param DateTimeImmutable $date Date de fin de période
     * @param array $config Configuration (type: cost/kwh, period: day/month/year)
     * @return float
     */
    protected function calculateValue(\DateTimeImmutable $date, array $config): float
    {
        switch ($config['period']) {
            case 'rolling_year':
                $start = $date->modify('-1 year')->setTime(0, 0, 0);
                break;
            case 'month_to_date':
                $start = $date->modify('first day of this month')->setTime(0, 0, 0);
                break;
            case 'day':
            default:
                $start = $date->setTime(0, 0, 0);
                break;
        }
        switch ($config['type']) {
            case 'cost':
                $summary = $this->consumption->getBillingSummary($start, $date);
                return (float) ($summary['totals']['cost'] ?? 0.0);
            case 'kwh':
            default:
                $summary = $this->consumption->getBillingSummary($start, $date);
                return (float) ($summary['totals']['kwh'] ?? 0.0);
        }
    }

    /**
     * Retourne l'ID source utilisé pour les calculs.
     */
    public function getSourceCmdId(): int
    {
        return $this->sourceCmdId;
    }
    

    // ...rest of the class implementation to be restored if needed...
}
