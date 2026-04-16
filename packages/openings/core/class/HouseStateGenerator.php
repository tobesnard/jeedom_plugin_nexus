<?php

declare(strict_types=1);

namespace Nexus\Openings;

use Nexus\Jeedom\Services\ICmdService;
use JsonSerializable;
use RuntimeException;

class HouseStateGenerator implements JsonSerializable
{
    private array $dataStructure = [];
    private ICmdService $cmdService;

    /**
     * @param array $config Configuration issue du JSON
     * @param ICmdService $cmdService Le service Jeedom (Réel ou Mock)
     */
    public function __construct(array $config, ICmdService $cmdService)
    {
        $this->cmdService = $cmdService;
        $this->parseConfig($config);
    }

    /**
     * Factory pour charger depuis le fichier avec injection de service
     */
    public static function fromJsonFile(string $filePath, ICmdService $cmdService): self
    {
        if (!file_exists($filePath)) {
            throw new RuntimeException("Fichier de configuration introuvable : $filePath");
        }

        $config = json_decode(file_get_contents($filePath), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new RuntimeException("Erreur JSON : " . json_last_error_msg());
        }

        return new self($config, $cmdService);
    }

    private function parseConfig(array $floors): void
    {
        foreach ($floors as $levelId => $floorData) {
            $floor = [
                'level' => $levelId,
                'label' => $floorData['label'] ?? $levelId,
                'rooms' => [],
            ];

            // On extrait les noms des pièces (clés du tableau) pour le tri
            $roomsRaw = $floorData['rooms'] ?? [];
            ksort($roomsRaw); // Tri par nom de pièce (clé : "salon", "entrée", etc.)

            foreach ($roomsRaw as $roomName => $openings) {
                $floor['rooms'][] = [
                    'room'     => $roomName,
                    'openings' => $this->hydrateOpenings($openings),
                ];
            }
            $this->dataStructure[] = $floor;
        }
    }

    private function hydrateOpenings(array $openings): array
    {
        // Tri par type d'ouvrant
        usort($openings, function ($a, $b) {
            return strcmp($a['type'] ?? '', $b['type'] ?? '');
        });

        return array_map(function ($opening) {
            $data = [
                'type'   => $opening['type'],
                'status' => $this->getJeedomValue((int) ($opening['cmd'] ?? 0)),
            ];

            if (isset($opening['name'])) {
                $data['name'] = $opening['name'];
            }

            return $data;
        }, $openings);
    }
    /**
     * IMPLÉMENTATION VIA JEEDOMCMDSERVICE
     */
    private function getJeedomValue(int $cmdId): string
    {
        try {
            // Utilisation du service injecté pour exécuter la commande par ID
            $value = $this->cmdService->execById($cmdId);

            // Jeedom retourne généralement 1 pour ouvert, 0 pour fermé pour les capteurs binaire
            // On s'assure de retourner le format 'open'/'closed' attendu par votre prompt IA
            return ($value == 1 || $value === '1' || $value === true) ? 'closed' : 'open';

        } catch (\Exception $e) {
            // Log de l'erreur via le service si disponible
            $this->cmdService->log("Erreur lecture ouvrant ID {$cmdId}: " . $e->getMessage());
            return 'unknown';
        }
    }

    public function jsonSerialize(): array
    {
        return ['house' => ['floors' => $this->dataStructure]];
    }

    public function getArray(): array
    {
        return $this->jsonSerialize();
    }
}
