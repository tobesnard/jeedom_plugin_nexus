from pyremoteplay import RPDevice

device = RPDevice("192.168.1.226")

# Première fois : enregistrement (demande PIN)
device.register()

# Réveiller la PS5
device.wakeup()
print("PS5 réveillée !")
