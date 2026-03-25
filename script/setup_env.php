<?php

/**
 * NEXUS ENV GENERATOR
 * 
 * Cette classe automatise la création du fichier .env pour le plugin Nexus Jeedom.
 * Elle est conçue pour être exécutée par Composer lors des étapes post-install et post-update.
 */

class EnvGenerator
{
    /** @var string */
    private $envFile;

    /** @var string */
    private $exampleFile;

    /** @var array */
    private $headerLines;

    public function __construct()
    {
        $baseDir           = dirname(__DIR__);
        $this->envFile     = $baseDir . '/.env';
        $this->exampleFile = $baseDir . '/.env.example';
        $this->headerLines = [
            "# --- NEXUS PLUGIN ENV CONFIGURATION ---",
            "# Ce fichier est généré automatiquement par script/setup_env.php",
            "# Date de génération : " . date('Y-m-d H:i:s'),
            "",
        ];
    }

    /**
     * Point d'entrée principal pour la génération du fichier .env.
     * 
     * @return void
     */
    public function run()
    {
        $template = $this->getTemplateKeys();
        if (empty($template)) {
            $template = $this->getFallbackKeys();
        }
        $this->generateEnv($template);
    }

    /**
     * Extrait les clés depuis le fichier modèle (.env.example).
     * 
     * @return array
     */
    private function getTemplateKeys()
    {
        if (!file_exists($this->exampleFile)) {
            return [];
        }

        $keys  = [];
        $lines = file($this->exampleFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        foreach ($lines as $line) {
            $line = trim($line);
            if (strpos($line, '#') === 0 || strpos($line, '=') === false) {
                continue;
            }

            $parts = explode('=', $line);
            $key   = trim($parts[0]);
            if (!empty($key)) {
                $keys[] = $key;
            }
        }

        return $keys;
    }

    /**
     * Liste de secours si le fichier exemple est absent.
     * 
     * @return array
     */
    private function getFallbackKeys()
    {
        echo "[Nexus] Warning: .env.example not found or empty. Using core fallback keys.\n";
        return [
            'GEMINI_API_KEY',
            'CHATGPT_API_KEY',
            'COPILOT_API_KEY',
            'JEEDOM_API_KEY'
        ];
    }

    /**
     * Construis et sauvegarde le fichier .env.
     * 
     * @param array $template
     * @return void
     */
    private function generateEnv($template)
    {
        // On récupère les valeurs depuis le cache de configuration Jeedom SI on est dans le contexte Jeedom
        $jeedomConfig = [];
        if (class_exists('config')) {
            foreach ($template as $key) {
                $val = config::byKey(strtolower($key), 'nexus');
                if ($val !== null && $val !== '') {
                    $jeedomConfig[$key] = $val;
                }
            }
        }

        $envOutput = implode("\n", $this->headerLines) . "\n";
        $foundCount = 0;

        foreach ($template as $key) {
            $value = getenv($key);
            if ($value === false && isset($_ENV[$key])) {
                $value = $_ENV[$key];
            }
            if (($value === false || $value === '') && isset($jeedomConfig[$key])) {
                $value = $jeedomConfig[$key];
            }

            // Sécurité : si la valeur est un tableau (config Jeedom complexe), on extrait la première valeur
            // Jeedom retourne parfois [valeur, defaut]
            if (is_array($value)) {
                $value = $value[0] ?? '';
            }

            $envOutput .= "{$key}=" . ($value !== false ? $value : "") . "\n";
            if ($value !== false && $value !== '') {
                $foundCount++;
            }
        }

        if (file_put_contents($this->envFile, $envOutput) !== false) {
            echo "[Nexus] Success: .env file updated from template ($foundCount keys filled).\n";
        } else {
            exit(1);
        }
    }
}

// Exécution du générateur s'il n'a pas déjà été défini/lancé par le processus parent (Jeedom)
if (!class_exists('EnvGeneratorExists')) {
    class EnvGeneratorExists {}
    (new EnvGenerator())->run();
}


