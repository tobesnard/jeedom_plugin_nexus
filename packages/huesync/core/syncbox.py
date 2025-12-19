#!/usr/bin/env python3

# -*- coding: utf-8 -*-

# Active ou désactive la synchronisation sur la Hue Sync Box

import asyncio
import argparse
from modules.hue_sync import HueSync
from modules.notification import Notification


async def main(args):
    salon_et_petit_salon = "b906beb6-962c-44b9-a646-07900b1864a2"

    device = HueSync(args.config)
    await device.box.initialize()

    try:
        if args.type == "action":
            if device.box.hue.connection_state == "busy":
                # busy : TODO Arrêter la synchronisation en cours ( en particulier celle avec le pc )"
                notification = Notification()
                notification.send(
                    "La Hue Sync Box est occupée. Veuillez arrêter la synchronisation en cours."
                )
                await device.box.close()
                print(device.box.hue.connection_state)
                return

            if args.command == "reboot":
                await device.box.execution.set_state(sync_active=False)
                await asyncio.sleep(2)
                await device.box.execution.set_state(sync_active=True)

            if args.command == "power_on":
                await device.box.execution.set_state(
                    mode="passthrough",
                )

            if args.command == "power_off":
                await device.box.execution.set_state(
                    mode="powersave",
                )

            if args.command == "mode_console":
                await device.box.execution.set_state(
                    sync_active=True,
                    mode="game",
                    hdmi_source="input2",
                    intensity="high",
                    brightness=(70 * 2),
                    hue_target=salon_et_petit_salon,
                )

            if args.command == "mode_cinema":
                await device.box.execution.set_state(
                    sync_active=True,
                    mode="video",
                    hdmi_source="input1",
                    intensity="high",
                    brightness=(70 * 2),
                    hue_target=salon_et_petit_salon,
                )

            if args.command == "mode_music":
                await device.box.execution.set_state(
                    sync_active=True,
                    mode="music",
                    hdmi_source="input1",
                    intensity="high",
                    brightness=(100 * 2),
                    hue_target=salon_et_petit_salon,
                )

            if args.command in ["hdmi1", "hdmi2", "hdmi3", "hdmi4"]:
                port_number = [port for port in args.command if port.isdigit()]
                await device.box.execution.set_state(
                    hdmi_source=f"input{port_number[0]}",
                )

            if args.command == "sync_on":
                await device.box.execution.set_state(
                    sync_active=True,
                )
            if args.command == "sync_off":
                await device.box.execution.set_state(
                    sync_active=False,
                )
        if args.type == "info":
            if args.command == "state":
                print(device.box.hue.connection_state)

    except Exception as e:
        print(f"Erreur lors de l'exécution de la commande : {e}")

    finally:
        # print(device) # attention seulement pour le debug sinon les commandes Info ne fonctionnent pas correctement
        await device.box.close()


if __name__ == "__main__":
    parser = argparse.ArgumentParser(
        description="Exécute des commandes auprès de la Hue Sync Box"
    )
    parser.add_argument(
        "--config",
        type=str,
        default="syncbox_config.json",
        help="Chemin vers le fichier de configuration",
    )
    parser.add_argument(
        "--command",
        type=str,
        help="Nom de la commande à éxecuter (e.g., sync_active_onoff)",
    )
    parser.add_argument(
        "--type", type=str, choices=["action", "info"], help="type de la commande"
    )

    args = parser.parse_args()

    asyncio.run(main(args))
