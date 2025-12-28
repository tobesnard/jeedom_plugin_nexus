#!/usr/bin/python3
import ePhilipsHue
import dbm
import os
import signal

cahefile = "/var/www/html/data/php/philipsHue/cache"


def start_stream(ip):
    hue = ePhilipsHue.ePhilipsHue(ip)
    print("version : {0}".format(hue.version()))
    hue.addInfoCommand(
        "Ambiance RDC", "zone", "grouped_light", 7759, {"dimming": {"brightness": "?"}}
    )
    hue.stream_start()


def stop_stream(ip):
    pidStringKey = "ePhilipsHue_{ip}_pid".format(ip=ip)
    with dbm.open(cahefile, "c") as db:
        pid = db.get(pidStringKey)
        print("stop stream  pid = {0}".format(pid))
        if pid != None:
            pid = int(pid)
            os.kill(pid, signal.SIGKILL)
            print("stop stream pid : {0} killed".format(pid))
            del db[pidStringKey]
        else:
            print("stop stream  not killed")


def pid_stream(ip):
    pidStringKey = "ePhilipsHue_{ip}_pid".format(ip=ip)
    with dbm.open(cahefile, "c") as db:
        pid = db.get(pidStringKey)
        if pid == None:
            print(int(0))
        else:
            print(int(pid))

