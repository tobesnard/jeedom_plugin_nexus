<?php


// require_once __DIR__ . "/../../vendor/autoload.php";

require_once __DIR__ . "/../php/philiptv.inc.php";

// $philipsTV = Nexus\Multimedia\PhilipsTV\TV::getInstance();
// echo "version " . $philipsTV->version();
//

// shell_exec('wakeonlan 68:07:0a:29:b3:63');
echo philipsTV_version();
echo "\n";
// echo ambihue_state();
philipsTV_on();
