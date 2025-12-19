#!/usr/bin/env python3

# S'enregistre auprès de la Hue Sync Box et stocke les informations d'enregistrement dans un fichier JSON.
import json
import asyncio
from aiohuesyncbox import HueSyncBox, InvalidState


def get_config(filename):
    with open(filename, "r") as f:
        data = json.load(f)
        return data


def set_config(filename, config):
    with open(filename, "w") as f:
        json.dump(config, f, indent=4)


async def register(config):
    box = HueSyncBox(config["host"], config["id"])

    print("Press the button on the box for a few seconds until the light blinks green.")

    registration_info = None
    while not registration_info:
        try:
            registration_info = await box.register(
                config["application_name"], config["device_name"]
            )
        except InvalidState:
            # Indicates the button was not pressed
            pass
        await asyncio.sleep(1)

    print("Registration successful:")
    print(registration_info)

    config["registration_id"] = registration_info["registration_id"]
    config["access_token"] = registration_info["access_token"]
    return config


async def unregister(config):
    # tn register the app from the Hue Sync Box
    box = HueSyncBox(config["host"], config["id"])
    await box.unregister(config["registration_id"])


async def main(config_filename="syncbox_config.json"):
    config = get_config(config_filename)
    config = await register(config)
    set_config(config_filename, config)


# Lance l'exécution de la fonction asynchrone
asyncio.run(main())
