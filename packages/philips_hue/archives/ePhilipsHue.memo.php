<?php

// CommandeHUE_Memo.php




$_hue_hub_ip = "192.168.1.172";
$_hue_jeton = "Q3t3U-TWmmQLhJJGd-pIe6oVwUXpzIWStQoaplgo";



// Récupère l'identifiant du bridge
// API philips HUE V2
$_url = "https://$_hue_hub_ip/clip/v2/resource/bridge";
$retour = shell_exec("curl --insecure -s -X GET $_url -H 'hue-application-key: $_hue_jeton' ");
$json = json_decode($retour);
$data = $json->{'data'}[0]->{"bridge_id"};
//echo $data;

// Récupère l'état hue go
// API philips HUE V2
$_rid_HueGo = '0ed08dd0-9bf6-4950-a36c-184fc1b389ff';  // rtype = light
$_url = "https://$_hue_hub_ip/clip/v2/resource/light/$_rid_HueGo";
$retour = shell_exec("curl --insecure -s -X GET $_url -H 'hue-application-key: $_hue_jeton' ");
$json = json_decode($retour);
$json = $json->{'data'}[0]->{'on'};
//echo (boolean) $json->{'on'}


// Augmente la luminosité de Hue Go à 100%
// API philips HUE V2
$_rid_HueGo = '0ed08dd0-9bf6-4950-a36c-184fc1b389ff';  // rtype = light
$_url = "https://$_hue_hub_ip/clip/v2/resource/light/$_rid_HueGo";
$curl = <<< EOD
	curl --insecure -s \
		-X PUT $_url \
		-H 'hue-application-key: $_hue_jeton' \
		-H 'Content-Type: application/json' \
		--data-raw '{"dimming": {"brightness": 100}}'
	EOD;
//echo shell_exec($curl);



//  Allume puis Eteins la philips HueGo
// API philips HUE V2
$_rid_HueGo = '0ed08dd0-9bf6-4950-a36c-184fc1b389ff';  // rtype = light
$_url = "https://$_hue_hub_ip/clip/v2/resource/light/$_rid_HueGo";
$curl = <<< EOD
	curl --insecure -s \
		-X PUT $_url \
		-H 'hue-application-key: $_hue_jeton' \
		-H 'Content-Type: application/json'
	EOD;
$data_row_on = " --data-raw '{\"on\": {\"on\": true}}' ";
$data_row_off = " --data-raw '{\"on\": {\"on\": false}}' ";

//echo shell_exec( $curl.$data_row_on );
//sleep(1);
//echo shell_exec( $curl.$data_row_off );


// Activation d'une scène dynamique : Rubis
// API philips HUE V2
$_scene_v2_id = '2e778bc0-7870-4556-b13c-1844cc11f349';  // My Rubis
$_url = "https://$_hue_hub_ip/clip/v2/resource/scene/$_scene_v2_id";
$curl = <<< EOD
	curl --insecure -s \
		-X PUT $_url \
		-H 'hue-application-key: $_hue_jeton' \
		-H 'Content-Type: application/json' \
		--data-raw '{"recall": {"action": "dynamic_palette"}}'
	EOD;
//echo shell_exec($curl);



// Augmente la luminosité du group Ambiance RDC au maximum
// API Philips HUE V1
$_group_id_v1 = '/groups/4';  // Group Ambiance RDC
$_url = $_hue_hub_ip."/api/".$_hue_jeton.$_group_id_v1."/action";
$curl = <<< EOD
		curl -s -X PUT -d '{ "bri": 254 }' $_url 
	EOD;
//echo shell_exec($curl);


// Récupère l'état luminosité du group Ambiance RDC
// API philips HUE V1
$_group_id_v1 = '/groups/4';  // Group Ambiance RDC
$_url = $_hue_hub_ip."/api/".$_hue_jeton.$_group_id_v1;
$curl = <<< EOD
		curl -s -X GET  $_url 
	EOD;
$json = json_decode(shell_exec($curl));
echo $json->{'action'}->{'bri'};

