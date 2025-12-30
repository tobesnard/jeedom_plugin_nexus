<?php

namespace Nexus\Energy\Electricity\Service\KwhReading;

/**
 * Interface pour les services fournissant des relevés journaliers kWh.
 */
interface IKwhReading
{
    /**
     * Retourne un tableau de relevés.
     * Chaque élément : ['date' => \DateTimeImmutable, 'value' => float]
     *
     * @param \DateTimeImmutable $start
     * @param \DateTimeImmutable $end
     * @return array[]
     */
    public function getDailyReadings(\DateTimeImmutable $start, \DateTimeImmutable $end): array;

    /**
     * Retourne la consommation totale (somme des valeurs) sur la période.
     *
     * @param \DateTimeImmutable $start
     * @param \DateTimeImmutable $end
     * @return float
     */
    public function getTotalKwh(\DateTimeImmutable $start, \DateTimeImmutable $end): float;

    /**
     * Retourne la date du tout premier relevé disponible en base de données.
     * Utile pour déterminer la borne de départ lors d'une réécriture d'historique.
     *
     * @return \DateTimeImmutable|null
     */
    public function getFirstReadingDate(): ?\DateTimeImmutable;
}
