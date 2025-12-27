<?php

/**
 * Nexus Framework - Configuration des constantes
 */

define('PYTHON_EXECUTABLE', '/home/jeedom/.pyenv/shims/python3');

define('JEEDOM_ROOT', '/var/www/html');
define('JEEDOM_CORE', JEEDOM_ROOT . '/core/php/core.inc.php');

define('NEXUS_ROOT', '/var/www/html/plugins/nexus');
define('NEXUS_CORE', NEXUS_ROOT . '/core/php/nexus.inc.php');
define('NEXUS_TMP_DIR', '/tmp/nexus');

define('HUESYNC_ROOT', JEEDOM_ROOT . '/plugins/nexus/packages/huesync');

define('RCLONE_REMOTE_PATH', 'google-drive:Jeedom/Sauvegardes/');
