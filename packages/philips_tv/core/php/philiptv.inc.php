<?php

require_once __DIR__ . '/../class/TV.php';

use Nexus\Multimedia\PhilipsTV\TV;
use Nexus\Utils\Helpers;

/**
 * Méthode Proxy : Récupère la version du wrapper API.
 * @return string|null
 */
function philipsTV_version()
{
    return Helpers::execute(function () {
        return TV::getInstance()->version();
    });
}

/**
 * Méthode Proxy : Active la synchronisation Ambilight + Hue.
 */
function philipsTV_ambihueOn()
{
    Helpers::execute(function () {
        TV::getInstance()->action('ambihue_on');
    });
}

/**
 * Méthode Proxy : Désactive la synchronisation Ambilight + Hue.
 */
function philipsTV_ambihueOff()
{
    Helpers::execute(function () {
        TV::getInstance()->action('ambihue_off');
    });
}

/**
 * Méthode Proxy : Récupère l'état de Ambilight + Hue.
 * @return int (1 pour On, 0 pour Off)
 */
function philipsTV_ambihueState()
{
    return Helpers::execute(function () {
        $status = json_decode(TV::getInstance()->action('ambihue_status'));
        return (isset($status->power) && $status->power === 'On') ? 1 : 0;
    }, 0);
}

/**
 * Méthode Proxy : Active l'Ambilight.
 */
function philipsTV_ambilightOn()
{
    Helpers::execute(function () {
        TV::getInstance()->action('ambilight_on');
    });
}

/**
 * Méthode Proxy : Désactive l'Ambilight.
 */
function philipsTV_ambilightOff()
{
    Helpers::execute(function () {
        TV::getInstance()->action('ambilight_off');
    });
}

/**
 * Méthode Proxy : Récupère l'état d'alimentation de l'Ambilight.
 * @return int (1 pour On, 0 pour Off)
 */
function philipsTV_ambilightState()
{
    return Helpers::execute(function () {
        $status = json_decode(TV::getInstance()->action('ambilight_status'));
        return (isset($status->power) && $status->power === 'On') ? 1 : 0;
    }, 0);
}

/**
 * Méthode Proxy : Définit le mode Ambilight sur "Jeu".
 */
function philipsTV_ambilightGame()
{
    Helpers::execute(function () {
        TV::getInstance()->action('ambilight_video_game');
    });
}

/**
 * Méthode Proxy : Définit le mode Ambilight sur "Standard".
 */
function philipsTV_ambilightStandard()
{
    Helpers::execute(function () {
        TV::getInstance()->action('ambilight_video_standard');
    });
}

/**
 * Méthode Proxy : Allume la TV ou la sort de veille.
 */
function philipsTV_on()
{
    Helpers::execute(function () {
        $philipsTV = TV::getInstance();
        $powerstate = json_decode($philipsTV->action('powerstate'));
        if (isset($powerstate->powerstate) && $powerstate->powerstate !== "On") {
            $philipsTV->action('standby');
        }
    });
}

/**
 * Méthode Proxy : Met la TV en veille.
 */
function philipsTV_off()
{
    Helpers::execute(function () {
        $philipsTV = TV::getInstance();
        $powerstate = json_decode($philipsTV->action('powerstate'));
        if (isset($powerstate->powerstate) && $powerstate->powerstate === "On") {
            $philipsTV->action('power_off');
        }
    });
}

/**
 * Méthode Proxy : Récupère l'état d'alimentation principal de la TV.
 * @return int (1 pour On, 0 pour Off)
 */
function philipsTV_state()
{
    return Helpers::execute(function () {
        $status = json_decode(TV::getInstance()->action('powerstate'));
        return (isset($status->powerstate) && $status->powerstate === 'On') ? 1 : 0;
    }, 0);
}

/**
 * Méthode Proxy : Règle simultanément le volume général et le volume casque.
 * @param int $_value Valeur de base pour le volume.
 */
function philipsTV_mixedVolume($_value)
{
    Helpers::execute(function () use ($_value) {
        $philipsTV = TV::getInstance();
        $philipsTV->action('general_volume', intval($_value));
        $philipsTV->action('headphones_volume', intval($_value + 5));
    });
}

/**
 * Méthode Proxy : Récupère le niveau de volume actuel.
 * @return int
 */
function philipsTV_volume()
{
    return Helpers::execute(function () {
        $json = json_decode(TV::getInstance()->action('volume'));
        return $json->current ?? 0;
    }, 0);
}

/**
 * Méthode Proxy : Augmente le volume mixte de 8 unités.
 */
function philipsTV_turnUpVolume()
{
    Helpers::execute(function () {
        $current = (int) philipsTV_volume();
        philipsTV_mixedVolume($current + 8);
    });
}

/**
 * Méthode Proxy : Diminue le volume mixte de 8 unités.
 */
function philipsTV_turnDownVolume()
{
    Helpers::execute(function () {
        $current = (int) philipsTV_volume();
        philipsTV_mixedVolume($current - 8);
    });
}

// --- Méthodes Proxy : Sélection des chaînes TNT ---

function philipsTV_tf1()
{
    Helpers::execute(function () { TV::getInstance()->setChannel("TF1"); });
}
function philipsTV_france2()
{
    Helpers::execute(function () { TV::getInstance()->setChannel("France 2"); });
}
function philipsTV_france3()
{
    Helpers::execute(function () { TV::getInstance()->setChannel("F3 Centre"); });
}
function philipsTV_canalplus()
{
    Helpers::execute(function () { TV::getInstance()->setChannel("CANAL+"); });
}
function philipsTV_france5()
{
    Helpers::execute(function () { TV::getInstance()->setChannel("France 5"); });
}
function philipsTV_m6()
{
    Helpers::execute(function () { TV::getInstance()->setChannel("M6"); });
}
function philipsTV_arte()
{
    Helpers::execute(function () { TV::getInstance()->setChannel("Arte"); });
}
function philipsTV_c8()
{
    Helpers::execute(function () { TV::getInstance()->setChannel("C8"); });
}
function philipsTV_w9()
{
    Helpers::execute(function () { TV::getInstance()->setChannel("W9"); });
}
function philipsTV_tmc()
{
    Helpers::execute(function () { TV::getInstance()->setChannel("TMC"); });
}
function philipsTV_tfx()
{
    Helpers::execute(function () { TV::getInstance()->setChannel("TFX"); });
}
function philipsTV_nrj12()
{
    Helpers::execute(function () { TV::getInstance()->setChannel("NRJ12"); });
}
function philipsTV_lcp()
{
    Helpers::execute(function () { TV::getInstance()->setChannel("LCP"); });
}
function philipsTV_france4()
{
    Helpers::execute(function () { TV::getInstance()->setChannel("France 4"); });
}
function philipsTV_bfmTV()
{
    Helpers::execute(function () { TV::getInstance()->setChannel("BFM TV"); });
}
function philipsTV_cnews()
{
    Helpers::execute(function () { TV::getInstance()->setChannel("CNEWS"); });
}
function philipsTV_cstar()
{
    Helpers::execute(function () { TV::getInstance()->setChannel("CSTAR"); });
}
function philipsTV_gulli()
{
    Helpers::execute(function () { TV::getInstance()->setChannel("Gulli"); });
}
function philipsTV_tf1Series()
{
    Helpers::execute(function () { TV::getInstance()->setChannel("TF1 Séries Films"); });
}
function philipsTV_lEquipe()
{
    Helpers::execute(function () { TV::getInstance()->setChannel("L'Equipe"); });
}
function philipsTV_6ter()
{
    Helpers::execute(function () { TV::getInstance()->setChannel("6ter"); });
}
function philipsTV_rmcStory()
{
    Helpers::execute(function () { TV::getInstance()->setChannel("RMC STORY"); });
}
function philipsTV_rmcDecouverte()
{
    Helpers::execute(function () { TV::getInstance()->setChannel("RMC Découverte"); });
}
function philipsTV_cherie25()
{
    Helpers::execute(function () { TV::getInstance()->setChannel("Chérie 25"); });
}
function philipsTV_lci()
{
    Helpers::execute(function () { TV::getInstance()->setChannel("LCI"); });
}
function philipsTV_franceinfo()
{
    Helpers::execute(function () { TV::getInstance()->setChannel("franceinfo:"); });
}
function philipsTV_tvTours()
{
    Helpers::execute(function () { TV::getInstance()->setChannel("TV Tours"); });
}
function philipsTV_parisPremiere()
{
    Helpers::execute(function () { TV::getInstance()->setChannel("PARIS PREMIERE"); });
}

/**
 * Méthode Proxy : Méthode de débogage (interne).
 */
function philipsTV_debug()
{
    Helpers::execute(function () {
        TV::getInstance()->debug();
    });
}

/**
 * Méthode Proxy : Bascule la source sur HDMI 1.
 */
function philipsTV_hdmi1()
{
    Helpers::execute(function () {
        TV::getInstance()->action('input_hdmi_1');
    });
}

/**
 * Méthode Proxy : Repasse la TV en mode réception antenne (Regarder la TV).
 */
function philipsTV_watchTV()
{
    Helpers::execute(function () {
        TV::getInstance()->action('watch_tv');
    });
}
