<?php

require_once __DIR__ . '/../class/TV.php';


$mixedVolume_cmd = '#[Scripts][Philips TV][Volume]#';


function philipsTV_version()
{
    $philipsTV = Nexus\Multimedia\PhilipsTV\TV::getInstance();
    return "version " . $philipsTV->version();
}

function ambihue_on()
{
    $philipsTV = Nexus\Multimedia\PhilipsTV\TV::getInstance();
    $philipsTV->action('ambihue_on');
}

function ambihue_off()
{
    $philipsTV = Nexus\Multimedia\PhilipsTV\TV::getInstance();
    $philipsTV->action('ambihue_off');
}

function ambihue_state()
{
    $philipsTV = Nexus\Multimedia\PhilipsTV\TV::getInstance();
    $status = $philipsTV->action('ambihue_status') ;
    $status =  json_decode($status) ;
    return $status->power === 'On' ? 1 : 0 ;
}

function ambilight_on()
{
    $philipsTV = Nexus\Multimedia\PhilipsTV\TV::getInstance();
    $philipsTV->action('ambilight_on');
    //$philipsTV->action('ambihue_on');
}

function ambilight_off()
{
    $philipsTV = Nexus\Multimedia\PhilipsTV\TV::getInstance();
    $philipsTV->action('ambilight_off');
    //$philipsTV->action('ambihue_off');
}

function ambilight_state()
{
    $philipsTV = Nexus\Multimedia\PhilipsTV\TV::getInstance();
    $status = $philipsTV->action('ambilight_status') ;
    $status =  json_decode($status) ;
    return $status->power === 'On' ? 1 : 0 ;
}

function ambilight_game()
{
    $philipsTV = Nexus\Multimedia\PhilipsTV\TV::getInstance();
    $philipsTV->action('ambilight_video_game');
}

function ambilight_standard()
{
    $philipsTV = Nexus\Multimedia\PhilipsTV\TV::getInstance();
    $philipsTV->action('ambilight_video_standard');
}

function philipsTV_on()
{
    $philipsTV = Nexus\Multimedia\PhilipsTV\TV::getInstance();
    $powerstate = $philipsTV->action('powerstate') ;
    $powerstate =  json_decode($powerstate) ;
    if ($powerstate->powerstate !== "On") {
        $philipsTV->action('standby');
    }
}

function philipsTV_off()
{
    $philipsTV = Nexus\Multimedia\PhilipsTV\TV::getInstance();
    $powerstate = $philipsTV->action('powerstate');
    $powerstate =  json_decode($powerstate) ;
    if ($powerstate->powerstate === "On") {
        $philipsTV->action('power_off');
    }
}

function philipsTV_state()
{
    $philipsTV = Nexus\Multimedia\PhilipsTV\TV::getInstance();
    $status = $philipsTV->action('powerstate') ;
    $status =  json_decode($status) ;
    return $status->powerstate === 'On' ? 1 : 0 ;
}

// function general_volume($_value){
// $philipsTV = Nexus\Multimedia\PhilipsTV\TV::getInstance();
//     $philipsTV->action('general_volume', intval( $_value ));
// }

// function headphones_volume($_value){
// $philipsTV = Nexus\Multimedia\PhilipsTV\TV::getInstance();
//     $philipsTV->action('headphones_volume', intval( $_value ));
// }

function philipsTV_mixed_volume($_value)
{
    $philipsTV = Nexus\Multimedia\PhilipsTV\TV::getInstance();
    $philipsTV->action('general_volume', intval($_value));
    $philipsTV->action('headphones_volume', intval($_value + 5));
}

function philipsTV_volume()
{
    $philipsTV = Nexus\Multimedia\PhilipsTV\TV::getInstance();
    $json = json_decode($philipsTV->action('volume'));
    return $json->current;
}

function philipsTV_turnUp_volume()
{
    $philipsTV = Nexus\Multimedia\PhilipsTV\TV::getInstance();
    // Volume actuelle + 5
    // $json = json_decode($philipsTV->action('volume'));
    // $current = $json->current + 5;
    $current = cmd::bystring($mixedVolume_cmd)->execCmd();
    $current += 8;
    philipsTV_mixed_volume($current);
}

function philipsTV_turnDown_volume()
{
    $philipsTV = Nexus\Multimedia\PhilipsTV\TV::getInstance();
    // $json = json_decode($philipsTV->action('volume'));
    // $current = $json->current - 5;
    $current = cmd::bystring($mixedVolume_cmd)->execCmd();
    $current -= 8;
    philipsTV_mixed_volume($current);
}

function philipsTV_tf1()
{
    $philipsTV = Nexus\Multimedia\PhilipsTV\TV::getInstance();
    $philipsTV->setChannel('TF1');
}

function philipsTV_france2()
{
    $philipsTV = Nexus\Multimedia\PhilipsTV\TV::getInstance();
    $philipsTV->setChannel('France 2');
}

function philipsTV_france3()
{
    $philipsTV = Nexus\Multimedia\PhilipsTV\TV::getInstance();
    $philipsTV->setChannel('F3 Centre');
}

function philipsTV_canalplus()
{
    $philipsTV = Nexus\Multimedia\PhilipsTV\TV::getInstance();
    $philipsTV->setChannel('CANAL+');
}

function philipsTV_france5()
{
    $philipsTV = Nexus\Multimedia\PhilipsTV\TV::getInstance();
    $philipsTV->setChannel('France 5');
}

function philipsTV_m6()
{
    $philipsTV = Nexus\Multimedia\PhilipsTV\TV::getInstance();
    $philipsTV->setChannel('M6');
}

function philipsTV_arte()
{
    $philipsTV = Nexus\Multimedia\PhilipsTV\TV::getInstance();
    $philipsTV->setChannel('Arte');
}

function philipsTV_c8()
{
    $philipsTV = Nexus\Multimedia\PhilipsTV\TV::getInstance();
    $philipsTV->setChannel('C8');
}

function philipsTV_w9()
{
    $philipsTV = Nexus\Multimedia\PhilipsTV\TV::getInstance();
    $philipsTV->setChannel('W9');
}

function philipsTV_tmc()
{
    $philipsTV = Nexus\Multimedia\PhilipsTV\TV::getInstance();
    $philipsTV->setChannel('TMC');
}

function philipsTV_tfx()
{
    $philipsTV = Nexus\Multimedia\PhilipsTV\TV::getInstance();
    $philipsTV->setChannel('TFX');
}

function philipsTV_nrj12()
{
    $philipsTV = Nexus\Multimedia\PhilipsTV\TV::getInstance();
    $philipsTV->setChannel('NRJ12');
}

function philipsTV_lcp()
{
    $philipsTV = Nexus\Multimedia\PhilipsTV\TV::getInstance();
    $philipsTV->setChannel('LCP');
}

function philipsTV_france4()
{
    $philipsTV = Nexus\Multimedia\PhilipsTV\TV::getInstance();
    $philipsTV->setChannel('France 4');
}

function philipsTV_bfmTV()
{
    $philipsTV = Nexus\Multimedia\PhilipsTV\TV::getInstance();
    $philipsTV->setChannel('BFM TV');
}

function philipsTV_cnews()
{
    $philipsTV = Nexus\Multimedia\PhilipsTV\TV::getInstance();
    $philipsTV->setChannel('CNEWS');
}

function philipsTV_cstar()
{
    $philipsTV = Nexus\Multimedia\PhilipsTV\TV::getInstance();
    $philipsTV->setChannel('CSTAR');
}

function philipsTV_gulli()
{
    $philipsTV = Nexus\Multimedia\PhilipsTV\TV::getInstance();
    $philipsTV->setChannel('Gulli');
}

function philipsTV_tf1Series()
{
    $philipsTV = Nexus\Multimedia\PhilipsTV\TV::getInstance();
    $philipsTV->setChannel('TF1 Séries Films');
}

function philipsTV_lEquipe()
{
    $philipsTV = Nexus\Multimedia\PhilipsTV\TV::getInstance();
    $philipsTV->setChannel('L\'Equipe');
}

function philipsTV_6ter()
{
    $philipsTV = Nexus\Multimedia\PhilipsTV\TV::getInstance();
    $philipsTV->setChannel('6ter');
}

function philipsTV_rmcStory()
{
    $philipsTV = Nexus\Multimedia\PhilipsTV\TV::getInstance();
    $philipsTV->setChannel('RMC STORY');
}

function philipsTV_rmcDecouverte()
{
    $philipsTV = Nexus\Multimedia\PhilipsTV\TV::getInstance();
    $philipsTV->setChannel('RMC Découverte');
}

function philipsTV_cherie25()
{
    $philipsTV = Nexus\Multimedia\PhilipsTV\TV::getInstance();
    $philipsTV->setChannel('Chérie 25');
}

function philipsTV_lci()
{
    $philipsTV = Nexus\Multimedia\PhilipsTV\TV::getInstance();
    $philipsTV->setChannel('LCI');
}

function philipsTV_franceinfo()
{
    $philipsTV = Nexus\Multimedia\PhilipsTV\TV::getInstance();
    $philipsTV->setChannel('franceinfo:');
}

function philipsTV_tvTours()
{
    $philipsTV = Nexus\Multimedia\PhilipsTV\TV::getInstance();
    $philipsTV->setChannel('TV Tours');
}

function philipsTV_parisPremiere()
{
    $philipsTV = Nexus\Multimedia\PhilipsTV\TV::getInstance();
    $philipsTV->setChannel('PARIS PREMIERE');
}

function philipsTV_debug()
{
    $philipsTV = Nexus\Multimedia\PhilipsTV\TV::getInstance();
    // echo var_dump($philipsTV->actionsJSON);
}

function philipsTV_hdmi_1()
{
    $philipsTV = Nexus\Multimedia\PhilipsTV\TV::getInstance();
    $philipsTV->action('input_hdmi_1');
}

function philipsTV_watch_tv()
{
    $philipsTV = Nexus\Multimedia\PhilipsTV\TV::getInstance();
    $philipsTV->action('watch_tv');
}
