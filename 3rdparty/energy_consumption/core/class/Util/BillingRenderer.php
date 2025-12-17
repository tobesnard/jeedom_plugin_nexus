<?php

namespace Nexus\Energy\Electricity\Util;

class BillingRenderer
{
    private const CLR_RESET  = "\033[0m";
    private const CLR_BOLD   = "\033[1m";
    private const CLR_TITLE  = "\033[1;37;44m"; // Blanc sur fond bleu
    private const CLR_HEADER = "\033[1;34m";    // Bleu gras
    private const CLR_KWH    = "\033[36m";      // Cyan
    private const CLR_PRICE  = "\033[33m";      // Jaune
    private const CLR_COST   = "\033[1;32m";    // Vert gras
    private const CLR_TOTAL  = "\033[1;36m";    // Cyan gras
    private const CLR_SUB    = "\033[35m";      // Magenta (pour l'abonnement)

    private const W_DATE    = 15;
    private const W_CONTRAT = 25;
    private const W_KWH     = 12;
    private const W_PRICE   = 12;
    private const W_COST    = 12;

    /**
     * Formate une cellule pour garantir la chasse fixe malgré les codes ANSI
     */
    private static function fCell(string $content, int $width, string $color = "", int $padType = STR_PAD_RIGHT): string
    {
        $truncated = mb_strimwidth($content, 0, $width, "");
        $padded = str_pad($truncated, $width, " ", $padType);
        return ($color !== "") ? $color . $padded . self::CLR_RESET : $padded;
    }

    public static function renderConsoleTable(array $summary, ?string $title = null): void
    {
        $sep = " | ";
        $lineLength = self::W_DATE + self::W_CONTRAT + self::W_KWH + self::W_PRICE + self::W_COST + (strlen($sep) * 4);

        // 1. Titre
        if ($title) {
            echo "\n" . self::CLR_TITLE . str_pad(" " . strtoupper($title) . " ", $lineLength, " ", STR_PAD_BOTH) . self::CLR_RESET . "\n";
        }

        // 2. Entête
        echo str_repeat("=", $lineLength) . "\n";
        echo self::fCell("Date", self::W_DATE, self::CLR_HEADER) . $sep .
             self::fCell("Contrat", self::W_CONTRAT, self::CLR_HEADER) . $sep .
             self::fCell("kWh", self::W_KWH, self::CLR_HEADER, STR_PAD_LEFT) . $sep .
             self::fCell("P.U. kWh", self::W_PRICE, self::CLR_HEADER, STR_PAD_LEFT) . $sep .
             self::fCell("Coût Tot.", self::W_COST, self::CLR_HEADER, STR_PAD_LEFT) . "\n";
        echo str_repeat("-", $lineLength) . "\n";

        // 3. Corps
        foreach ($summary['daily_details'] as $row) {
            echo self::fCell($row['date'], self::W_DATE) . $sep .
                 self::fCell($row['contract'], self::W_CONTRAT) . $sep .
                 self::fCell(number_format($row['kwh'], 2, '.', ''), self::W_KWH, self::CLR_KWH, STR_PAD_LEFT) . $sep .
                 self::fCell(number_format($row['unit_price'] ?? 0, 4, '.', ''), self::W_PRICE, self::CLR_PRICE, STR_PAD_LEFT) . $sep .
                 self::fCell(number_format($row['daily_cost'], 2, '.', '') . " €", self::W_COST, self::CLR_COST, STR_PAD_LEFT) . "\n";
        }

        // 4. Pied de tableau
        echo str_repeat("-", $lineLength) . "\n";
        echo self::fCell("TOTAL", self::W_DATE, self::CLR_BOLD) . $sep .
             self::fCell("", self::W_CONTRAT) . $sep .
             self::fCell(number_format($summary['totals']['kwh'], 2, '.', ''), self::W_KWH, self::CLR_TOTAL, STR_PAD_LEFT) . $sep .
             self::fCell("", self::W_PRICE) . $sep .
             self::fCell(number_format($summary['totals']['cost'], 2, '.', '') . " €", self::W_COST, self::CLR_COST, STR_PAD_LEFT) . "\n";
        echo str_repeat("=", $lineLength) . "\n";

        // 5. Bloc de détails financiers (si disponibles)
        if (isset($summary['totals']['kwh_cost'])) {
            $t = $summary['totals'];
            echo "\n" . self::CLR_BOLD . "DÉTAILS FINANCIERS :" . self::CLR_RESET . "\n";
            printf("  • Part Variable (Consommation) : %s\n", self::CLR_KWH . number_format($t['kwh_cost'], 2, '.', '') . " €" . self::CLR_RESET);
            printf("  • Part Fixe (Abonnement)      : %s\n", self::CLR_SUB . number_format($t['subscription_cost'], 2, '.', '') . " €" . self::CLR_RESET);
            printf("  • P.U. Moyen du kWh net       : %s\n", self::CLR_PRICE . number_format($t['avg_kwh_price'], 4, '.', '') . " €" . self::CLR_RESET);
            printf("  • Coût Moyen Abonnement/mois  : %s\n", self::CLR_SUB . number_format($t['avg_monthly_sub'], 2, '.', '') . " €" . self::CLR_RESET);
            echo str_repeat("-", 40) . "\n\n";
        }
    }
}
