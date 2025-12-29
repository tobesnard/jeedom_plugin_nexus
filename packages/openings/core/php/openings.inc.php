<?php

require_once __DIR__ . '/../../../../vendor/autoload.php';

use Nexus\Jeedom\Services\JeedomCmdService;
use Nexus\Openings\HouseStateGenerator;
use Nexus\Openings\OpeningsManager;
use Nexus\Utils\Helpers;

/**
 * Méthode Proxy : Récupère l'état des portes et fenêtre sous forme de phrase compréhensible par l'homme
 **/
function openings_getState()
{
    return Helpers::execute(function () {
        $configFilePath = __DIR__ . "/../config/house_config.json";
        $jeedomService = new JeedomCmdService();

        $dataGenerator = HouseStateGenerator::fromJsonFile(
            $configFilePath,
            $jeedomService,
        );

        return OpeningsManager::getStatusText($dataGenerator->getArray());
    }, "Erreur de récupération de l'état des ouvrants.");
}
