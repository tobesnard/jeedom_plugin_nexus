<?php

require_once __DIR__ . '/../class/TV.php';
require_once "/var/www/html/core/php/core.inc.php";

/**
 * Helper interne pour l'exécution sécurisée et le logging.
 * @param callable $callback
 * @param mixed $default
 * @return mixed
 */
function _philipsTV_execute(callable $callback, $default = null)
{
    try {
        return $callback();
    } catch (Throwable $e) {
        // error_log("PhilipsTV Exception: " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine());
        log::add('nexus', 'error', "PhilipsTV Exception : " . $e->getMessage());
        return $default;
    }
}

/**
 * Méthode Proxy : Récupère la version du wrapper API.
 * @return string|null
 */
function philipsTV_version()
{
    return _philipsTV_execute(function () {
        return Nexus\Multimedia\PhilipsTV\TV::getInstance()->version();
    });
}

/**
 * Méthode Proxy : Active la synchronisation Ambilight + Hue.
 */
function philipsTV_ambihueOn()
{
    _philipsTV_execute(function () {
        Nexus\Multimedia\PhilipsTV\TV::getInstance()->action('ambihue_on');
    });
}

/**
 * Méthode Proxy : Désactive la synchronisation Ambilight + Hue.
 */
function philipsTV_ambihueOff()
{
    _philipsTV_execute(function () {
        Nexus\Multimedia\PhilipsTV\TV::getInstance()->action('ambihue_off');
    });
}

/**
 * Méthode Proxy : Récupère l'état de Ambilight + Hue.
 * @return int (1 pour On, 0 pour Off)
 */
function philipsTV_ambihueState()
{
    return _philipsTV_execute(function () {
        $philipsTV = Nexus\Multimedia\PhilipsTV\TV::getInstance();
        $status = json_decode($philipsTV->action('ambihue_status'));
        return (isset($status->power) && $status->power === 'On') ? 1 : 0;
    }, 0);
}

/**
 * Méthode Proxy : Active l'Ambilight.
 */
function philipsTV_ambilightOn()
{
    _philipsTV_execute(function () {
        Nexus\Multimedia\PhilipsTV\TV::getInstance()->action('ambilight_on');
    });
}

/**
 * Méthode Proxy : Désactive l'Ambilight.
 */
function philipsTV_ambilightOff()
{
    _philipsTV_execute(function () {
        Nexus\Multimedia\PhilipsTV\TV::getInstance()->action('ambilight_off');
    });
}

/**
 * Méthode Proxy : Récupère l'état d'alimentation de l'Ambilight.
 * @return int (1 pour On, 0 pour Off)
 */
function philipsTV_ambilightState()
{
    return _philipsTV_execute(function () {
        $philipsTV = Nexus\Multimedia\PhilipsTV\TV::getInstance();
        $status = json_decode($philipsTV->action('ambilight_status'));
        return (isset($status->power) && $status->power === 'On') ? 1 : 0;
    }, 0);
}

/**
 * Méthode Proxy : Définit le mode Ambilight sur "Jeu".
 */
function philipsTV_ambilightGame()
{
    _philipsTV_execute(function () {
        Nexus\Multimedia\PhilipsTV\TV::getInstance()->action('ambilight_video_game');
    });
}

/**
 * Méthode Proxy : Définit le mode Ambilight sur "Standard".
 */
function philipsTV_ambilightStandard()
{
    _philipsTV_execute(function () {
        Nexus\Multimedia\PhilipsTV\TV::getInstance()->action('ambilight_video_standard');
    });
}

/**
 * Méthode Proxy : Allume la TV ou la sort de veille.
 */
function philipsTV_on()
{
    _philipsTV_execute(function () {
        $philipsTV = Nexus\Multimedia\PhilipsTV\TV::getInstance();
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
    _philipsTV_execute(function () {
        $philipsTV = Nexus\Multimedia\PhilipsTV\TV::getInstance();
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
    return _philipsTV_execute(function () {
        $philipsTV = Nexus\Multimedia\PhilipsTV\TV::getInstance();
        $status = json_decode($philipsTV->action('powerstate'));
        return (isset($status->powerstate) && $status->powerstate === 'On') ? 1 : 0;
    }, 0);
}

/**
 * Méthode Proxy : Règle simultanément le volume général et le volume casque.
 * @param int $_value Valeur de base pour le volume.
 */
function philipsTV_mixedVolume($_value)
{
    _philipsTV_execute(function () use ($_value) {
        $philipsTV = Nexus\Multimedia\PhilipsTV\TV::getInstance();
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
    return _philipsTV_execute(function () {
        $philipsTV = Nexus\Multimedia\PhilipsTV\TV::getInstance();
        $json = json_decode($philipsTV->action('volume'));
        return $json->current ?? 0;
    }, 0);
}

/**
 * Méthode Proxy : Augmente le volume mixte de 8 unités.
 */
function philipsTV_turnUpVolume()
{
    _philipsTV_execute(function () {
        $current = (int) philipsTV_volume();
        philipsTV_mixedVolume($current + 8);
    });
}

/**
 * Méthode Proxy : Diminue le volume mixte de 8 unités.
 */
function philipsTV_turnDownVolume()
{
    _philipsTV_execute(function () {
        $current = (int) philipsTV_volume();
        philipsTV_mixedVolume($current - 8);
    });
}

// --- Méthodes Proxy : Sélection des chaînes TNT ---

function philipsTV_tf1()
{
    _philipsTV_execute(function () { Nexus\Multimedia\PhilipsTV\TV::getInstance()->setChannel('TF1'); });
}
function philipsTV_france2()
{
    _philipsTV_execute(function () { Nexus\Multimedia\PhilipsTV\TV::getInstance()->setChannel('France 2'); });
}
function philipsTV_france3()
{
    _philipsTV_execute(function () { Nexus\Multimedia\PhilipsTV\TV::getInstance()->setChannel('F3 Centre'); });
}
function philipsTV_canalplus()
{
    _philipsTV_execute(function () { Nexus\Multimedia\PhilipsTV\TV::getInstance()->setChannel('CANAL+'); });
}
function philipsTV_france5()
{
    _philipsTV_execute(function () { Nexus\Multimedia\PhilipsTV\TV::getInstance()->setChannel('France 5'); });
}
function philipsTV_m6()
{
    _philipsTV_execute(function () { Nexus\Multimedia\PhilipsTV\TV::getInstance()->setChannel('M6'); });
}
function philipsTV_arte()
{
    _philipsTV_execute(function () { Nexus\Multimedia\PhilipsTV\TV::getInstance()->setChannel('Arte'); });
}
function philipsTV_c8()
{
    _philipsTV_execute(function () { Nexus\Multimedia\PhilipsTV\TV::getInstance()->setChannel('C8'); });
}
function philipsTV_w9()
{
    _philipsTV_execute(function () { Nexus\Multimedia\PhilipsTV\TV::getInstance()->setChannel('W9'); });
}
function philipsTV_tmc()
{
    _philipsTV_execute(function () { Nexus\Multimedia\PhilipsTV\TV::getInstance()->setChannel('TMC'); });
}
function philipsTV_tfx()
{
    _philipsTV_execute(function () { Nexus\Multimedia\PhilipsTV\TV::getInstance()->setChannel('TFX'); });
}
function philipsTV_nrj12()
{
    _philipsTV_execute(function () { Nexus\Multimedia\PhilipsTV\TV::getInstance()->setChannel('NRJ12'); });
}
function philipsTV_lcp()
{
    _philipsTV_execute(function () { Nexus\Multimedia\PhilipsTV\TV::getInstance()->setChannel('LCP'); });
}
function philipsTV_france4()
{
    _philipsTV_execute(function () { Nexus\Multimedia\PhilipsTV\TV::getInstance()->setChannel('France 4'); });
}
function philipsTV_bfmTV()
{
    _philipsTV_execute(function () { Nexus\Multimedia\PhilipsTV\TV::getInstance()->setChannel('BFM TV'); });
}
function philipsTV_cnews()
{
    _philipsTV_execute(function () { Nexus\Multimedia\PhilipsTV\TV::getInstance()->setChannel('CNEWS'); });
}
function philipsTV_cstar()
{
    _philipsTV_execute(function () { Nexus\Multimedia\PhilipsTV\TV::getInstance()->setChannel('CSTAR'); });
}
function philipsTV_gulli()
{
    _philipsTV_execute(function () { Nexus\Multimedia\PhilipsTV\TV::getInstance()->setChannel('Gulli'); });
}
function philipsTV_tf1Series()
{
    _philipsTV_execute(function () { Nexus\Multimedia\PhilipsTV\TV::getInstance()->setChannel('TF1 Séries Films'); });
}
function philipsTV_lEquipe()
{
    _philipsTV_execute(function () { Nexus\Multimedia\PhilipsTV\TV::getInstance()->setChannel('L\'Equipe'); });
}
function philipsTV_6ter()
{
    _philipsTV_execute(function () { Nexus\Multimedia\PhilipsTV\TV::getInstance()->setChannel('6ter'); });
}
function philipsTV_rmcStory()
{
    _philipsTV_execute(function () { Nexus\Multimedia\PhilipsTV\TV::getInstance()->setChannel('RMC STORY'); });
}
function philipsTV_rmcDecouverte()
{
    _philipsTV_execute(function () { Nexus\Multimedia\PhilipsTV\TV::getInstance()->setChannel('RMC Découverte'); });
}
function philipsTV_cherie25()
{
    _philipsTV_execute(function () { Nexus\Multimedia\PhilipsTV\TV::getInstance()->setChannel('Chérie 25'); });
}
function philipsTV_lci()
{
    _philipsTV_execute(function () { Nexus\Multimedia\PhilipsTV\TV::getInstance()->setChannel('LCI'); });
}
function philipsTV_franceinfo()
{
    _philipsTV_execute(function () { Nexus\Multimedia\PhilipsTV\TV::getInstance()->setChannel('franceinfo:'); });
}
function philipsTV_tvTours()
{
    _philipsTV_execute(function () { Nexus\Multimedia\PhilipsTV\TV::getInstance()->setChannel('TV Tours'); });
}
function philipsTV_parisPremiere()
{
    _philipsTV_execute(function () { Nexus\Multimedia\PhilipsTV\TV::getInstance()->setChannel('PARIS PREMIERE'); });
}

/**
 * Méthode Proxy : Méthode de débogage (interne).
 */
function philipsTV_debug()
{
    _philipsTV_execute(function () {
        Nexus\Multimedia\PhilipsTV\TV::getInstance()->debug();
    });
}

/**
 * Méthode Proxy : Bascule la source sur HDMI 1.
 */
function philipsTV_hdmi1()
{
    _philipsTV_execute(function () {
        Nexus\Multimedia\PhilipsTV\TV::getInstance()->action('input_hdmi_1');
    });
}

/**
 * Méthode Proxy : Repasse la TV en mode réception antenne (Regarder la TV).
 */
function philipsTV_watchTV()
{
    _philipsTV_execute(function () {
        Nexus\Multimedia\PhilipsTV\TV::getInstance()->action('watch_tv');
    });
}
