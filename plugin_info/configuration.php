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

require_once dirname(__FILE__) . '/../../../core/php/core.inc.php';
include_file('core', 'authentification', 'php');
if (!isConnect()) {
    include_file('desktop', '404', 'php');
    die();
}

// Lecture dynamique du .env.example pour s'assurer que tous les champs sont présents
$exampleFile = dirname(__FILE__) . '/../.env.example';
$keys = [];
if (file_exists($exampleFile)) {
    $lines = file($exampleFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if (strpos($line, '#') === 0 || strpos($line, '=') === false) continue;
        $parts = explode('=', $line);
        $key = trim($parts[0]);
        if ($key) $keys[] = $key;
    }
}

/**
 * Fonction helper pour générer un groupe de formulaire
 */
function getPluginConfigRow($key, $label, $type = 'text') {
    $l1key = strtolower($key);
    $value = config::byKey($l1key, 'nexus', '');
    if (is_array($value)) {
        $value = $value[0] ?? '';
    }
    $html = '<div class="form-group">';
    $html .= '<label class="col-md-4 control-label">{{' . $label . '}}</label>';
    $html .= '<div class="col-md-4">';
    $html .= '<input type="' . $type . '" class="configKey form-control" data-l1key="' . $l1key . '" placeholder="' . $key . '" value="' . htmlspecialchars((string)$value) . '"/>';
    $html .= '</div>';
    $html .= '</div>';
    return $html;
}

?>
<form class="form-horizontal">
    <fieldset>
        <legend><i class="fas fa-robot"></i> {{Intelligence Artificielle}}</legend>
        <?php
        echo getPluginConfigRow('GEMINI_API_KEY', 'Google Gemini API Key');
        echo getPluginConfigRow('CHATGPT_API_KEY', 'OpenAI ChatGPT API Key');
        echo getPluginConfigRow('COPILOT_API_KEY', 'GitHub Copilot Token');
        ?>
    </fieldset>

    <fieldset>
        <legend><i class="fas fa-lightbulb"></i> {{Philips Hue & TV}}</legend>
        <?php
        echo getPluginConfigRow('PHILIPS_HUE_HUB_IP', 'Philips Hue Hub IP');
        echo getPluginConfigRow('PHILIPS_HUE_TOKEN', 'Philips Hue Token', 'password');
        echo getPluginConfigRow('PHILIPS_HUE_CLIENT_KEY', 'Philips Hue Client Key', 'password');
        echo '<hr/>';
        echo getPluginConfigRow('PHILIPS_TV_IP', 'Philips TV IP');
        echo getPluginConfigRow('PHILIPS_TV_USERNAME', 'Philips TV Username');
        echo getPluginConfigRow('PHILIPS_TV_PASSWORD', 'Philips TV Password', 'password');
        echo getPluginConfigRow('PHILIPS_TV_SECRET', 'Philips TV Secret', 'password');
        echo getPluginConfigRow('PHILIPS_TV_PORT', 'Philips TV Port');
        echo getPluginConfigRow('PHILIPS_TV_MAC', 'Philips TV MAC');
        ?>
    </fieldset>

    <fieldset>
        <legend><i class="fas fa-tint"></i> {{Hydrao}}</legend>
        <?php
        echo getPluginConfigRow('HYDRAO_EMAIL', 'Hydrao Email');
        echo getPluginConfigRow('HYDRAO_PASSWORD', 'Hydrao Password', 'password');
        echo getPluginConfigRow('HYDRAO_API_KEY', 'Hydrao API Key', 'password');
        echo getPluginConfigRow('HYDRAO_UUID', 'Hydrao UUID');
        ?>
    </fieldset>

    <fieldset>
        <legend><i class="fas fa-key"></i> {{Sécurité & Système}}</legend>
        <?php
        echo getPluginConfigRow('JEEDOM_API_KEY', 'Jeedom API Key (Nexus)', 'password');
        echo getPluginConfigRow('NOTIFICATION_MANAGER_API_KEY', 'Notification Manager API Key', 'password');
        ?>
    </fieldset>

    <div class="alert alert-info" style="margin-top:20px;">
        <i class="fas fa-info-circle"></i> {{La modification de ces valeurs mettra automatiquement à jour votre fichier .env local.}}
    </div>
</form>
<?php
// Vérification que toutes les clés du template sont affichées
if (isset($keys)) {
    $displayed_keys = [
        'gemini_api_key', 'chatgpt_api_key', 'copilot_api_key',
        'philips_hue_hub_ip', 'philips_hue_token', 'philips_hue_client_key',
        'philips_tv_ip', 'philips_tv_username', 'philips_tv_password', 'philips_tv_secret', 'philips_tv_port', 'philips_tv_mac',
        'hydrao_email', 'hydrao_password', 'hydrao_api_key', 'hydrao_uuid',
        'jeedom_api_key', 'notification_manager_api_key'
    ];
    $missing = [];
    foreach ($keys as $k) {
        if (!in_array(strtolower($k), $displayed_keys)) $missing[] = $k;
    }
    if (count($missing) > 0) {
        echo '<div class="alert alert-warning"><i class="fas fa-exclamation-triangle"></i> {{Clés manquantes dans l\'interface :}} ' . implode(', ', $missing) . '</div>';
    }
}
?>
