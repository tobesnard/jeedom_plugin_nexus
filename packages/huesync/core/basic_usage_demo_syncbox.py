#!/usr/bin/env python3

import asyncio
import json
from aiohuesyncbox import HueSyncBox


def get_config(filename):
    with open(filename, "r") as f:
        data = json.load(f)
        return data


def get_box(config):
    # host and id can be obtained through mDNS/zeroconf discovery
    # (or for testing look them up in the official Hue app)
    box = HueSyncBox(config["host"], config["id"], config["access_token"])
    return box


async def initialize_box(box):
    # Call initialize before interacting with the box
    await box.initialize()
    print("device name : ", box.device.name)
    print("sync_active : ", box.execution.sync_active)
    print("mode : ", box.execution.mode)


async def set_hdmi1_video_sync_state(box):
    # Turn the box on, start syncing with video mode on input 4
    await box.execution.set_state(sync_active=True, mode="video", hdmi_source="input1")


async def update_box_status(box):
    # Call update() to update with latest status from the box
    await box.execution.update()
    print("sync_active : ", box.execution.sync_active)
    print("mode : ", box.execution.mode)


async def main(config_filename="syncbox_config.json"):
    config = get_config(config_filename)
    box = get_box(config)
    await initialize_box(box)
    # await set_hdmi1_video_sync_state(box)
    # await update_box_status(box)


# Lance l'exécution de la fonction asynchrone
asyncio.run(main())
