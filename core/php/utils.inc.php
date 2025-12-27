<?php

require_once __DIR__ . "/../../vendor/autoload.php";
// require_once "/var/www/html/core/php/core.inc.php";

function utils_formatHour($heure)
{
    return Nexus\Utils\Utils::formatHour($heure);
}

// function utils_nop()
// {
//     ;
// }

function utils_escapeChar($str)
{
    return Nexus\Utils\Utils::escapeChar($str);
}

// function utils_minBetween($cmdId, $startDate, $endDate)
// {
//     return Nexus\Utils\Utils::minBetween($cmdId, $startDate, $endDate);
// }

// function utils_maxBetween($cmdId, $startDate, $endDate)
// {
//     return Nexus\Utils\Utils::maxBetween($cmdId, $startDate, $endDate);
// }

function utils_uniform($str)
{
    return Nexus\Utils\Utils::uniform($str);
}

function utils_extractNotificationValue(...$args)
{
    return Nexus\Utils\Utils::extractNotificationValue(...$args);
}

function utils_askTelegram(string $title, string $answers, int $timeout, ?string $variableName = null)
{
    return Nexus\Utils\Utils::askTelegram($title, $answers, $timeout, $variableName);
}
