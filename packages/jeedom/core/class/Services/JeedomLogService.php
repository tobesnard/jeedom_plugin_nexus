<?php

namespace Nexus\Jeedom\Services;

/**
 * Service de gestion des logs et messages Jeedom - Singleton
 *
 * Cette classe centralise toutes les interactions avec le système de log
 * natif de Jeedom et son centre de messages. Elle permet d'isoler la
 * responsabilité de la journalisation du reste de la logique métier.
 * * @author Tony <tobesnard@gmail.com>
 * @since 1.1.0
 */
class JeedomLogService implements ILogService
{
    /** @var self|null Instance unique du singleton */
    private static $instance = null;

    /**
     * Constructeur privé pour empêcher l'instanciation directe
     */
    private function __construct() {}

    /**
     * Empêcher le clonage de l'instance
     */
    private function __clone() {}

    /**
     * Récupère l'instance unique du service
     *
     * @return self
     */
    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Envoie un message dans le flux de log Jeedom
     *
     * @param string $message Le message à journaliser
     * @param string $level   Niveau de log (debug, info, warning, error)
     * @param string $channel Nom du fichier de log (défaut: 'nexus')
     * @return void
     */
    public function log(string $message, string $level = 'info', string $channel = 'nexus'): void
    {
        if (class_exists('\log')) {
            \log::add($channel, $level, $message);
        }
    }

    /**
     * Ajoute une notification dans le centre de messages de Jeedom
     *
     * @param string $title   Titre du message
     * @param string $message Contenu détaillé
     * @param string $channel Catégorie du message (défaut: 'nexus')
     * @return void
     */
    public function addMessage(string $title, string $message, string $channel = 'nexus'): void
    {
        if (class_exists('\message')) {
            \message::add($channel, sprintf("[%s] %s", $title, $message));
        }
    }

    /**
     * Journalisation structurée dédiée aux exécutions de commandes
     *
     * @param string $type    Type d'identifiant utilisé (string|id)
     * @param string $id      La commande ou l'ID concerné
     * @param array  $opts    Options de la commande (contexte)
     * @param bool   $success État de réussite de l'exécution
     * @return void
     */
    public function logExecution(string $type, string $id, array $opts, bool $success): void
    {
        return;

        $status = $success ? 'SUCCESS' : 'FAILED';
        $msg = sprintf("Exécution %s '%s' [%s]", $type, $id, $status);

        if (!empty($opts)) {
            $msg .= " | Options: " . json_encode($opts);
        }

        $this->log($msg, 'debug');
    }
}
