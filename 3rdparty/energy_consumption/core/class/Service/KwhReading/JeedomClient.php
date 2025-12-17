<?php

namespace Nexus\Energy\Electricity\Service\KwhReading;

/**
 * Wrapper minimal autour des appels Jeedom globaux.
 * Permet d'isoler l'accès à l'API Jeedom pour faciliter le mocking en tests.
 */
class JeedomClient
{
    /**
     * Retourne l'objet commande Jeedom par son id.
     *
     * @param int $id
     * @return object|null
     */
    public function getCmdById(int $id)
    {
        return \cmd::byId($id);
    }

    /**
     * Retourne l'historique pour une commande sur une plage donnée.
     *
     * @param int $cmdId
     * @param string $start
     * @param string $end
     * @return array
     */
    public function getHistory(int $cmdId, string $start, string $end): array
    {
        return \history::all($cmdId, $start, $end);
    }
}
