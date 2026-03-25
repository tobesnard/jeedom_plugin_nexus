<?php

require_once __DIR__ . '/../../../vendor/autoload.php';

use Nexus\Jeedom\JeedomCmdService;
use Nexus\Openings\HouseStateGenerator;
use Nexus\Openings\OpeningsManager;

try {

    $stateFilePath = __DIR__ . "/../core/config/house_state_mocked.json";
    $json = file_get_contents($stateFilePath);
    $houseData = json_decode($json, true, 512, JSON_THROW_ON_ERROR);

    // 4. Synthèse IA
    $text = OpeningsManager::getStatusText($houseData);

    echo $text;

} catch (\Exception $e) {
    echo "Erreur système : " . $e->getMessage();
}
