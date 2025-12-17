<?php

namespace Nexus\Energy\Electricity;

use Nexus\Energy\Electricity\Service\KwhReading\IKwhReading;
use InvalidArgumentException;
use RuntimeException;
use DateTimeImmutable;

class Consumption
{
    private IKwhReading $kwhReading;

    /** @var Contract[] */
    private array $contracts;

    /**
     * @param IKwhReading $kwhReading
     * @param Contract[] $contracts
     * @throws InvalidArgumentException
     */
    public function __construct(IKwhReading $kwhReading, array $contracts)
    {
        $this->kwhReading = $kwhReading;

        foreach ($contracts as $contract) {
            if (!$contract instanceof Contract) {
                throw new InvalidArgumentException(
                    sprintf(
                        "All items in the contracts array must be instances of %s.",
                        Contract::class
                    )
                );
            }
        }

        $this->contracts = $contracts;
    }

    /**
     * Identifie le contrat actif pour une date précise
     */
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

    /**
     * Compile les données de consommation et calcule les coûts réels
     * en itérant sur chaque relevé pour appliquer le bon contrat (gestion multi-contrat sur la période)
     * * @param DateTimeImmutable $start
     * @param DateTimeImmutable $end
     * @return array
     */
    public function getBillingSummary(DateTimeImmutable $start, DateTimeImmutable $end): array
    {
        $readings = $this->kwhReading->getDailyReadings($start, $end);

        $totalCost = 0.0;
        $totalKwh = 0.0;
        $details = [];

        foreach ($readings as $reading) {
            /** @var DateTimeImmutable $date */
            $date = $reading['date'];
            $kwh = (float) $reading['value'];

            $contract = $this->getActiveContract($date);

            if (!$contract) {
                // On log l'absence de contrat pour cette date mais on continue le calcul
                $details[] = [
                    'date' => $date->format('Y-m-d'),
                    'kwh' => $kwh,
                    'cost' => 0.0,
                    'error' => 'No active contract'
                ];
                continue;
            }

            $price = $contract->getKwhPrice();
            $sub = $contract->getMonthlySubscription();

            // Calcul proratisé : (conso * prix) + (abonnement mensuel / 30.5 jours)
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
            'period' => [
                'start' => $start->format('Y-m-d'),
                'end' => $end->format('Y-m-d'),
            ],
            'totals' => [
                'kwh' => $totalKwh,
                'cost' => $totalCost,
            ],
            'daily_details' => $details
        ];
    }
}
