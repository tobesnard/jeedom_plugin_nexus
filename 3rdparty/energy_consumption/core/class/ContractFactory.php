<?php

namespace Nexus\Energy\Electricity;

class ContractFactory
{
    /**
     * @param string $jsonPath Chemin vers le fichier config/contrats.json
        * @return Contract[]
     */
    public static function createFromConfigFile(string $jsonPath): array
    {
        if (!file_exists($jsonPath)) {
            throw new \InvalidArgumentException("Fichier de configuration introuvable : $jsonPath");
        }

        $jsonData = json_decode(file_get_contents($jsonPath), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \RuntimeException("Erreur lors du parsing JSON : " . json_last_error_msg());
        }

        $contracts = [];
        foreach ($jsonData as $c) {
            $startDate = $c['contract_details']['validity']['start_date'] ?? null;
            $endDate = $c['contract_details']['validity']['end_date'] ?? null;
            $unitPrice = $c['pricing']['unit_prices'][0]['price_per_kwh'] ?? 0.0;
            $monthlySubscription = $c['pricing']['monthly_subscription'] ?? 0.0;
            $tariffOption = $c['contract_details']['tariff_option'] ?? '';
            $accountNumber = $c['contract_details']['account_number'] ?? null;
            $deliveryPointId = $c['metadata']['delivery_point_id'] ?? null;
            $offerName = $c['contract_details']['offer_name'] ?? null;

            $contracts[] = new Contract(
                new \DateTimeImmutable($startDate),
                (float) $unitPrice,
                (float) $monthlySubscription,
                $tariffOption,
                $endDate ? new \DateTimeImmutable($endDate) : null,
                $accountNumber,
                $deliveryPointId,
                $offerName
            );
        }

        return $contracts;
    }
}
