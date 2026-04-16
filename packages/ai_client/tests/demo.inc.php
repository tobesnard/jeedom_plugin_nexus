<?php
// Inclusion explicite du bootstrap pour charger le .env
require_once __DIR__ . '/../../../core/config/bootstrap.php';
error_log('[DEBUG][Entrypoint] getenv(GEMINI_API_KEY)=' . getenv('GEMINI_API_KEY'));
error_log('[DEBUG][Entrypoint] $_ENV[GEMINI_API_KEY]=' . ($_ENV['GEMINI_API_KEY'] ?? ''));

require_once __DIR__ . "/../core/php/aiclient.inc.php";

echo aiclient_gemini("1 + 1");
