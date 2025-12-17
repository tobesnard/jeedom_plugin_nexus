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
            $contracts[] = new Contract(
                new \DateTimeImmutable($c['contract_details']['validity']['start_date']),
                (float) $c['pricing']['unit_prices'][0]['price_per_kwh'],
                (float) $c['pricing']['monthly_subscription'],
                $c['contract_details']['tariff_option'],
                $c['contract_details']['validity']['end_date'] ? new \DateTimeImmutable($c['contract_details']['validity']['end_date']) : null,
                $c['contract_details']['account_number'],
                $c['metadata']['delivery_point_id'],
                $c['contract_details']['offer_name']
            );
        }

        return $contracts;
    }
}
