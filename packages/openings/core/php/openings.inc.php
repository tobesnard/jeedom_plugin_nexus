<?php

require_once __DIR__ . '/../../../../vendor/autoload.php';

use Nexus\Jeedom\JeedomCmdService;
use Nexus\Openings\HouseStateGenerator;
use Nexus\Openings\OpeningsManager;

/**
 * Méthode Proxy : Récupère l'état des portes et fenêtre sous forme de phrase compréhensible par l'homme
 **/
function openings_getState()
{
    try {
        $configFilePath = __DIR__ . "/../config/house_config.json";
        $jeedomService = new JeedomCmdService();

        $dataGenerator = HouseStateGenerator::fromJsonFile(
            $configFilePath,
            $jeedomService,
        );

        return OpeningsManager::getStatusText($dataGenerator->getArray());
    } catch (\Exception $e) {
        echo "Erreur système : " . $e->getMessage();
    }
}
