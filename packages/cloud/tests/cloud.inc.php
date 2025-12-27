<?php

require_once __DIR__ . "/../core/php/cloud.inc.php";

echo "Lancement de la sauvegarde sur le Cloud\n";

try {
    cloud_backup();
} catch (Exception $e) {
    echo $e->getMessage();
}
