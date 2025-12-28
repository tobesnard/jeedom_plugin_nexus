<?php

// require_once __DIR__ . '/philipsHue.class.php';
require_once '/var/www/html/data/php/philipsHue/ePhilipsHue.class.php';
require_once '/var/www/html/core/php/core.inc.php';

trait edom_philipsHue
{
    private static $luminosite_rdc = '#[Salon][Ambiance RDC][Etat Luminosité]#';
    private static $luminosite_chambre = '#[Chambre][Ambiance Chambre][Etat Luminosité]#';
    private static $luminosite_bureau = '#[Bureau - Atelier][Ambiance Bureau][Etat Luminosité]#';

    public static function philipsHue_ring($_value)
    {
        $philipsHue = ePhilipsHue::getInstance();

        $settings = '{"dimming": {"brightness": "?"}}';
        $luminosity = (float) $philipsHue->getSetting('light', 'Hue Go', $settings) + $_value / 5;
        echo var_dump($luminosity);
        //$luminosity = cmd::bystring('#[Escalier][Hue Go][Etat Luminosité]#')->execCmd() + $_value/5 ;

        $luminosity = $luminosity <= 0 ? 0 : $luminosity;
        $luminosity = $luminosity >= 100 ? 100 : $luminosity;

        $settings = '{"dimming": {"brightness": '.$luminosity.'}}';
        $id = $philipsHue->getByName_id('Hue Go', 'light');
        return $philipsHue->putSetting('light', $id, $settings);

    }





    // Ambiance Chambre
    public static function philipsHue_ambianceChambre_brightnessState()
    {
        $type = 'grouped_light';
        $name = 'Ambiance Chambre';
        $setting = '{"dimming":{"brightness":"?"}}';
        $philipsHue = ePhilipsHue::getInstance();
        $value = intval($philipsHue->getSetting($type, $name, $setting));
        return floatval($value);
    }

    public static function philipsHue_ambianceChambre_brightness($_value)
    {
        $name = 'Ambiance Chambre';
        $type = 'zone';
        $rtype = 'grouped_light';
        $settings = '{"dimming": {"brightness": '.$_value.'}}';

        $philipsHue = ePhilipsHue::getInstance();
        $rid = $philipsHue->getByName_rid($name, $type, $rtype); //name, type, rtype
        return $philipsHue->putSetting($rtype, $rid, $settings);

    }

    public static function philipsHue_ambianceChambre_turnUpBrightness()
    {
        //$brightness = self::philipsHue_ambianceChambre_brightnessState();
        $brightness = cmd::bystring(static::$luminosite_chambre)->execCmd();
        $brightness += 25;
        $brightness = $brightness > 100 ? 100 : $brightness;
        return self::philipsHue_ambianceChambre_brightness($brightness);
    }

    public static function philipsHue_ambianceChambre_turnDownBrightness()
    {
        //$brightness = self::philipsHue_ambianceChambre_brightnessState();
        $brightness = cmd::bystring(static::$luminosite_chambre)->execCmd();
        $brightness -= 25;
        $brightness = $brightness < 0 ? 0 : $brightness;
        return self::philipsHue_ambianceChambre_brightness($brightness);
    }

    public static function philipsHue_ambianceChambre_main()
    {
        $philipsHue = ePhilipsHue::getInstance();
        $scene_id = $philipsHue->getByName_id('Main Chambre', 'scene');
        return $philipsHue->recall_scene($scene_id, true);
    }


    // Ambiance Bureau
    public static function philipsHue_ambianceBureau_brightnessState()
    {
        $type = 'grouped_light';
        $name = 'Ambiance Bureau';
        $setting = '{"dimming":{"brightness":"?"}}';
        $philipsHue = ePhilipsHue::getInstance();
        $value = intval($philipsHue->getSetting($type, $name, $setting));
        return floatval($value);
    }

    public static function philipsHue_ambianceBureau_brightness($_value)
    {
        $name = 'Ambiance Bureau';
        $type = 'zone';
        $rtype = 'grouped_light';
        $settings = '{"dimming": {"brightness": '.$_value.'}}';

        $philipsHue = ePhilipsHue::getInstance();
        $rid = $philipsHue->getByName_rid($name, $type, $rtype); //name, type, rtype
        return $philipsHue->putSetting($rtype, $rid, $settings);

    }

    public static function philipsHue_ambianceBureau_turnUpBrightness()
    {
        // $brightness = self::philipsHue_ambianceBureau_brightnessState();
        $brightness = cmd::bystring(static::$luminosite_bureau)->execCmd();
        $brightness += 25;
        $brightness = $brightness > 100 ? 100 : $brightness;
        return self::philipsHue_ambianceBureau_brightness($brightness);
    }

    public static function philipsHue_ambianceBureau_turnDownBrightness()
    {
        // $brightness = self::philipsHue_ambianceBureau_brightnessState();
        $brightness = cmd::bystring(static::$luminosite_bureau)->execCmd();
        $brightness -= 25;
        $brightness = $brightness < 0 ? 0 : $brightness;
        return self::philipsHue_ambianceBureau_brightness($brightness);
    }










    // Ambiance RDC
    public static function philipsHue_ambianceRDC_brightnessState()
    {
        $type = 'grouped_light';
        $name = 'Ambiance RDC';
        $setting = '{"dimming":{"brightness":"?"}}';
        $philipsHue = ePhilipsHue::getInstance();
        $value = intval($philipsHue->getSetting($type, $name, $setting));
        return floatval($value);
    }

    public static function philipsHue_ambianceRDC_brightness($_value)
    {
        $name = 'Ambiance RDC';
        $type = 'zone';
        $rtype = 'grouped_light';
        $settings = '{"dimming": {"brightness": '.$_value.'}}';

        $philipsHue = ePhilipsHue::getInstance();
        $rid = $philipsHue->getByName_rid($name, $type, $rtype); //name, type, rtype
        return $philipsHue->putSetting($rtype, $rid, $settings);

    }

    public static function philipsHue_ambianceRDC_turnUpBrightness()
    {
        // $brightness = self::philipsHue_ambianceRDC_brightnessState();
        $brightness = cmd::bystring(static::$luminosite_rdc)->execCmd();
        $brightness += 25;
        $brightness = $brightness > 100 ? 100 : $brightness;
        return self::philipsHue_ambianceRDC_brightness($brightness);
    }

    public static function philipsHue_ambianceRDC_turnDownBrightness()
    {
        // $brightness = self::philipsHue_ambianceRDC_brightnessState();
        $brightness = cmd::bystring(static::$luminosite_rdc)->execCmd();
        $brightness -= 25;
        $brightness = $brightness < 0 ? 0 : $brightness;
        return self::philipsHue_ambianceRDC_brightness($brightness);
    }




    ///////////

    public static function philipsHue_version()
    {
        $philipsHue = ePhilipsHue::getInstance();
        return "version ".$philipsHue->pilipsHue_version();
    }

    public static function philipsHue_debug()
    {
        echo "\n*** From philipsHue_debug() ***\n";

        $philipsHue = ePhilipsHue::getInstance();
        echo "\n".$philipsHue->getByName_id("Rubis", "scene");
        echo "\n".$philipsHue->getByName_rid("Ambiance RDC", "zone", "grouped_light");

        echo "\n";
    }

    public static function philipsHue_rubis()
    {
        $philipsHue = ePhilipsHue::getInstance();
        $scene_id = $philipsHue->getByName_id('Rubis', 'scene');
        return $philipsHue->recall_scene($scene_id);
    }

    public static function philipsHue_osaka()
    {
        $philipsHue = ePhilipsHue::getInstance();
        $scene_id = $philipsHue->getByName_id('Osaka', 'scene');
        return $philipsHue->recall_scene($scene_id);
    }

    public static function philipsHue_galaxy()
    {
        $philipsHue = ePhilipsHue::getInstance();
        $scene_id = $philipsHue->getByName_id('Galaxy', 'scene');
        return $philipsHue->recall_scene($scene_id);
    }

    public static function philipsHue_rosysparkle()
    {
        $philipsHue = ePhilipsHue::getInstance();
        $scene_id = $philipsHue->getByName_id('Rosy sparkle', 'scene');
        return $philipsHue->recall_scene($scene_id);
    }

    public static function philipsHue_miami()
    {
        $philipsHue = ePhilipsHue::getInstance();
        $scene_id = $philipsHue->getByName_id('Miami', 'scene');
        return $philipsHue->recall_scene($scene_id);
    }

    public static function philipsHue_concentration()
    {
        $philipsHue = ePhilipsHue::getInstance();
        $scene_id = $philipsHue->getByName_id('Versailles', 'scene');
        return $philipsHue->recall_scene($scene_id);
    }

    public static function philipsHue_versailles()
    {
        $philipsHue = ePhilipsHue::getInstance();
        $scene_id = $philipsHue->getByName_id('Versailles', 'scene');
        return $philipsHue->recall_scene($scene_id);
    }



    public static function philipsHue_entranceSensor_on()
    {
        $name = 'Hue motion sensor Entrée';
        $type = 'device';
        $rtype = 'motion';
        $settings = '{"enabled": true }';
        $philipsHue = ePhilipsHue::getInstance();
        $rid = $philipsHue->getByName_rid($name, $type, $rtype);
        return $philipsHue->putSetting($rtype, $rid, $settings);
    }

    public static function philipsHue_entranceSensor_off()
    {
        $name = 'Hue motion sensor Entrée';
        $type = 'device';
        $rtype = 'motion';
        $settings = '{"enabled": false }';
        $philipsHue = ePhilipsHue::getInstance();
        $rid = $philipsHue->getByName_rid($name, $type, $rtype);
        return $philipsHue->putSetting($rtype, $rid, $settings);
    }

    public static function philipsHue_entranceSensorTemperature_on()
    {
        $name = 'Hue motion sensor Entrée';
        $type = 'device';
        $rtype = 'temperature';
        $settings = '{"enabled": true }';
        $philipsHue = ePhilipsHue::getInstance();
        $rid = $philipsHue->getByName_rid($name, $type, $rtype);
        return $philipsHue->putSetting($rtype, $rid, $settings);
    }

    public static function philipsHue_entranceSensorTemperature()
    {
        $name = 'Hue motion sensor Entrée';
        $type = 'temperature';
        $setting = '{"temperature": {"temperature": "?"}}';
        $philipsHue = ePhilipsHue::getInstance();
        $temperature = $philipsHue->getSetting($type, $name, $setting);
        return floatval($temperature) ;

    }

    public static function philipsHue_hubConnectivity()
    {
        $name = 'Philips hue';
        $type = 'zigbee_connectivity';
        $setting = '{"status": "?" }';
        $philipsHue = ePhilipsHue::getInstance();
        $status = $philipsHue->getSetting($type, $name, $setting);
        return $status == "connected" ? true : false ;
    }

    public static function philipsHue_motionDetected()
    {
        $name = 'Hue motion sensor Entrée';
        $type = 'motion';
        $setting = '{"motion": {"motion": "?"}}';
        $philipsHue = ePhilipsHue::getInstance();
        $setting = $philipsHue->getSetting($type, $name, $setting);
        return boolval($setting);
    }



    public static function philipsHue_deleteCache()
    {
        $philipsHue = ePhilipsHue::getInstance();
        $philipsHue->philipsHue_deleteCache();
    }

    public static function philipsHue_startStream()
    {
        $ip = "192.168.1.172";
        $path = "/var/www/html/data/php/philipsHue";
        $cmd = <<< EOF
    python3 -c '
    import sys; sys.path.append("$path");
    import ePhilipsHueInterface; 
    ePhilipsHueInterface.start_stream("$ip");
    ' 
    EOF;
        echo "start ..\n";
        echo exec($cmd);
    }

    public static function philipsHue_stopStream()
    {
        $ip = "192.168.1.172";
        $path = "/var/www/html/data/php/philipsHue";
        $cmd = <<< EOF
    python3 -c '
    import sys; sys.path.append("$path");
    import ePhilipsHueInterface; 
    ePhilipsHueInterface.stop_stream("$ip");
    ' 
    EOF;
        echo exec($cmd);
    }

    public static function philipsHue_pidStream()
    {
        $ip = "192.168.1.172";
        $path = "/var/www/html/data/php/philipsHue";
        $cmd = <<< EOF
    python3 -c '
    import sys; sys.path.append("$path");
    import ePhilipsHueInterface; 
    ePhilipsHueInterface.pid_stream("$ip");
    ' 
    EOF;
        $res = intval(exec($cmd));
        return $res;
    }

    public static function philipsHue_luxFromLightLevel($light_level)
    {
        $lux = exp(($light_level - 1) / 10000 * log(10));
        return round($lux, 0);
    }

}
