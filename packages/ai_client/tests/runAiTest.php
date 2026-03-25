<?php

require_once __DIR__ . '/../../../vendor/autoload.php';

use Nexus\AI\AIClient\AIFactory;

/**
 * Script de test fonctionnel optimisé
 */
function runAiTest(string $provider, string $prompt)
{
    $colors = [
        'blue'   => "\033[34m",
        'green'  => "\033[32m",
        'red'    => "\033[31m",
        'yellow' => "\033[33m",
        'reset'  => "\033[0m",
        'bold'   => "\033[1m",
    ];

    echo "{$colors['blue']}===> PROVIVDER: " . strtoupper($provider) . "{$colors['reset']}\n";

    try {
        $client = AIFactory::create($provider);

        $start = microtime(true);
        $memStart = memory_get_usage();

        $response = $client->query($prompt);

        $end = microtime(true);
        $memEnd = memory_get_usage();

        $duration = round($end - $start, 3);
        $memory = round(($memEnd - $memStart) / 1024, 2);

        echo "{$colors['green']}✔ SUCCESS{$colors['reset']} | Time: {$duration}s | Mem: {$memory}KB\n";
        echo "{$colors['bold']}Prompt:{$colors['reset']} $prompt\n";
        echo "{$colors['yellow']}Response:{$colors['reset']} " . trim($response) . "\n";

    } catch (\Exception $e) {
        echo "{$colors['red']}✘ FAILURE{$colors['reset']}\n";
        echo "Message: " . $e->getMessage() . "\n";
        echo "Trace: " . $e->getFile() . " on line " . $e->getLine() . "\n";
    }

    echo str_repeat("-", 50) . "\n\n";
}

// --- INITIALISATION ---

// Configuration du chemin vers le config.json
$filepath = __DIR__ . '/../core/config/config.json';

if (!file_exists($filepath)) {
    echo "\033[31m[CRITICAL]\033[0m Config file not found at: $filepath\n";
    exit(1);
}

// Détection du mode (CLI ou Web)
if (php_sapi_name() !== 'cli') {
    echo "<pre>";
}

$testPrompt = "Combien vaut 1 + 1 ? Réponds uniquement le chiffre.";

// --- EXÉCUTION ---

$providers = [
    'gemini',
    'chatgpt',
    'copilot',
];

foreach ($providers as $provider) {
    runAiTest($provider, $testPrompt);
}

if (php_sapi_name() !== 'cli') {
    echo "</pre>";
}
