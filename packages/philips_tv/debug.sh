#!/bin/bash

TV_IP="192.168.1.54"
TIMEOUT=5

echo "=== Diagnostic Philips TV sur $TV_IP ==="

# 1. Vérifier la connectivité IP
echo "[+] Test ping..."
if ping -c 2 $TV_IP >/dev/null 2>&1; then
    echo "    ✅ TV répond au ping"
else
    echo "    ❌ TV ne répond pas au ping (vérifie IP ou réseau)"
    exit 1
fi

# 2. Tester les ports
for PORT in 8008 1925 1926; do
    echo "[+] Test port $PORT..."
    if nc -z -w2 $TV_IP $PORT >/dev/null 2>&1; then
        echo "    ✅ Port $PORT ouvert"
    else
        echo "    ❌ Port $PORT fermé"
    fi
done

# 3. Tester endpoints connus
for PORT in 8008 1925 1926; do
    echo "[+] Test endpoints sur port $PORT..."
    for ENDPOINT in "/" "/system" "/apps" "/1/system"; do
        echo "    → GET $ENDPOINT"
        curl -s -m $TIMEOUT http://$TV_IP:$PORT$ENDPOINT | head -c 200
        echo -e "\n"
    done
done

echo "=== Résultats ==="
echo "Si /system ou /1/system répond sur 1925 ou 1926 → configure pylips.py avec ce port."
echo "Si seul 8008 répond mais retourne 404 → c’est Google Cast, pas l’API JSON Philips."
echo "Augmente aussi le timeout dans pylips.py (timeout=$TIMEOUT ou plus)."
