<?php

namespace Nexus\Energy\Electricity\Service\KwhReading;

/**
 * Wrapper minimal autour des appels Jeedom globaux.
 *
 * Ce client centralise l'accès aux fonctions globales `\cmd` et `\history`
 * fournies par Jeedom. En injectant cette dépendance dans `JeedomKwhReading`
 * il devient possible de remplacer le client par un fake lors des tests.
 */
class JeedomClient
{
    /**
     * Retourne l'objet commande Jeedom par son id.
     *
     * @param int $id
     * @return object|null Objet commande Jeedom ou null si absent
     */
    public function getCmdById(int $id)
    {
        return \cmd::byId($id);
    }

    /**
     * Retourne l'historique pour une commande sur une plage donnée.
     *
     * @param int $cmdId
     * @param string $start Date/heure de début au format Y-m-d H:i:s
     * @param string $end Date/heure de fin au format Y-m-d H:i:s
     * @return array Liste d'objets historique comportant `getValue()` et `getDatetime()`.
     */
    public function getHistory(int $cmdId, string $start, string $end): array
    {
        return \history::all($cmdId, $start, $end);
    }
}
