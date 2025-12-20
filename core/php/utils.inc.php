<?php

require_once __DIR__ . "/../../vendor/autoload.php";

function utils_formatHour($heure)
{
    return Nexus\Utils\Utils::formatHour($heure);
}

function nop()
{
    ;
}

function escapeChar($str)
{
    return Nexus\Utils\Utils::escapeChar($str);
}

function min_between($cmdId, $startDate, $endDate)
{
    return Nexus\Utils\Utils::min_between($cmdId, $startDate, $endDate);
}

function max_between($cmdId, $startDate, $endDate)
{
    return Nexus\Utils\Utils::max_between($cmdId, $startDate, $endDate);
}

function uniform($str)
{
    return Nexus\Utils\Utils::uniform($str);
}

function extract_notification_value(...$args)
{
    return Nexus\Utils\Utils::extract_notification_value(...$args);
}

function utils_askTelegram(string $title, string $answers, int $timeout, ?string $variableName = null)
{
    return Nexus\Utils\Utils::askTelegram($title, $answers, $timeout, $variableName);
}
