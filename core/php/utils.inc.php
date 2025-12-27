<?php

require_once __DIR__ . "/../../vendor/autoload.php";

use Nexus\Utils\Helpers;
use Nexus\Utils\Utils;

/**
 * Méthode Proxy : Formate un entier HHMM en string HH:MM.
 */
function utils_formatHour($heure): string
{
    return Helpers::execute(function () use ($heure) {
        return Utils::formatHour($heure);
    }, "");
}

/**
 * Méthode Proxy : Échappe les caractères spéciaux pour Regex.
 */
function utils_escapeChar($str): string
{
    return Helpers::execute(function () use ($str) {
        return Utils::escapeChar($str);
    }, $str);
}

/**
 * Méthode Proxy : Uniformise le texte (minuscules, sans accents).
 */
function utils_uniform($str): string
{
    return Helpers::execute(function () use ($str) {
        return Utils::uniform($str);
    }, $str);
}

/**
 * Méthode Proxy : Extrait la valeur d'une notification JSON ou TTS.
 */
function utils_extractNotificationValue(...$args): string
{
    return Helpers::execute(function () use ($args) {
        return Utils::extractNotificationValue(...$args);
    }, "");
}

/**
 * Méthode Proxy : Interaction Telegram avec gestion de timeout.
 */
function utils_askTelegram(string $title, string $answers, int $timeout, ?string $variableName = null)
{
    return Helpers::execute(function () use ($title, $answers, $timeout, $variableName) {
        return Utils::askTelegram($title, $answers, $timeout, $variableName);
    });
}
