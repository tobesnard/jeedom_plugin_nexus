

import sys
import time
import os
from scapy.all import sniff, Dot11Deauth

class JammingDetection:
    def __init__(self, interface, threshold_pps=1000, threshold_deauth=50):
        self.interface = interface
        self.threshold_pps = threshold_pps
        self.threshold_deauth = threshold_deauth
        self.stats = {
            'packet_count': 0,
            'deauth_count': 0,
            'start_time': time.time()
        }
        self._sniffer = None
        self._running = False

    def _detect_jamming(self, pkt):
        self.stats['packet_count'] += 1
        if pkt.haslayer(Dot11Deauth):
            self.stats['deauth_count'] += 1

        elapsed = time.time() - self.stats['start_time']
        if elapsed >= 1.0:
            pps = self.stats['packet_count'] / elapsed
            if pps > self.threshold_pps:
                print(f"[!] ALERTE : Saturation détectée ({int(pps)} pps)")
            if self.stats['deauth_count'] > self.threshold_deauth:
                print(f"[!] ALERTE : Attaque Deauth probable ({self.stats['deauth_count']} trames/s)")
            self.stats['packet_count'] = 0
            self.stats['deauth_count'] = 0
            self.stats['start_time'] = time.time()

    def start_service(self):
        print(f"[*] Surveillance sur {self.interface}...")
        self._running = True
        try:
            sniff(iface=self.interface, prn=self._detect_jamming, store=0, stop_filter=lambda x: not self._running)
        except KeyboardInterrupt:
            print("\n[*] Arrêt du scan.")
        except Exception as e:
            print(f"[-] Erreur : {e}")

    def stop_service(self):
        print("[*] Arrêt demandé du service de détection.")
        self._running = False



def list_network_interfaces():
    # Liste uniquement les interfaces Wi-Fi (wlan* ou wl*)
    interfaces = []
    try:
        for iface in os.listdir('/sys/class/net/'):
            if iface.startswith('wlan') or iface.startswith('wl'):
                interfaces.append(iface)
    except Exception as e:
        print(f"[-] Erreur lors de la détection des interfaces : {e}")
    return interfaces

if __name__ == "__main__":
    interfaces = list_network_interfaces()
    if not interfaces:
        print("[-] Aucune interface Wi-Fi détectée ! (nommée wlan* ou wl*)")
        print("    Vérifiez que votre carte Wi-Fi est présente et active.")
        sys.exit(1)
    print("[*] Interfaces Wi-Fi détectées : " + ", ".join(interfaces))
    detectors = []
    try:
        for iface in interfaces:
            print(f"[*] Lancement de la surveillance sur {iface}...")
            detector = JammingDetection(interface=iface)
            detectors.append(detector)
            detector.start_service()
    except KeyboardInterrupt:
        print("\n[*] Arrêt demandé par l'utilisateur.")
        for detector in detectors:
            detector.stop_service()