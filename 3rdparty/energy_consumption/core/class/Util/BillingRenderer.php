<?php

namespace Nexus\Energy\Electricity\Util;

class BillingRenderer
{
    private const CLR_RESET  = "\033[0m";
    private const CLR_BOLD   = "\033[1m";
    private const CLR_TITLE  = "\033[1;33;44m"; // Jaune sur fond bleu
    private const CLR_HEADER = "\033[1;34m";    // Bleu gras
    private const CLR_KWH    = "\033[36m";      // Cyan
    private const CLR_PRICE  = "\033[33m";      // Jaune
    private const CLR_COST   = "\033[1;32m";    // Vert gras
    private const CLR_TOTAL  = "\033[1;36m";    // Cyan gras

    public static function renderConsoleTable(array $summary, ?string $title = null): void
    {
        $lineLength = 85;
        $headerFormat = self::CLR_HEADER . "%-12s | %-20s | %-10s | %-10s | %-10s" . self::CLR_RESET . "\n";
        $rowFormat    = "%-12s | %-20s | " . self::CLR_KWH . "%-10.2f" . self::CLR_RESET . " | " . self::CLR_PRICE . "%-10.4f" . self::CLR_RESET . " | " . self::CLR_COST . "%-10.2f €" . self::CLR_RESET . "\n";
        $footerFormat = self::CLR_BOLD . "%-12s   %-20s | " . self::CLR_TOTAL . "%-10.2f" . self::CLR_RESET . self::CLR_BOLD . " | %-10s | " . self::CLR_COST . "%-10.2f €" . self::CLR_RESET . "\n";

        // Affichage du titre stylisé
        if ($title) {
            echo "\n" . self::CLR_TITLE . str_pad(" " . strtoupper($title) . " ", $lineLength, " ", STR_PAD_BOTH) . self::CLR_RESET . "\n";
        }

        // Haut du tableau
        echo str_repeat("=", $lineLength) . "\n";
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
