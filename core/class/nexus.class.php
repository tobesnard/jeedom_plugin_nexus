<?php

/* This file is part of Jeedom.
*
* Jeedom is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
* Jeedom is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with Jeedom. If not, see <http://www.gnu.org/licenses/>.
*/

/* * ***************************Includes********************************* */
require_once __DIR__  . '/../../../../core/php/core.inc.php';

class nexus extends eqLogic
{
    /*     * *************************Attributs****************************** */

    /*
    * Permet de définir les possibilités de personnalisation du widget (en cas d'utilisation de la fonction 'toHtml' par exemple)
    * Tableau multidimensionnel - exemple: array('custom' => true, 'custom::layout' => false)
    public static $_widgetPossibility = array();
    */

    /*
    * Permet de crypter/décrypter automatiquement des champs de configuration du plugin
    * Exemple : "param1" & "param2" seront cryptés mais pas "param3"
    public static $_encryptConfigKey = array('param1', 'param2');
    */

    /*     * ***********************Methode static*************************** */

    /**
     * Donne le statut des dépendances
     */
    public static function dependancy_info() {
        $return = array();
        $return['log'] = 'nexus_packages';
        $return['progress_file'] = jeedom::getTmpFolder('nexus') . '/dependance';
        
        if (class_exists('log')) {
            log::add('nexus', 'debug', '[Nexus] Appel de dependancy_info');
        }

        if (file_exists($return['progress_file'])) {
            $return['state'] = 'in_progress';
        } else {
            $vendor_dir = __DIR__ . '/../../vendor';
            $autoload = $vendor_dir . '/autoload.php';
            if (is_dir($vendor_dir) && file_exists($autoload)) {
                $return['state'] = 'ok';
            } else {
                if (class_exists('log')) {
                    log::add('nexus', 'debug', '[Nexus] Statut NOK car vendor/autoload.php absent ou dossier vendor absent');
                }
                $return['state'] = 'nok';
            }
        }
        return $return;
    }

    /**
     * Installe les dépendances via le mécanisme packages.json de Jeedom
     */
    public static function dependancy_install() {
    }

    /*
    * Fonction exécutée automatiquement toutes les minutes par Jeedom
    public static function cron() {}
    */

    /*
    * Fonction exécutée automatiquement toutes les 5 minutes par Jeedom
    public static function cron5() {}
    */

    /*
    * Fonction exécutée automatiquement toutes les 10 minutes par Jeedom
    public static function cron10() {}
    */

    /*
    * Fonction exécutée automatiquement toutes les 15 minutes par Jeedom
    public static function cron15() {}
    */

    /*
    * Fonction exécutée automatiquement toutes les 30 minutes par Jeedom
    public static function cron30() {}
    */

    /*
    * Fonction exécutée automatiquement toutes les heures par Jeedom
    public static function cronHourly() {}
    */

    /*
    * Fonction exécutée automatiquement tous les jours par Jeedom
    public static function cronDaily() {}
    */

    /*
    * Permet de déclencher une action avant modification d'une variable de configuration du plugin
    * Exemple avec la variable "param3"
    public static function preConfig_param3( $value ) {
      // do some checks or modify on $value
      return $value;
    }
    */

    /*
    * Permet de déclencher une action après modification d'une variable de configuration du plugin
    * Exemple avec la variable "param3"
    public static function postConfig_param3($value) {
      // no return value
    }
    */

    /**
     * Méthode appelée par Jeedom après la sauvegarde de la configuration du plugin.
     */
    public static function post_save() {
        self::postUpdateConfig();
    }

    public static function postConfig_gemini_api_key($_value) { self::postUpdateConfig(); }
    public static function postConfig_chatgpt_api_key($_value) { self::postUpdateConfig(); }
    public static function postConfig_copilot_api_key($_value) { self::postUpdateConfig(); }
    public static function postConfig_notification_manager_api_key($_value) { self::postUpdateConfig(); }
    public static function postConfig_hydrao_api_key($_value) { self::postUpdateConfig(); }
    public static function postConfig_hydrao_email($_value) { self::postUpdateConfig(); }
    public static function postConfig_hydrao_password($_value) { self::postUpdateConfig(); }
    public static function postConfig_hydrao_uuid($_value) { self::postUpdateConfig(); }
    public static function postConfig_philips_hue_token($_value) { self::postUpdateConfig(); }
    public static function postConfig_philips_hue_hub_ip($_value) { self::postUpdateConfig(); }
    public static function postConfig_philips_hue_client_key($_value) { self::postUpdateConfig(); }
    public static function postConfig_philips_tv_password($_value) { self::postUpdateConfig(); }
    public static function postConfig_philips_tv_secret($_value) { self::postUpdateConfig(); }
    public static function postConfig_philips_tv_ip($_value) { self::postUpdateConfig(); }
    public static function postConfig_philips_tv_port($_value) { self::postUpdateConfig(); }
    public static function postConfig_philips_tv_mac($_value) { self::postUpdateConfig(); }
    public static function postConfig_philips_tv_username($_value) { self::postUpdateConfig(); }
    public static function postConfig_jeedom_api_key($_value) { self::postUpdateConfig(); }

    public static function postUpdateConfig() {
        if (class_exists('log')) {
            log::add('nexus', 'debug', 'Lancement de postUpdateConfig');
        }
        $script = '/var/www/html/plugins/nexus/script/setup_env.php';
        if (file_exists($script)) {
            if (!class_exists('EnvGenerator')) {
                require_once $script;
            }
            $generator = new EnvGenerator();
            $generator->run();
        }
    }

    /*
     * Permet d'indiquer des éléments supplémentaires à remonter dans les informations de configuration
     * lors de la création semi-automatique d'un post sur le forum community
     public static function getConfigForCommunity() {
        // Cette function doit retourner des infos complémentataires sous la forme d'un
        // string contenant les infos formatées en HTML.
        return "les infos essentiel de mon plugin";
     }
     */

    /*     * *********************Méthodes d'instance************************* */

    // Fonction exécutée automatiquement avant la création de l'équipement
    public function preInsert()
    {
    }

    // Fonction exécutée automatiquement après la création de l'équipement
    public function postInsert()
    {
    }

    // Fonction exécutée automatiquement avant la mise à jour de l'équipement
    public function preUpdate()
    {
    }

    // Fonction exécutée automatiquement après la mise à jour de l'équipement
    public function postUpdate()
    {
    }

    // Fonction exécutée automatiquement avant la sauvegarde (création ou mise à jour) de l'équipement
    public function preSave()
    {
    }

    // Fonction exécutée automatiquement après la sauvegarde (création ou mise à jour) de l'équipement
    public function postSave()
    {
    }

    // Fonction exécutée automatiquement avant la suppression de l'équipement
    public function preRemove()
    {
    }

    // Fonction exécutée automatiquement après la suppression de l'équipement
    public function postRemove()
    {
    }

    /*
    * Permet de crypter/décrypter automatiquement des champs de configuration des équipements
    * Exemple avec le champ "Mot de passe" (password)
    public function decrypt() {
      $this->setConfiguration('password', utils::decrypt($this->getConfiguration('password')));
    }
    public function encrypt() {
      $this->setConfiguration('password', utils::encrypt($this->getConfiguration('password')));
    }
    */

    /*
    * Permet de modifier l'affichage du widget (également utilisable par les commandes)
    public function toHtml($_version = 'dashboard') {}
    */

    /*     * **********************Getteur Setteur*************************** */
}

class nexusCmd extends cmd
{
    /*     * *************************Attributs****************************** */

    /*
    public static $_widgetPossibility = array();
    */

    /*     * ***********************Methode static*************************** */


    /*     * *********************Methode d'instance************************* */

    /*
    * Permet d'empêcher la suppression des commandes même si elles ne sont pas dans la nouvelle configuration de l'équipement envoyé en JS
    public function dontRemoveCmd() {
      return true;
    }
    */

    // Exécution d'une commande
    public function execute($_options = array())
    {
    }

    /*     * **********************Getteur Setteur*************************** */
}
