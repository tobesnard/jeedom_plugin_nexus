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
    Helpers::log('[openings_getState] Début', 'debug');
    return Helpers::execute(function () {
        $configFilePath = __DIR__ . "/../config/house_config.json";
        Helpers::log('[openings_getState] Chemin config : ' . $configFilePath, 'debug');

            $jeedomService = JeedomCmdService::getInstance();
        Helpers::log('[openings_getState] JeedomCmdService instancié', 'debug');

        $dataGenerator = HouseStateGenerator::fromJsonFile(
            $configFilePath,
            $jeedomService,
        );
        Helpers::log('[openings_getState] HouseStateGenerator créé', 'debug');

        $array = $dataGenerator->getArray();
        Helpers::log('[openings_getState] getArray() appelé', 'debug');

        $result = OpeningsManager::getStatusText($array);
        Helpers::log('[openings_getState] getStatusText() appelé', 'debug');

        return $result;
    }, "Erreur de récupération de l'état des ouvrants.");
}
