<?php

require_once __DIR__ . '/../class/TV.php';

$mixedVolume_cmd = '#[Scripts][Philips TV][Volume]#';

class PhilipsTV
{
    private const CONFIG_FILE = __DIR__ . "/../config/philipstv_config.json";
    private static string $mac;

    public static function getInstance()
    {
        self::loadConfig();
        self::sendWakeOnLan();
        return Nexus\Multimedia\PhilipsTV\TV::getInstance();
    }

    /**
     * Envoie un paquet Wake-on-LAN pour réveiller la carte réseaux du téléviseur.
     */
    private static function sendWakeOnLan(?string $macAddress = null): void
    {
        $targetMac = $macAddress ?? self::$mac;
        $safeMac = escapeshellarg($targetMac);
        $command = "wakeonlan $safeMac >/dev/null 2>&1";
        exec($command);
    }

    /**
    * charge la configuration du téléviseur
    */
    private static function loadConfig(): void
    {

        if (!file_exists(self::CONFIG_FILE)) {
            throw new Exception("Fichier de configuration introuvable : " . self::CONFIG_FILE);
        }

        $jsonContent = file_get_contents(self::CONFIG_FILE);
        $data = json_decode($jsonContent, true);

        if (isset($data['mac'])) {
            self::$mac = $data['mac'];
        } else {
            throw new Exception("L'adresse MAC est manquante dans le fichier JSON.");
        }
    }
}


function philipsTV_version()
{
    $philipsTV = PhilipsTV::getInstance();
    return $philipsTV->version();
}

function philipsTV_ambihueOn()
{
    $philipsTV = PhilipsTV::getInstance();
    $philipsTV->action('ambihue_on');
}

function philipsTV_ambihueOff()
{
    $philipsTV = PhilipsTV::getInstance();
    $philipsTV->action('ambihue_off');
}

function philipsTV_ambihueState()
{
    $philipsTV = PhilipsTV::getInstance();
    $status = $philipsTV->action('ambihue_status') ;
    $status =  json_decode($status) ;
    return $status->power === 'On' ? 1 : 0 ;
}

function philipsTV_ambilightOn()
{
    $philipsTV = PhilipsTV::getInstance();
    $philipsTV->action('ambilight_on');
    //$philipsTV->action('ambihue_on');
}

function philipsTV_ambilightOff()
{
    $philipsTV = PhilipsTV::getInstance();
    $philipsTV->action('ambilight_off');
    //$philipsTV->action('ambihue_off');
}

function philipsTV_ambilightState()
{
    $philipsTV = PhilipsTV::getInstance();
    $status = $philipsTV->action('ambilight_status') ;
    $status =  json_decode($status) ;
    return $status->power === 'On' ? 1 : 0 ;
}

function philipsTV_ambilightGame()
{
    $philipsTV = PhilipsTV::getInstance();
    $philipsTV->action('ambilight_video_game');
}

function philipsTV_ambilightStandard()
{
    $philipsTV = PhilipsTV::getInstance();
    $philipsTV->action('ambilight_video_standard');
}

function philipsTV_on()
{
    $philipsTV = PhilipsTV::getInstance();
    $powerstate = $philipsTV->action('powerstate') ;
    $powerstate =  json_decode($powerstate) ;
    if ($powerstate->powerstate !== "On") {
        $philipsTV->action('standby');
    }
}

function philipsTV_off()
{
    $philipsTV = PhilipsTV::getInstance();
    $powerstate = $philipsTV->action('powerstate');
    $powerstate =  json_decode($powerstate) ;
    if ($powerstate->powerstate === "On") {
        $philipsTV->action('power_off');
    }
}

function philipsTV_state()
{
    $philipsTV = PhilipsTV::getInstance();
    $status = $philipsTV->action('powerstate') ;
    $status =  json_decode($status) ;
    return $status->powerstate === 'On' ? 1 : 0 ;
}

// function general_volume($_value){
// $philipsTV = PhilipsTV::getInstance();
//     $philipsTV->action('general_volume', intval( $_value ));
// }

// function headphones_volume($_value){
// $philipsTV = PhilipsTV::getInstance();
//     $philipsTV->action('headphones_volume', intval( $_value ));
// }

function philipsTV_mixedVolume($_value)
{
    $philipsTV = PhilipsTV::getInstance();
    $philipsTV->action('general_volume', intval($_value));
    $philipsTV->action('headphones_volume', intval($_value + 5));
}

function philipsTV_volume()
{
    $philipsTV = PhilipsTV::getInstance();
    $json = json_decode($philipsTV->action('volume'));
    return $json->current;
}

function philipsTV_turnUpVolume()
{
    $philipsTV = PhilipsTV::getInstance();
    // Volume actuelle + 5
    // $json = json_decode($philipsTV->action('volume'));
    // $current = $json->current + 5;
    $current = cmd::bystring($mixedVolume_cmd)->execCmd();
    $current += 8;
    philipsTV_mixed_volume($current);
}

function philipsTV_turnDownVolume()
{
    $philipsTV = PhilipsTV::getInstance();
    // $json = json_decode($philipsTV->action('volume'));
    // $current = $json->current - 5;
    $current = cmd::bystring($mixedVolume_cmd)->execCmd();
    $current -= 8;
    philipsTV_mixed_volume($current);
}

function philipsTV_tf1()
{
    $philipsTV = PhilipsTV::getInstance();
    $philipsTV->setChannel('TF1');
}

function philipsTV_france2()
{
    $philipsTV = PhilipsTV::getInstance();
    $philipsTV->setChannel('France 2');
}

function philipsTV_france3()
{
    $philipsTV = PhilipsTV::getInstance();
    $philipsTV->setChannel('F3 Centre');
}

function philipsTV_canalplus()
{
    $philipsTV = PhilipsTV::getInstance();
    $philipsTV->setChannel('CANAL+');
}

function philipsTV_france5()
{
    $philipsTV = PhilipsTV::getInstance();
    $philipsTV->setChannel('France 5');
}

function philipsTV_m6()
{
    $philipsTV = PhilipsTV::getInstance();
    $philipsTV->setChannel('M6');
}

function philipsTV_arte()
{
    $philipsTV = PhilipsTV::getInstance();
    $philipsTV->setChannel('Arte');
}

function philipsTV_c8()
{
    $philipsTV = PhilipsTV::getInstance();
    $philipsTV->setChannel('C8');
}

function philipsTV_w9()
{
    $philipsTV = PhilipsTV::getInstance();
    $philipsTV->setChannel('W9');
}

function philipsTV_tmc()
{
    $philipsTV = PhilipsTV::getInstance();
    $philipsTV->setChannel('TMC');
}

function philipsTV_tfx()
{
    $philipsTV = PhilipsTV::getInstance();
    $philipsTV->setChannel('TFX');
}

function philipsTV_nrj12()
{
    $philipsTV = PhilipsTV::getInstance();
    $philipsTV->setChannel('NRJ12');
}

function philipsTV_lcp()
{
    $philipsTV = PhilipsTV::getInstance();
    $philipsTV->setChannel('LCP');
}

function philipsTV_france4()
{
    $philipsTV = PhilipsTV::getInstance();
    $philipsTV->setChannel('France 4');
}

function philipsTV_bfmTV()
{
    $philipsTV = PhilipsTV::getInstance();
    $philipsTV->setChannel('BFM TV');
}

function philipsTV_cnews()
{
    $philipsTV = PhilipsTV::getInstance();
    $philipsTV->setChannel('CNEWS');
}

function philipsTV_cstar()
{
    $philipsTV = PhilipsTV::getInstance();
    $philipsTV->setChannel('CSTAR');
}

function philipsTV_gulli()
{
    $philipsTV = PhilipsTV::getInstance();
    $philipsTV->setChannel('Gulli');
}

function philipsTV_tf1Series()
{
    $philipsTV = PhilipsTV::getInstance();
    $philipsTV->setChannel('TF1 Séries Films');
}

function philipsTV_lEquipe()
{
    $philipsTV = PhilipsTV::getInstance();
    $philipsTV->setChannel('L\'Equipe');
}

function philipsTV_6ter()
{
    $philipsTV = PhilipsTV::getInstance();
    $philipsTV->setChannel('6ter');
}

function philipsTV_rmcStory()
{
    $philipsTV = PhilipsTV::getInstance();
    $philipsTV->setChannel('RMC STORY');
}

function philipsTV_rmcDecouverte()
{
    $philipsTV = PhilipsTV::getInstance();
    $philipsTV->setChannel('RMC Découverte');
}

function philipsTV_cherie25()
{
    $philipsTV = PhilipsTV::getInstance();
    $philipsTV->setChannel('Chérie 25');
}

function philipsTV_lci()
{
    $philipsTV = PhilipsTV::getInstance();
    $philipsTV->setChannel('LCI');
}

function philipsTV_franceinfo()
{
    $philipsTV = PhilipsTV::getInstance();
    $philipsTV->setChannel('franceinfo:');
}

function philipsTV_tvTours()
{
    $philipsTV = PhilipsTV::getInstance();
    $philipsTV->setChannel('TV Tours');
}

function philipsTV_parisPremiere()
{
    $philipsTV = PhilipsTV::getInstance();
    $philipsTV->setChannel('PARIS PREMIERE');
}

function philipsTV_debug()
{
    $philipsTV = PhilipsTV::getInstance();
    // echo var_dump($philipsTV->actionsJSON);
}

function philipsTV_hdmi1()
{
    $philipsTV = PhilipsTV::getInstance();
    $philipsTV->action('input_hdmi_1');
}

function philipsTV_watchTV()
{
    $philipsTV = PhilipsTV::getInstance();
    $philipsTV->action('watch_tv');
}
