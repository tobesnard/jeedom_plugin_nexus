<?php

require_once __DIR__ . '/../../vendor/autoload.php'; // Ajuste le chemin

use Nexus\Utils\Utils;

/**
 * Mock minimaliste pour les classes Jeedom si exécuté hors core
 */
if (!class_exists('cmd')) {
    class cmd
    {
        public static function byId($id)
        {
            return new self();
        }
        public function getIsHistorized()
        {
            return true;
        }
        public function getStatistique($s, $e)
        {
            return ['min' => 12.5, 'max' => 22.8];
        }
    }
}

if (!class_exists('dataStore')) {
    class dataStore
    {
        public static function byTypeLinkIdKey($a, $b, $c)
        {
            return new self();
        }
        public function getValue()
        {
            return 'Réponse TEST';
        }
        public function remove()
        {
            return true;
        }
    }
}

if (!class_exists('scenarioExpression')) {
    class scenarioExpression
    {
        public static function createAndExec($a, $b, $c)
        {
            return true;
        }
    }
}

// --- DEBUT DES TESTS ---

echo "=== Test Nexus\Utils\Utils ===\n\n";

// 1. Test escapeChar
$regex = "valeur (10)";
echo "1. escapeChar : " . (Utils::escapeChar($regex) === "valeur \(10\)" ? "OK" : "FAIL") . " (" . Utils::escapeChar($regex) . ")\n";

// 2. Test min_between / max_between
echo "2. min_between : " . (Utils::min_between("#123#", "now", "now") === 12.5 ? "OK" : "FAIL") . "\n";
echo "3. max_between : " . (Utils::max_between("123", "now", "now") === 22.8 ? "OK" : "FAIL") . "\n";

// 4. Test uniform
$dirty = " ÉTÉ à l'Haÿ-les-Roses ";
$expected = "ete a l'hay-les-roses";
echo "4. uniform : " . (Utils::uniform($dirty) === $expected ? "OK" : "FAIL") . " (" . Utils::uniform($dirty) . ")\n";

// 5. Test extract_notification_value
$json = '{"title":"Alerte", "value":"Le lave-linge est fini"}';
echo "5. extract (JSON) : " . (Utils::extract_notification_value($json) === "Le lave-linge est fini" ? "OK" : "FAIL") . "\n";

$raw = 'Ceci est une valeur directe sans json';
echo "6. extract (RAW) : " . (Utils::extract_notification_value($raw) === $raw ? "OK" : "FAIL") . "\n";

// 6. Test formatHeure
echo "7. formatHeure (int) : " . (Utils::formatHeure(930) === "09:30" ? "OK" : "FAIL") . "\n";
echo "8. formatHeure (string) : " . (Utils::formatHeure("2215") === "22:15" ? "OK" : "FAIL") . "\n";

// 7. Test askTelegram
echo "9. askTelegram : " . (Utils::askTelegram("Titre", "Oui;Non", 30) === "reponse test" ? "OK" : "FAIL") . "\n";
