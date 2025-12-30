<?php

namespace Nexus\Energy\Electricity\Service\KwhReading;

require_once __DIR__ . "/../../../../../../vendor/autoload.php";

use DateTimeImmutable;
use DB;
use Exception;

/**
 * Implémentation `IKwhReading` pour Jeedom.
 * Interroge la commande de consommation journalière (Source) pour les calculs.
 */
class JeedomKwhReading implements IKwhReading
{
    private JeedomClient $client;
    private int $cmdId;

    /**
     * @param int $cmdId ID de la commande source (ex: Consommation Jour Linky)
     * @param JeedomClient|null $client Wrapper Jeedom (injectable pour tests)
     */
    public function __construct(int $cmdId, ?JeedomClient $client = null)
    {
        $this->cmdId = $cmdId;
        $this->client = $client ?? new JeedomClient();
    }

    /**
     * Récupère l'historique brut de la commande source.
     * * @param DateTimeImmutable $start
     * @param DateTimeImmutable $end
     * @return array[]
     * @throws Exception
     */
    public function getDailyReadings(DateTimeImmutable $start, DateTimeImmutable $end): array
    {
        $dataset = [];
        $cmd = $this->client->getCmdById($this->cmdId);

        if (!is_object($cmd)) {
            throw new Exception("La commande Jeedom source ID {$this->cmdId} est introuvable.");
        }

        $historyList = $this->client->getHistory($this->cmdId, $start->format('Y-m-d H:i:s'), $end->format('Y-m-d H:i:s'));

        foreach ($historyList as $entry) {
            $dataset[] = [
                'date'  => new DateTimeImmutable($entry->getDatetime()),
                'value' => (float) $entry->getValue(),
            ];
        }

        return $dataset;
    }

    /**
     * Somme les kWh sur une période.
     */
    public function getTotalKwh(DateTimeImmutable $start, DateTimeImmutable $end): float
    {
        $readings = $this->getDailyReadings($start, $end);
        return array_sum(array_column($readings, 'value'));
    }

    /**
     * Trouve la date la plus ancienne en base pour cette commande source.
     */
    public function getFirstReadingDate(): ?DateTimeImmutable
    {
        $sql = "SELECT MIN(datetime) as min_date FROM (
                    SELECT MIN(datetime) as datetime FROM history WHERE cmd_id = :id
                    UNION
                    SELECT MIN(datetime) as datetime FROM historyArch WHERE cmd_id = :id
                ) as sub";

        $res = DB::Prepare($sql, ['id' => $this->cmdId], DB::FETCH_TYPE_ROW);

        return ($res && !empty($res['min_date'])) ? new DateTimeImmutable($res['min_date']) : null;
    }
}
