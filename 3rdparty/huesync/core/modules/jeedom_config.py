import json
import os


class JeedomConfig:
    def __init__(self, config_path="/var/www/html/data/config/jeedom.config.json"):
        """
        Initialise la configuration Jeedom.

        :param config_path: Chemin vers un fichier JSON contenant 'jeeApi_endpoint'
        """
        self.jeeApi_endpoint = None
        self.notification_manager_api_key = None
        self.api_key = os.getenv("JEEDOM_API_KEY")

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
