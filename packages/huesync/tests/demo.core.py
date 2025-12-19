#!/usr/bin/env python3

# -*- coding: utf-8 -*-


import sys
import os
import asyncio

# Ajoute le dossier parent de 'tests' au chemin de recherche
sys.path.append(os.path.abspath(os.path.join(os.path.dirname(__file__), "..")))

from core.modules.hue_sync import HueSync

# Calcule le chemin absolu du dossier contenant demo.core.py
BASE_DIR = os.path.dirname(os.path.abspath(__file__))

# Reconstruit le chemin vers la config de manière fiable
CONFIG_FILE = os.path.join(BASE_DIR, "..", "core", "config", "syncbox_config.json")


async def main(config_file=CONFIG_FILE):
    device = HueSync(config_file)
    await device.box.initialize()
    print(device.box.execution.sync_active)
    await device.box.close()


if __name__ == "__main__":
    config_path = sys.argv[1] if len(sys.argv) > 1 else CONFIG_FILE
    asyncio.run(main(config_path))
