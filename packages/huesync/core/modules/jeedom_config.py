import json
import os
import logging

logger = logging.getLogger("JeedomConfigLogger")
logger.setLevel(logging.DEBUG)

# Utilisation d'un fichier de log spécifique à l'utilisateur pour éviter les conflits de permission
log_file = f"/tmp/jeedomconfig_{os.getlogin() if hasattr(os, 'getlogin') else 'default'}.log"
file_handler = logging.FileHandler(log_file)
file_handler.setLevel(logging.DEBUG)

formatter = logging.Formatter("%(asctime)s - %(levelname)s - %(message)s")
file_handler.setFormatter(formatter)

if not logger.handlers:
    logger.addHandler(file_handler)


class JeedomConfig:
    JEEDOM_PLUGIN_ROOT = "/var/www/html/plugins/nexus/"

    def __init__(
        self,
        config_path=os.path.join(
            JEEDOM_PLUGIN_ROOT,
            "core/config",
            "jeedom.config.json",
        ),
    ):
        """
        Initialise la configuration Jeedom.

        :param config_path: Chemin vers un fichier JSON contenant 'jeeApi_endpoint'
        """
        self.jeeApi_endpoint = None
        self.notification_manager_api_key = None

        # logger.info(f"config_path : {config_path}")

        if config_path:
            self._load_from_file(config_path)

    def _load_from_file(self, path):
        """
        Charge la configuration depuis un fichier JSON.

        :param path: Chemin du fichier
        """
        if not os.path.exists(path):
            raise FileNotFoundError(f"Fichier de configuration introuvable : {path}")

        with open(path, "r") as f:
            data = json.load(f)
            self.jeeApi_endpoint = data.get("jeeApi_endpoint")
            self.notification_manager_api_key = data.get("notification_manager_api_key")

        if not self.jeeApi_endpoint:
            raise ValueError("Le fichier doit contenir 'jeeApi_endpoint'.")
