<?php
// Inclusion explicite du bootstrap pour charger le .env
require_once __DIR__ . '/../../../core/config/bootstrap.php';

require_once __DIR__ . "/../core/php/aiclient.inc.php";

echo aiclient_gemini("1 + 1");
