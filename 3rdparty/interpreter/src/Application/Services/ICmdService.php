<?php

namespace Interpreter\Application\Services;

/**
 * Interface pour les services de commande Jeedom
 *
 * Cette interface définit le contrat pour l'exécution et la gestion
 * des commandes et événements dans l'écosystème Jeedom.
 * Utilise le pattern Strategy pour permettre différentes implémentations.
 *
 * @author  Tony <tobesnard@gmail.com>
 * @since   1.0.0
 */
interface ICmdService
{
    /**
     * Exécute une commande par sa chaîne de caractères
     *
     * @param string $cmd La chaîne de commande à exécuter
     * @param array $options Options d'exécution optionnelles
     *
     * @return mixed Le résultat de l'exécution de la commande
     *
     * @throws \InvalidArgumentException Si la commande est invalide
     * @throws \RuntimeException Si l'exécution échoue
     */
    public function execByString(string $cmd, array $options = []);

    /**
     * Exécute une commande par son identifiant numérique
     *
     * @param int $id L'identifiant de la commande à exécuter
     * @param array $options Options d'exécution optionnelles
     *
     * @return mixed Le résultat de l'exécution de la commande
     *
     * @throws \InvalidArgumentException Si l'ID de commande est invalide
     * @throws \RuntimeException Si l'exécution échoue
     */
    public function execById(int $id, array $options = []);

    /**
     * Déclenche un événement par sa chaîne de caractères
     *
     * @param string $cmd La chaîne de l'événement à déclencher
     * @param mixed $value La valeur à associer à l'événement
     *
     * @return bool True si l'événement a été déclenché avec succès
     *
     * @throws \InvalidArgumentException Si l'événement est invalide
     * @throws \RuntimeException Si le déclenchement échoue
     */
    public function eventByString(string $cmd, $value): bool;

    /**
     * Déclenche un événement par son identifiant
     *
     * @param string $cmd L'identifiant de l'événement à déclencher
     * @param mixed $value La valeur à associer à l'événement
     *
     * @return bool True si l'événement a été déclenché avec succès
     *
     * @throws \InvalidArgumentException Si l'ID d'événement est invalide
     * @throws \RuntimeException Si le déclenchement échoue
     */
    public function eventById(int $cmd, $value): bool;

    /**
     *
     */
    public function log(string $logMessage): bool;
}
