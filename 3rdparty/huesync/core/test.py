#!/usr/bin/env python3

# -*- coding: utf-8 -*-


import sys
import asyncio
from modules.hue_sync import HueSync


async def main(config_file="syncbox_config.json"):
    device = HueSync(config_file)
    await device.box.initialize()
    print(device.box.execution.sync_active)
    await device.box.close()


if __name__ == "__main__":
    config_path = sys.argv[1] if len(sys.argv) > 1 else "syncbox_config.json"
    asyncio.run(main(config_path))
