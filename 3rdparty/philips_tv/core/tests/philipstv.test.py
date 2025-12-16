#!/usr/bin/env python3
# -*- coding: utf-8 -*-

import sys
import json

sys.path.insert(0, "./../../pylips")  # chemin vers le dossier cloné

from pylips import Pylips

tv = Pylips("./../../pylips/settings.ini")


def hdmi_test():
    # Vérifier la source actuelle
    current_activity = tv.get("activities/current")
    # current_activity est actuellement une chaîne JSON
    current_activity_dict = json.loads(current_activity)

    print(current_activity_dict)  # vérifie le contenu

    # Vérification HDMI1
    package_name = (
        current_activity_dict.get("component", {}).get("packageName", "").lower()
    )
    if "hdmi1" in package_name:
        print("Le téléviseur est sur HDMI1")
    else:
        print("Le téléviseur n'est pas sur HDMI1 !!!!!!!")


if __name__ == "__main__":
    hdmi_test()
