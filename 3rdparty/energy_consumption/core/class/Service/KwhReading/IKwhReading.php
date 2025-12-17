<?php

namespace Nexus\Energy\Electricity\Service\KwhReading;

interface IKwhReading
{
    /**
     * Retourne un tableau de relevés.
     * Chaque ligne est un tableau : ['date' => \DateTimeImmutable, 'value' => float]
     * * @return array[]
     */
    public function getDailyReadings(\DateTimeImmutable $start, \DateTimeImmutable $end): array;

    /**
     * Retourne la consommation totale sur la période
     */
    public function getTotalKwh(\DateTimeImmutable $start, \DateTimeImmutable $end): float;
}
