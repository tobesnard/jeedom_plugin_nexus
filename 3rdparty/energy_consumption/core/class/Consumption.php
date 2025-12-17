<?php

namespace Nexus\Energy\Electricity;

use Nexus\Energy\Electricity\Service\KwhReading\IKwhReading;
use InvalidArgumentException;
use DateTimeImmutable;

class Consumption
{
    private IKwhReading $kwhReading;
    private array $contracts;

    public function __construct(IKwhReading $kwhReading, array $contracts)
    {
        $this->kwhReading = $kwhReading;
        foreach ($contracts as $contract) {
            if (!$contract instanceof Contract) {
                throw new InvalidArgumentException("Instance de Contract requise.");
            }
        }
        $this->contracts = $contracts;
    }

    public function getActiveContract(DateTimeImmutable $date): ?Contract
    {
        foreach ($this->contracts as $contract) {
            $start = $contract->getStartDate();
            $end = $contract->getEndDate();
            if ($date >= $start && ($end === null || $date <= $end)) {
                return $contract;
            }
        }
        return null;
    }

    public function getBillingSummary(DateTimeImmutable $start, DateTimeImmutable $end): array
    {
        $readings = $this->kwhReading->getDailyReadings($start, $end);
        $totalCost = 0.0;
        $totalKwh = 0.0;
        $details = [];

        foreach ($readings as $reading) {
            $date = $reading['date'];
            $kwh = (float)$reading['value'];
            $contract = $this->getActiveContract($date);

            if (!$contract) {
                $details[] = [
                    'date' => $date->format('Y-m-d'),
                    'kwh' => $kwh,
                    'daily_cost' => 0.0,
                    'contract' => 'No contract'
                ];
                continue;
            }

            $price = $contract->getKwhPrice();
            $sub = $contract->getMonthlySubscription();

            // Proratisation sur 30.5 jours
            $dailyCost = ($kwh * $price) + ($sub / 30.5);

            $totalCost += $dailyCost;
            $totalKwh += $kwh;

            $details[] = [
                'date' => $date->format('Y-m-d'),
                'kwh' => $kwh,
                'unit_price' => $price,
                'daily_cost' => $dailyCost,
                'contract' => $contract->getOfferName() ?? 'Unnamed'
            ];
        }

        return [
            'period' => ['start' => $start->format('Y-m-d'), 'end' => $end->format('Y-m-d')],
            'totals' => ['kwh' => $totalKwh, 'cost' => $totalCost],
            'daily_details' => $details
        ];
    }

    /**
     * Retourne un résumé condensé avec décomposition Coût/Abonnement
     */
    private function getCondensedSummary(DateTimeImmutable $start, DateTimeImmutable $end): array
    {
        $summary = $this->getBillingSummary($start, $end);

        $totalKwh = $summary['totals']['kwh'];
        $totalCost = $summary['totals']['cost'];

        // 1. Calcul du coût de l'abonnement sur la période
        // On somme le coût quotidien de l'abonnement pour chaque jour traité dans getBillingSummary
        $totalSubCost = 0.0;
        foreach ($summary['daily_details'] as $day) {
            // On retrouve l'abonnement : daily_cost - (kwh * unit_price)
            $dayKwhCost = $day['kwh'] * ($day['unit_price'] ?? 0);
            $totalSubCost += ($day['daily_cost'] - $dayKwhCost);
        }

        $totalKwhCost = $totalCost - $totalSubCost;

        // 2. Calcul des moyennes
        $avgKwhPrice = $totalKwh > 0 ? ($totalKwhCost / $totalKwh) : 0;

        // Calcul du nombre de jours de la période
        $daysCount = count($summary['daily_details']);
        $avgMonthlySub = $daysCount > 0 ? ($totalSubCost / $daysCount * 30.5) : 0;

        return [
            'period' => $summary['period'],
            'totals' => array_merge($summary['totals'], [
                'kwh_cost' => $totalKwhCost,
                'subscription_cost' => $totalSubCost,
                'avg_kwh_price' => $avgKwhPrice,
                'avg_monthly_sub' => $avgMonthlySub
            ]),
            'daily_details' => [[
                'date'       => "SYNTHÈSE",
                'kwh'        => $totalKwh,
                'unit_price' => $avgKwhPrice,
                'daily_cost' => $totalCost,
                'contract'   => "Moyenne Pondérée",
                // Métadonnées additionnelles pour un éventuel rendu étendu
                'sub_details' => sprintf(
                    "Conso: %.2f€ | Abo: %.2f€ (Moy: %.2f€/mois)",
                    $totalKwhCost,
                    $totalSubCost,
                    $avgMonthlySub
                )
            ]]
        ];
    }

    public function getYesterdaySummary(): array
    {
        $yesterdayStart = new DateTimeImmutable('yesterday 00:00:00');
        $yesterdayEnd   = $yesterdayStart->setTime(23, 59, 59);

        return $this->getCondensedSummary($yesterdayStart, $yesterdayEnd);
    }

    public function getCurrentMonthSummary(): array
    {
        $start = new DateTimeImmutable('first day of this month 00:00:00');
        $yesterday = new DateTimeImmutable('yesterday 23:59:59');
        if ($yesterday < $start) {
            $yesterday = $start->setTime(23, 59, 59);
        }
        return $this->getCondensedSummary($start, $yesterday);
    }

    public function getYearlyRollingSummary(): array
    {
        $yesterday = new DateTimeImmutable('yesterday 23:59:59');
        $start = $yesterday->modify('-1 year')->setTime(0, 0, 0);
        return $this->getCondensedSummary($start, $yesterday);
    }
}
