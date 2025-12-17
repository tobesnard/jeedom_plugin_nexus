<?php

namespace Nexus\Energy\Electricity\Service\KwhReading;

require_once '/var/www/html/core/php/core.inc.php';

use DateTimeImmutable;

class JeedomKwhReading implements IKwhReading
{
    /**
     * Récupère l'historique Jeedom de la commande Linky->daily_consumption 2285
     */
    public function getDailyReadings(DateTimeImmutable $start, DateTimeImmutable $end): array
    {
        $dataset = [];
        $cmdId = 2285;

        // Récupération de l'objet commande Jeedom
        $cmd = \cmd::byId($cmdId);

        if (!is_object($cmd)) {
            throw new \Exception("La commande Jeedom ID {$cmdId} est introuvable.");
        }

        // Extraction de l'historique sur la période
        // Format de retour : tableau d'objets history avec méthodes getValue() et getDatetime()
        $historyList = \history::all($cmdId, $start->format('Y-m-d H:i:s'), $end->format('Y-m-d H:i:s'));

        foreach ($historyList as $entry) {
            $dataset[] = [
                'date'  => new DateTimeImmutable($entry->getDatetime()),
                'value' => (float)$entry->getValue()
            ];
        }

        return $dataset;
    }

    public function getTotalKwh(DateTimeImmutable $start, DateTimeImmutable $end): float
    {
        $readings = $this->getDailyReadings($start, $end);
        return array_sum(array_column($readings, 'value'));
    }
}
