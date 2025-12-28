<?php

class ePhilipsHue
{
    private $version = "0.0.1";
    private static $datetime = null;
    private static $_instance = null;

    private $hub_ip = "192.168.1.172";
    private $token = "***REMOVED***";
    private $resources = null;


    private function __construct()
    {
        $this->version = "0.0.2";
        static::$datetime = new DateTime();

        if (is_null($this->resources)) {
            $cacheKey = "resources";
            $cacheName = "ePhilipsHue.resources";
            $cache = cache::byKey($cacheName)->getValue() ;

            if ($cache === "") {
                $ip = $this->hub_ip;
                $token = $this->token;
                $url = "https://$ip/clip/v2/resource";
                $curl = <<< EOD
                    curl --insecure -s \
                    -X GET $url \
                    -H 'hue-application-key: $token' \
                    -H 'Content-Type: application/json'
                EOD;
                $cache = json_decode(shell_exec($curl));
                $cache = $cache->data;
                cache::set($cacheName, $cache);
            }

            $this->resources = $cache;
        }
    }

    public function pilipsHue_version()
    {
        return $this->version;
    }

    public function pilipsHue_cache($_name)
    {
        return cache::byKey($_name)->getValue();
    }

    public function philipsHue_deleteCache()
    {
        cache::delete("ePhilipsHue.resources");
        cache::delete("ePhilipsHue.id");
        cache::delete("ePhilipsHue.rid");
    }

    public static function getInstance()
    {
        $now = new DateTime();
        $now = $now->format('Ymd');
        if (is_null(self::$_instance) or self::$datetime->format('Ymd') !== $now) {
            self::$_instance = new ePhilipsHue();
        }
        return self::$_instance;
    }

    public function getByName_id($_name, $_type)
    {
        // cache::delete("ePhilipsHue.id");
        $cacheKey = "$_name.$_type";
        $cacheName = "ePhilipsHue.id";
        $cache = cache::byKey($cacheName)->getValue() ;

        if ($cache === "") {
            $cache = json_decode('{}');
            cache::set($cacheName, $cache);
        }

        if (isset($cache->{ $cacheKey })) {
            return $cache->{ $cacheKey } ;
        } else {
            foreach ($this->resources as $r) {
                if ($r->type === $_type and $r->metadata->name === $_name) {
                    $cache->{ $cacheKey } = $r->id;
                    cache::set("ePhilipsHue.id", $cache);
                    return $r->id;
                }
            }
        }
    }

    public function getByName_rid($_name, $_type, $_rtype)
    {
        // cache::delete("ePhilipsHue.rid");
        $cacheKey = "$_name.$_type.$_rtype";
        $cacheName = "ePhilipsHue.rid";
        $cache = cache::byKey($cacheName)->getValue() ;

        if ($cache === "") {
            $cache = json_decode('{}');
            cache::set($cacheName, $cache);
        }

        if (isset($cache->{ $cacheKey })) {
            return $cache->{ $cacheKey } ;
        } else {
            foreach ($this->resources as $r) {
                if ($r->type === $_type and $r->metadata->name === $_name) {
                    foreach ($r->services as $s) {
                        if ($s->rtype === $_rtype) {
                            $cache->{ $cacheKey } = $s->rid;
                            cache::set("ePhilipsHue.id", $cache);

                            return $s->rid;
                        }
                    }
                }
            }
        }
    }

    public function recall_scene($_id, $_static = false)
    {
        $ip = $this->hub_ip;
        $token = $this->token;
        $action = $_static !== false ? 'active' : 'dynamic_palette';
        $url = "https://$ip/clip/v2/resource/scene/$_id";
        $curl = <<< EOD
            curl --insecure -s \
            -X PUT $url \
            -H 'hue-application-key: $token' \
            -H 'Content-Type: application/json' \
            --data-raw '{"recall": {"action": "$action"}}'
        EOD;

        return shell_exec($curl);
    }

    public function getSetting($_type, $_name, $_settings)
    {

        $settingJson = json_decode($_settings);

        $type = "device";	// Valeure par défaut, utilisé entre autre par light, motion, etc
        if ($_type == "grouped_light") {
            $type = "zone";
        }

        $rtype = $_type;
        $rid = $this->getByName_rid($_name, $type, $rtype) ;

        $ip = $this->hub_ip;
        $token = $this->token;
        $url = "https://$ip/clip/v2/resource/$_type/$rid";
        $curl = <<< EOD
            curl --insecure -s \
            -X GET $url \
            -H 'hue-application-key: $token' \
            -H 'Content-Type: application/json' 
        EOD;
        $resource = json_decode(shell_exec($curl));
        $resource = $resource->data[0];

        $value = $this->getSettingsInJson($resource, $settingJson);
        return $value;
    }

    public function putSetting($_type, $_id, $_settings)
    {
        $ip = $this->hub_ip;
        $token = $this->token;
        $url = "https://$ip/clip/v2/resource/$_type/$_id";
        $curl = <<< EOD
            curl --insecure -s \
            -X PUT $url \
            -H 'hue-application-key: $token' \
            -H 'Content-Type: application/json' \
            -d '$_settings'
        EOD;
        return shell_exec($curl);
    }




    // *** Private Method

    private function getSettingsInJson($_data_json, $_settings_json)
    {
        /** Recherche du json dans du json */
        foreach ($_data_json as $key_data => $value_data) {
            foreach ($_settings_json as $key_settings => $value_settings) {
                if ($key_data == $key_settings) {
                    if (is_object($value_data) and is_object($value_settings)) {
                        return $this->getSettingsInJson($value_data, $value_settings);
                    } else {
                        if ($value_settings == "?") {
                            return $value_data;
                        }
                    }
                }
            }
        }
    }

}
