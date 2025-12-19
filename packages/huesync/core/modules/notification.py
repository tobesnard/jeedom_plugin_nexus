import requests
from modules.jeedom_config import JeedomConfig


class Notification:
    def __init__(self):
        """
        Initialise la classe Notification.
        """
        jeedom_config = JeedomConfig()
        self.jeeApi_endpoint = jeedom_config.jeeApi_endpoint
        self.api_key = jeedom_config.notification_manager_api_key

    def send(self, message):
        """
        Envoie un message via la commande Jeedom.

        :param message: Le message à envoyer
        """
        endpoint = self.jeeApi_endpoint
        params = {
            "plugin": "notificationmanager",
            "apikey": self.api_key,
            "type": "cmd",
            "id": 9422,  # 9422 = [Télécommunication][Notification Manager][Assistant Vocal]
            "message": message,
        }

        try:
            response = requests.get(endpoint, params=params)
            response.raise_for_status()
        except requests.RequestException as e:
            print("❌ Erreur lors de l'envoi de la notification :", e)
