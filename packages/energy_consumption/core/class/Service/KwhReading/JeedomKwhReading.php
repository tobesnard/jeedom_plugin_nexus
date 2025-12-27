<?php

namespace Nexus\Energy\Electricity\Service\KwhReading;

use DateTimeImmutable;

/**
 * Implémentation `IKwhReading` pour Jeedom.
 *
 * Cette classe interroge la commande Linky (par défaut id 2285) via
 * `JeedomClient` pour récupérer l'historique des consommations journalières.
 */
class JeedomKwhReading implements IKwhReading
{
    private JeedomClient $client;
    private int $cmdId;

    /**
     * @param JeedomClient|null $client Wrapper Jeedom (injectable pour tests)
     * @param int $cmdId ID de la commande Linky (par défaut 2285)
     */
    public function __construct(?JeedomClient $client = null, int $cmdId = 2285)
    {
        $this->client = $client ?? new JeedomClient();
        $this->cmdId = $cmdId;
    }
    /**
     * Récupère l'historique Jeedom de la commande Linky->daily_consumption (par défaut id 2285).
     *
     * Le tableau retourné contient des éléments de la forme :
     * ['date' => DateTimeImmutable, 'value' => float]
     *
     * @param DateTimeImmutable $start
     * @param DateTimeImmutable $end
     * @return array[]
     * @throws \Exception si la commande Jeedom n'existe pas
     */
    public function getDailyReadings(DateTimeImmutable $start, DateTimeImmutable $end): array
    {
        $dataset = [];
        // Récupération de l'objet commande Jeedom via le client
        $cmd = $this->client->getCmdById($this->cmdId);

        if (!is_object($cmd)) {
            throw new \Exception("La commande Jeedom ID {$this->cmdId} est introuvable.");
        }

        // Extraction de l'historique sur la période via le client
        // Format de retour attendu : tableau d'objets history avec méthodes getValue() et getDatetime()
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
     * Somme simple des valeurs retournées par `getDailyReadings`.
     *
     * @param DateTimeImmutable $start
     * @param DateTimeImmutable $end
     * @return float
     */
    public function getTotalKwh(DateTimeImmutable $start, DateTimeImmutable $end): float
    {
        $readings = $this->getDailyReadings($start, $end);
        return array_sum(array_column($readings, 'value'));
    }
}
