import json
import asyncio
from aiohuesyncbox import HueSyncBox
from aiohuesyncbox.helpers import generate_attribute_string


class HueSync:
    def __init__(self, config_path):
        """Initialise l'objet HueSync avec la configuration et la box"""
        self.config_path = config_path
        self.config = self._load_config()
        self.box = self._create_box()

    def __str__(self):
        """Retourne une représentation textuelle de l'objet HueSync."""
        hue_info = generate_attribute_string(
            self.box.hue,
            [
                "bridge_unique_id",
                "bridge_ip_address",
                "connection_state",
            ],
        )
        execution_info = generate_attribute_string(
            self.box.execution,
            [
                "sync_active",
                "hdmi_active",
                "mode",
                "last_sync_mode",
                "hdmi_source",
                "hue_target",
                "brightness",
                # "video",
                # "game",
                # "music",
            ],
        )
        return f"{hue_info}\n{execution_info}"

    def _load_config(self):
        """Charge la configuration depuis le fichier JSON"""
        with open(self.config_path, "r") as f:
            return json.load(f)

    def _create_box(self):
        """Crée une instance de HueSyncBox à partir de la configuration"""
        return HueSyncBox(
            self.config["host"], self.config["id"], self.config["access_token"]
        )

    async def _initialize(self):
        """Initialise la Hue Sync Box (connexion, authentification, etc.)"""
        try:
            await self.box.close()
            await asyncio.sleep(1)
        except Exception as e:
            print(f"Erreur lors de l'initialisation de la box : {e}")
        finally:
            await self.box.initialize()

    async def apply_state(self, *args, **kwargs):
        """Applique un nouvel état et met à jour l'état local"""
        try:
            await self._initialize()
            await self.box.execution.set_state(*args, **kwargs)
            await self.box.execution.update()
        except Exception as e:
            print(f"Erreur lors de l'application de l'état : {e}")
        finally:
            await self.box.close()
