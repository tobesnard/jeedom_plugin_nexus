<?php

namespace Nexus\Energy\Electricity\Service\KwhReading;

use DateTimeImmutable;

class JeedomKwhReading implements IKwhReading
{
    /**
     * Simule ou récupère l'historique Jeedom
     */
    public function getDailyReadings(DateTimeImmutable $start, DateTimeImmutable $end): array
    {
        $dataset = [];
        $current = $start;

        while ($current <= $end) {
            $dataset[] = [
                'date' => $current,
                'value' => 1.2 // Simulation : 1.2 kWh par jour
            ];
            $current = $current->modify('+1 day');
        }

        return $dataset;
    }

    public function getTotalKwh(DateTimeImmutable $start, DateTimeImmutable $end): float
    {
        $readings = $this->getDailyReadings($start, $end);
        return array_sum(array_column($readings, 'value'));
    }
}
