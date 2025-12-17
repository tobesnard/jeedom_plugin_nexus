<?php

namespace Nexus\Energy\Electricity\Util;

class BillingRenderer
{
    /**
     * Affiche le résumé de facturation sous forme de tableau CLI
     * * @param array $summary Le tableau retourné par Consumption::getBillingSummary
     * @return void
     */
    public static function renderConsoleTable(array $summary): void
    {
        $lineLength = 85;
        $headerFormat = "%-12s | %-20s | %-10s | %-10s | %-10s\n";
        $rowFormat    = "%-12s | %-20s | %-10.2f | %-10.4f | %-10.2f €\n";
        $footerFormat = "%-12s   %-20s | %-10.2f | %-10s | %-10.2f €\n";

        // Haut du tableau
        echo "\n" . str_repeat("=", $lineLength) . "\n";
        printf($headerFormat, "Date", "Contrat", "kWh", "Prix Unit.", "Coût Jour");
        echo str_repeat("-", $lineLength) . "\n";

        // Détails quotidiens
        foreach ($summary['daily_details'] as $row) {
            printf(
                $rowFormat,
                $row['date'],
                substr($row['contract'], 0, 20),
                $row['kwh'],
                $row['unit_price'] ?? 0,
                $row['daily_cost']
            );
        }

        // Pied du tableau (Totaux)
        echo str_repeat("-", $lineLength) . "\n";
        printf(
            $footerFormat,
            "TOTAL",
            "",
            $summary['totals']['kwh'],
            "",
            $summary['totals']['cost']
        );
        echo str_repeat("=", $lineLength) . "\n\n";
    }
}
