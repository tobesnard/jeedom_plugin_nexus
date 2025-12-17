<?php

require_once __DIR__ . '/../class/TV.php';

/**
 * Méthode Proxy : Récupère la version du wrapper API.
 * @return string
 */
function philipsTV_version()
{
    $philipsTV = Nexus\Multimedia\PhilipsTV\TV::getInstance();
    return $philipsTV->version();
}

/**
 * Méthode Proxy : Active la synchronisation Ambilight + Hue.
 */
function philipsTV_ambihueOn()
{
    $philipsTV = Nexus\Multimedia\PhilipsTV\TV::getInstance();
    $philipsTV->action('ambihue_on');
}

/**
 * Méthode Proxy : Désactive la synchronisation Ambilight + Hue.
 */
function philipsTV_ambihueOff()
{
    $philipsTV = Nexus\Multimedia\PhilipsTV\TV::getInstance();
    $philipsTV->action('ambihue_off');
}

/**
 * Méthode Proxy : Récupère l'état de Ambilight + Hue.
 * @return int (1 pour On, 0 pour Off)
 */
function philipsTV_ambihueState()
{
    $philipsTV = Nexus\Multimedia\PhilipsTV\TV::getInstance();
    $status = $philipsTV->action('ambihue_status');
    $status = json_decode($status);
    return (isset($status->power) && $status->power === 'On') ? 1 : 0;
}

/**
 * Méthode Proxy : Active l'Ambilight.
 */
function philipsTV_ambilightOn()
{
    $philipsTV = Nexus\Multimedia\PhilipsTV\TV::getInstance();
    $philipsTV->action('ambilight_on');
}

/**
 * Méthode Proxy : Désactive l'Ambilight.
 */
function philipsTV_ambilightOff()
{
    $philipsTV = Nexus\Multimedia\PhilipsTV\TV::getInstance();
    $philipsTV->action('ambilight_off');
}

/**
 * Méthode Proxy : Récupère l'état d'alimentation de l'Ambilight.
 * @return int (1 pour On, 0 pour Off)
 */
function philipsTV_ambilightState()
{
    $philipsTV = Nexus\Multimedia\PhilipsTV\TV::getInstance();
    $status = $philipsTV->action('ambilight_status');
    $status = json_decode($status);
    return (isset($status->power) && $status->power === 'On') ? 1 : 0;
}

/**
 * Méthode Proxy : Définit le mode Ambilight sur "Jeu".
 */
function philipsTV_ambilightGame()
{
    $philipsTV = Nexus\Multimedia\PhilipsTV\TV::getInstance();
    $philipsTV->action('ambilight_video_game');
}

/**
 * Méthode Proxy : Définit le mode Ambilight sur "Standard".
 */
function philipsTV_ambilightStandard()
{
    $philipsTV = Nexus\Multimedia\PhilipsTV\TV::getInstance();
    $philipsTV->action('ambilight_video_standard');
}

/**
 * Méthode Proxy : Allume la TV ou la sort de veille.
 */
function philipsTV_on()
{
    $philipsTV = Nexus\Multimedia\PhilipsTV\TV::getInstance();
    $powerstate = json_decode($philipsTV->action('powerstate'));
    if ($powerstate->powerstate !== "On") {
        $philipsTV->action('standby');
    }
}

/**
 * Méthode Proxy : Met la TV en veille.
 */
function philipsTV_off()
{
    $philipsTV = Nexus\Multimedia\PhilipsTV\TV::getInstance();
    $powerstate = json_decode($philipsTV->action('powerstate'));
    if ($powerstate->powerstate === "On") {
        $philipsTV->action('power_off');
    }
}

/**
 * Méthode Proxy : Récupère l'état d'alimentation principal de la TV.
 * @return int (1 pour On, 0 pour Off)
 */
function philipsTV_state()
{
    $philipsTV = Nexus\Multimedia\PhilipsTV\TV::getInstance();
    $status = json_decode($philipsTV->action('powerstate'));
    return ($status->powerstate === 'On') ? 1 : 0;
}

/**
 * Méthode Proxy : Règle simultanément le volume général et le volume casque.
 * @param int $_value Valeur de base pour le volume.
 */
function philipsTV_mixedVolume($_value)
{
    $philipsTV = Nexus\Multimedia\PhilipsTV\TV::getInstance();
    $philipsTV->action('general_volume', intval($_value));
    $philipsTV->action('headphones_volume', intval($_value + 5));
}

/**
 * Méthode Proxy : Récupère le niveau de volume actuel.
 * @return int
 */
function philipsTV_volume()
{
    $philipsTV = Nexus\Multimedia\PhilipsTV\TV::getInstance();
    $json = json_decode($philipsTV->action('volume'));
    return $json->current;
}

/**
 * Méthode Proxy : Augmente le volume mixte de 8 unités.
 */
function philipsTV_turnUpVolume()
{
    $current = (int) philipsTV_volume();
    philipsTV_mixedVolume($current + 8);
}

/**
 * Méthode Proxy : Diminue le volume mixte de 8 unités.
 */
function philipsTV_turnDownVolume()
{
    $current = (int) philipsTV_volume();
    philipsTV_mixedVolume($current - 8);
}

// --- Méthodes Proxy : Sélection des chaînes TNT ---

function philipsTV_tf1()
{
    Nexus\Multimedia\PhilipsTV\TV::getInstance()->setChannel('TF1');
}
function philipsTV_france2()
{
    Nexus\Multimedia\PhilipsTV\TV::getInstance()->setChannel('France 2');
}
function philipsTV_france3()
{
    Nexus\Multimedia\PhilipsTV\TV::getInstance()->setChannel('F3 Centre');
}
function philipsTV_canalplus()
{
    Nexus\Multimedia\PhilipsTV\TV::getInstance()->setChannel('CANAL+');
}
function philipsTV_france5()
{
    Nexus\Multimedia\PhilipsTV\TV::getInstance()->setChannel('France 5');
}
function philipsTV_m6()
{
    Nexus\Multimedia\PhilipsTV\TV::getInstance()->setChannel('M6');
}
function philipsTV_arte()
{
    Nexus\Multimedia\PhilipsTV\TV::getInstance()->setChannel('Arte');
}
function philipsTV_c8()
{
    Nexus\Multimedia\PhilipsTV\TV::getInstance()->setChannel('C8');
}
function philipsTV_w9()
{
    Nexus\Multimedia\PhilipsTV\TV::getInstance()->setChannel('W9');
}
function philipsTV_tmc()
{
    Nexus\Multimedia\PhilipsTV\TV::getInstance()->setChannel('TMC');
}
function philipsTV_tfx()
{
    Nexus\Multimedia\PhilipsTV\TV::getInstance()->setChannel('TFX');
}
function philipsTV_nrj12()
{
    Nexus\Multimedia\PhilipsTV\TV::getInstance()->setChannel('NRJ12');
}
function philipsTV_lcp()
{
    Nexus\Multimedia\PhilipsTV\TV::getInstance()->setChannel('LCP');
}
function philipsTV_france4()
{
    Nexus\Multimedia\PhilipsTV\TV::getInstance()->setChannel('France 4');
}
function philipsTV_bfmTV()
{
    Nexus\Multimedia\PhilipsTV\TV::getInstance()->setChannel('BFM TV');
}
function philipsTV_cnews()
{
    Nexus\Multimedia\PhilipsTV\TV::getInstance()->setChannel('CNEWS');
}
function philipsTV_cstar()
{
    Nexus\Multimedia\PhilipsTV\TV::getInstance()->setChannel('CSTAR');
}
function philipsTV_gulli()
{
    Nexus\Multimedia\PhilipsTV\TV::getInstance()->setChannel('Gulli');
}
function philipsTV_tf1Series()
{
    Nexus\Multimedia\PhilipsTV\TV::getInstance()->setChannel('TF1 Séries Films');
}
function philipsTV_lEquipe()
{
    Nexus\Multimedia\PhilipsTV\TV::getInstance()->setChannel('L\'Equipe');
}
function philipsTV_6ter()
{
    Nexus\Multimedia\PhilipsTV\TV::getInstance()->setChannel('6ter');
}
function philipsTV_rmcStory()
{
    Nexus\Multimedia\PhilipsTV\TV::getInstance()->setChannel('RMC STORY');
}
function philipsTV_rmcDecouverte()
{
    Nexus\Multimedia\PhilipsTV\TV::getInstance()->setChannel('RMC Découverte');
}
function philipsTV_cherie25()
{
    Nexus\Multimedia\PhilipsTV\TV::getInstance()->setChannel('Chérie 25');
}
function philipsTV_lci()
{
    Nexus\Multimedia\PhilipsTV\TV::getInstance()->setChannel('LCI');
}
function philipsTV_franceinfo()
{
    Nexus\Multimedia\PhilipsTV\TV::getInstance()->setChannel('franceinfo:');
}
function philipsTV_tvTours()
{
    Nexus\Multimedia\PhilipsTV\TV::getInstance()->setChannel('TV Tours');
}
function philipsTV_parisPremiere()
{
    Nexus\Multimedia\PhilipsTV\TV::getInstance()->setChannel('PARIS PREMIERE');
}

/**
 * Méthode Proxy : Méthode de débogage (interne).
 */
function philipsTV_debug()
{
    $philipsTV = Nexus\Multimedia\PhilipsTV\TV::getInstance();
    $philipsTV->debug();
}

/**
 * Méthode Proxy : Bascule la source sur HDMI 1.
 */
function philipsTV_hdmi1()
{
    $philipsTV = Nexus\Multimedia\PhilipsTV\TV::getInstance();
    $philipsTV->action('input_hdmi_1');
}

/**
 * Méthode Proxy : Repasse la TV en mode réception antenne (Regarder la TV).
 */
function philipsTV_watchTV()
{
    $philipsTV = Nexus\Multimedia\PhilipsTV\TV::getInstance();
    $philipsTV->action('watch_tv');
}
