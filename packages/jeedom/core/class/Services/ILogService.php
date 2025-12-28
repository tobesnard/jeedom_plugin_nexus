<?php

namespace Nexus\Jeedom\Services;

/**
 * Interface ILogService
 *
 * Contrat pour la gestion des logs et des notifications dans Jeedom.
 * Permet de découpler la logique de journalisation de son implémentation concrète.
 *
 * @author Tony <tobesnard@gmail.com>
 * @since 1.1.0
 */
interface ILogService
{
    /**
     * Envoie un message dans le flux de log Jeedom
     *
     * @param string $message Le message à journaliser
     * @param string $level   Niveau de log (debug, info, warning, error)
     * @param string $channel Nom du fichier de log
     * @return void
     */
    public function log(string $message, string $level = 'info', string $channel = 'nexus'): void;

    /**
     * Ajoute une notification dans le centre de messages de Jeedom
     *
     * @param string $title   Titre du message
     * @param string $message Contenu détaillé
     * @param string $channel Catégorie du message
     * @return void
     */
    public function addMessage(string $title, string $message, string $channel = 'nexus'): void;

    /**
     * Journalisation structurée dédiée aux exécutions de commandes
     *
     * @param string $type    Type d'identifiant utilisé (string|id)
     * @param string $id      La commande ou l'ID concerné
     * @param array  $opts    Options de la commande (contexte)
     * @param bool   $success État de réussite de l'exécution
     * @return void
     */
    public function logExecution(string $type, string $id, array $opts, bool $success): void;
}
