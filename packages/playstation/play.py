#!/usr/bin/env python3
"""
Script pour réveiller une PS5 via le protocole Remote Play
"""
import socket
import json
import os
from pathlib import Path

CONFIG_FILE = Path.home() / '.ps5_credentials.json'

def load_credentials():
    """Charge les credentials sauvegardés"""
    if CONFIG_FILE.exists():
        with open(CONFIG_FILE, 'r') as f:
            return json.load(f)
    return None

def save_credentials(ip, registration_data):
    """Sauvegarde les credentials"""
    with open(CONFIG_FILE, 'w') as f:
        json.dump({'ip': ip, 'data': registration_data}, f)
    print(f"Credentials sauvegardés dans {CONFIG_FILE}")

def send_wakeup_packet(ip, port=9295):
    """Envoie un paquet de réveil à la PS5"""
    try:
        sock = socket.socket(socket.AF_INET, socket.SOCK_STREAM)
        sock.settimeout(5)
        sock.connect((ip, port))
        
        # Requête HTTP simple pour tester
        request = b"GET / HTTP/1.1\r\nHost: " + ip.encode() + b"\r\n\r\n"
        sock.send(request)
        
        response = sock.recv(1024).decode('utf-8', errors='ignore')
        print(f"Réponse: {response}")
        
        sock.close()
        return True
    except Exception as e:
        print(f"Erreur lors de l'envoi: {e}")
        return False

def main():
    PS5_IP = '192.168.1.226'
    
    print("=== Réveil PS5 via Remote Play ===\n")
    
    # Charger les credentials si disponibles
    creds = load_credentials()
    if creds:
        print(f"Credentials trouvés pour {creds['ip']}")
    else:
        print("Aucun credential trouvé.")
        print("\nPour enregistrer cet appareil:")
        print("1. Sur la PS5: Paramètres → Système → Connexion à distance → Lier un appareil")
        print("2. Notez le code PIN affiché")
        print("3. Installez: pip install pyremoteplay")
        print("4. Utilisez la bibliothèque pour vous enregistrer\n")
    
    # Tenter le réveil
    print(f"Envoi du paquet de réveil à {PS5_IP}:9295...")
    if send_wakeup_packet(PS5_IP):
        print("✓ Paquet envoyé avec succès")
    else:
        print("✗ Échec de l'envoi")

if __name__ == '__main__':
    main()
