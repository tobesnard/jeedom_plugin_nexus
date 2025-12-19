# Hue Sync Box Api Documentation

Environnement virtuel python installé `python3 -m venv huesync`
Activation de l'environnement virtuel `source huesync/bin/activate`
Installation du module [aiohuesyncbox](https://github.com/mvdwetering/aiohuesyncbox) `pip install aiohuesyncbox`

L'exécution des script nécessite la version 3.10 ou supérieur de python.

Obtenir l'identifiant de la Hue Sync Box depuis window `dns-sd -B _huesync._tcp local.`
Obtenir l'adresse IP de la Hue Sync Box depuis windows `dns-sd -G v4 <ID>.local`

Script pour obtenir les infos de la box via zeroconf `./src/search_syncbox_zeroconf.py`
Script pour s'enregistrer sur la box et obtenir le token `./src/register_syncbox.py`
Script pour une démo d'utilisation de l'api `./src/basic_usage_demo_syncbox.py`

Écriture des script python :
    - `syncbox_mode_cinema.py` : pour passer la box en mode cinéma
    - `syncbox_mode_console.py` : pour passer la box en mode console
    - `syncbox_powersave.py` : pour éteindre la box

Écriture d'un lanceur en PHP `script_launcher.php`
le plugin script de Jeedom n'exécute n'exécute pas la bonne version de Python
malgré un shebang correct.
