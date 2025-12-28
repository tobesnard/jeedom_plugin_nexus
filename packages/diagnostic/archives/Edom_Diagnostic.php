<?php

/**
 * Diagnostique Aqara
 * Permet de dire si le module aqara remonte correctement ses valeures.
 * Toutes les 12 heures, le script compare la tempèrature au moment (t) avec celle du moment (t-12h)
 * si la différence = 0, alors le module ne fonctionne pas correctement.
 *
 * Pour de meilleurs résultats, prevoir une exécution à l'heure la plus froide de la journée (5h00)
 * Puis une seconde exécution à l'heure la plus chaude de la journée (17h00)
 *
 * Méthode :
 * Construction de l'arbre des données.
 * Pour chaque module de "Zigbee-Equipment" ayant une commande "..temperature.."
 * On récupère :
 *   CommandId = xx,
 *   EquipmentName = 'name',
 *   TempEvolve = false
 *
 * Comparaison des tempèratures à (t) et (t-12h)
 * Si la différence est autre que 0, TempEvolve = true
 *
 * Information
 * Si des modules on une tempèrature qui n'évolue pas
 * On lance une info (message jeedom + flag)
 *
 *
 *  */

require_once '/var/www/html/core/php/core.inc.php';  // pour messag::, attention chemin diferent entre un usage scenario et vscode

trait Edom_Diagnostic
{
    private static $debug = false;
    private static $_zigbeeEquipments_id = 23; // Identifiant de l'objet "Zigbee Equipments" dans Jeedom

    public static function zigbeeModules_diagnostic_depreciate()
    {
        $results = static::getDataTemps();
        return static::throwMessageIf($results);
    }

    public static function zigbeeModules_diagnostic()
    {

        // On récupère la liste des modules concernés
        $sql = <<< EOD
            SELECT cmd.id , eqLogic.name 
            FROM cmd 
            INNER JOIN eqLogic ON cmd.eqLogic_id=eqLogic.id 
            INNER JOIN object ON eqLogic.object_id=object.id 
            WHERE object.id=:id AND cmd.name like '%temperature% AND eqLogic.isEnable=1'
        EOD;
        $ids = DB::Prepare($sql, array('id' => static::$_zigbeeEquipments_id), DB::FETCH_TYPE_ALL);

        // Création d'un tableau de résultats
        $zigbeeModules_nok = array();
        foreach ($ids as $o) {
            $isOk = static::zigbeeModules_isOk($o["id"]);
            if (!$isOk) {
                $module = array("id" => $o["id"], "name" => $o["name"]);
                array_push($zigbeeModules_nok, $module);
            }
        }

        // Genére un message si nécessaire
        static::throwMessageIf($zigbeeModules_nok);
    }


    public static function zigbeeModules_isOk($_id)
    {

        $delta = "-1 days";
        $lastData = 0;
        $average = 0;

        // Récupère les informations sur la période determinée $delta-> $now
        $deb = date("Y-m-d H:i:s", strtotime($delta));
        $fin = date("Y-m-d H:i:s", strtotime("now"));
        $dataset = history::all($_id, $deb, $fin);

        // Création d'un tableau de résultats
        $values = array();
        foreach ($dataset as $d) {
            array_push($values, (float) $d->getValue());
        }

        $lastData = end($values);
        $average = array_sum($values) / count($values);

        // Pour Debug
        echo "Id : $_id, lastData : $lastData, Average : $average\n" ;

        echo self::$debug ? "Id : $_id, lastData : $lastData, Average : $average" : "";

        if ($lastData == $average) {
            return false;
        } else {
            return true;
        }

    }






    public static function googlePresence_diagnostic()
    {
        $id = 2978;   // Identifiant de la commande info Etat, équipement Présence Google
        $delta = "-1 days";

        // Récupère les informations sur la période determinée $delta-> $now
        $deb = date("Y-m-d H:i:s", strtotime($delta));
        $fin = date("Y-m-d H:i:s", strtotime("now"));
        $dataset = history::all($id, $deb, $fin);

        // Création d'un tableau de résultats
        $values = array();
        foreach ($dataset as $d) {
            array_push($values, (int) $d->getValue());
        }

        // Calcul de la moyenne et determine si la presence google fonctionne ou non
        $diagnostic = true;
        if (count($values) > 0) {
            $average = array_sum($values) / count($values);
            if ($average == 0 || $average == 1) {
                $diagnostic = false;
            }
        }

        return $diagnostic;



    }

    private static function getDataTemps()
    {
        // Requête
        $sql = <<< EOD
            SELECT cmd.id , eqLogic.name 
            FROM cmd 
            INNER JOIN eqLogic ON cmd.eqLogic_id=eqLogic.id 
            INNER JOIN object ON eqLogic.object_id=object.id 
            WHERE object.id=:id AND cmd.name like '%temperature%'
        EOD;

        $results = DB::Prepare($sql, array('id' => static::$_zigbeeEquipments_id), DB::FETCH_TYPE_ALL);

        // Si pour le module la tempéraure évolue, alors il est ejecté du resultat
        foreach ($results as $key => $row) {
            $tempEvolution = static::getTempEvolution($row["id"]);
            if ($tempEvolution == true) {
                unset($results[$key]);
            }
        }

        return $results;
    }

    private static function getTempEvolution($_id = null, $_delta = "-12 hour")
    {

        $deb = date("Y-m-d H:i:s", strtotime($_delta));
        $fin = date("Y-m-d H:i:s", strtotime("now"));
        $dataset = history::all($_id, $deb, $fin);

        $firstValue = $dataset[0]   == null ? 0 : $dataset[0]->getValue();
        $lastValue  = end($dataset) == null ? 0 : end($dataset)->getValue();

        $firstValue = number_format($firstValue, 2);
        $lastValue  = number_format($lastValue, 2);

        echo self::$debug ? "debug : ".$_id."\t".$firstValue."\t".$lastValue."\n" : "";
        return ($lastValue - $firstValue) == 0 ? false : true;
    }

    private static function displayDataTemps($results)
    {
        // Affichage sur la sortie standard
        $str = "";
        $str .= "*************************************\n";
        foreach ($results as $key => $row) {
            $str .= $row["id"]."\t".$row["name"]."\n";
        }
        $str .= "*************************************\n";
        echo $str;
    }

    private static function throwMessageIf($_results)
    {
        // Construction de l'information et envoi au centre de messagerie jeedom
        if (count($_results) >= 1) {
            $message = "Le ou les modules suivant ne répondent plus :";
            foreach ($_results as $r) {
                $message .= " [".$r["name"]."],";
            }
            $message = rtrim($message, "., ");
            // echo self::$debug ? $message."\n" : "";
            echo $message."\n";
            message::add("Diagnostique Zigbee", $message);
        }
    }

    public static function diagnostic_debug()
    {
        self::$debug = true;
        $results = static::getDataTemps();
        static::displayDataTemps($results);
        static::throwMessageIf($results);
    }

} // End Trait

