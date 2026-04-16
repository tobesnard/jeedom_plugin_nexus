import sys
from scapy.all import sniff, Dot11, Dot11Deauth, Dot11Beacon
from collections import Counter
import time

# Configuration
INTERFACE = "wlan0mon"  # Remplacez par votre interface en mode moniteur
THRESHOLD_PPS = 1000    # Seuil de paquets par seconde pour suspicion de saturation
THRESHOLD_DEAUTH = 50   # Seuil de trames de désauthentification

stats = {
    'packet_count': 0,
    'deauth_count': 0,
    'start_time': time.time()
}

def detect_jamming(pkt):
    stats['packet_count'] += 1
    
    # Détection spécifique des trames de désauthentification (Flood)
    if pkt.haslayer(Dot11Deauth):
        stats['deauth_count'] += 1

    # Analyse par intervalle de 1 seconde
    elapsed = time.time() - stats['start_time']
    if elapsed >= 1.0:
        pps = stats['packet_count'] / elapsed
        
        if pps > THRESHOLD_PPS:
            print(f"[!] ALERTE : Saturation détectée ({int(pps)} pps)")
        
        if stats['deauth_count'] > THRESHOLD_DEAUTH:
            print(f"[!] ALERTE : Attaque Deauth probable ({stats['deauth_count']} trames/s)")
            
        # Réinitialisation des compteurs
        stats['packet_count'] = 0
        stats['deauth_count'] = 0
        stats['start_time'] = time.time()

try:
    print(f"[*] Surveillance sur {INTERFACE}...")
    sniff(iface=INTERFACE, prn=detect_jamming, store=0)
except KeyboardInterrupt:
    print("\n[*] Arrêt du scan.")
    sys.exit(0)
except Exception as e:
    print(f"[-] Erreur : {e}")