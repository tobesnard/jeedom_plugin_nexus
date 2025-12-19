#!/usr/bin/env python3
# -*- coding: utf-8 -*-

import sys
import json
import os

# Détermination du chemin absolu du dossier racine du projet
BASE_DIR = os.path.abspath(os.path.join(os.path.dirname(__file__), ".."))

# Ajout du dossier racine pour permettre l'import de 'pylips'
if BASE_DIR not in sys.path:
    sys.path.append(BASE_DIR)

try:
    from pylips.pylips import Pylips
except ImportError:
    print(f"Erreur : Impossible de trouver le module pylips dans {BASE_DIR}")
    sys.exit(1)

# Chemin absolu vers le fichier settings.ini
SETTINGS_PATH = os.path.join(BASE_DIR, "pylips", "settings.ini")

# Initialisation
tv = Pylips(SETTINGS_PATH)


def hdmi_test():
    # Récupération de l'activité
    current_activity = tv.get("activities/current")

    # Sécurité : Si current_activity est déjà un dict, on ne désérialise pas
    if isinstance(current_activity, str):
        current_activity_dict = json.loads(current_activity)
    else:
        current_activity_dict = current_activity

    print(f"DEBUG - Structure reçue : {json.dumps(current_activity_dict, indent=2)}")

    # Extraction du nom du package / composant
    # Utilisation de .get() récursif pour éviter les KeyError
    component = current_activity_dict.get("component", {})
    package_name = component.get("packageName", "").lower()

    if "hdmi1" in package_name:
        print("Résultat : Le téléviseur est sur HDMI1")
    else:
        print(
            f"Résultat : Le téléviseur n'est pas sur HDMI1 (Actuellement sur : {package_name})"
        )


if __name__ == "__main__":
    hdmi_test()
