#!/usr/bin/python3
import os
import json
import time
import requests
import urllib3
import subprocess
import dbm
import signal


# objectif 1 : afficher sur la sortie standard l'identifiant de la scene rubis
# objectif 2 : afficher sur la sortie standard les infos du stream


class ePhilipsHue:
    def __init__(self, hub_ip):
        self._version = "0.0.1"
        self._jeedom_ip = hub_ip
        self._hub_ip = "192.168.1.172"
        self._token = "***REMOVED***"
        self._resources = self.getResources()
        self._infoCommands = []
        self.pidStringKey = "ePhilipsHue_{ip}_pid".format(ip=hub_ip)
        with dbm.open("cache", "c") as db:
            if db.get(self.pidStringKey) != None:
                raise Exception("spam", "eggs")

    def version(self):
        return self._version

    def getResources(self):
        "Retourne un json des ressources disponible sur le hub Philips Hue - Méthode Requests/Module"
        urllib3.disable_warnings(urllib3.exceptions.InsecureRequestWarning)
        headers = {
            "Content-Type": "application/json",
            "hue-application-key": self._token,
        }
        url = "https://{ip}/clip/v2/resource".format(ip=self._hub_ip)
        res = requests.get(url, headers=headers, verify=False)
        return res.json()["data"]

    def getByName_id(self, name, type):
        for r in self._resources:
            if r["type"] == type and r["metadata"]["name"] == name:
                return r["id"]

    def getByName_rid(self, name, type, rtype):
        for r in self._resources:
            if r["type"] == type and r["metadata"]["name"] == name:
                for s in r["services"]:
                    if s["rtype"] == rtype:
                        return s["rid"]

    def addInfoCommand(self, _name, _type, _rtype, _jeedomId, _reachingParameter):
        infoCmd = ePhilipsHue.InfoCommand(
            self, _name, _type, _rtype, _jeedomId, _reachingParameter
        )
        self._infoCommands.append(infoCmd)

    def stream_curl(self):
        url = "https://{ip}/eventstream/clip/v2".format(ip=self._hub_ip)
        curl_command = "curl --insecure -s -N  -H 'hue-application-key: {token}' -H 'Content-Type: application/json' -H 'Accept: text/event-stream' {url} ".format(
            token=self._token, url=url
        )
        myPopen = subprocess.Popen(
            curl_command, shell=True, stdout=subprocess.PIPE, encoding="ascii"
        )
        print("pid : {0}".format(myPopen.pid))
        with dbm.open("cache", "c") as db:
            db[self.pidStringKey] = str(myPopen.pid)
        while True:
            line = myPopen.stdout.readline()
            if myPopen.poll() is not None:
                break
            yield line

    def stream_start(self):
        try:
            stream = self.stream_curl()
            # print( "pid : {0}".format(ePhilipsHue.pid) )
            lastId = ""
            for o in stream:
                params = {}
                if o.startswith("id:"):
                    lastId = o.split(":")[1].strip()
                if o.startswith("data:"):
                    data = json.loads(o[6:])[0]
                    params["creationtime"] = data["creationtime"]
                    params["type"] = data["type"]
                    params["data"] = data["data"]
                    params["id"] = lastId
                    new_update = HueUpdate(params)
                    print(new_update)
                    for infoCmd in self._infoCommands:
                        value = new_update.getValue(infoCmd)
                        infoCmd.setValue(value)
                        infoCmd.sendJsonRPC()
        except:
            self.stream_stop()

    def stream_stop(self, ip=0):
        if ip != 0:
            pidStringKey = "ePhilipsHue_{ip}_pid".format(ip=ip)
        else:
            pidStringKey = self.pidStringKey
        with dbm.open("cache", "c") as db:
            pid = int(db[pidStringKey])
            os.kill(pid, signal.SIGKILL)
            print("stop stream pid : {0} killed".format(pid))
            del db[pidStringKey]

    class InfoCommand:
        def __init__(self, hueInstance, _name, _type, _rtype, _jeedomId, reachingParam):
            self.name = _name
            self.type = _type
            self.rtype = _rtype
            self.jeedomId = _jeedomId
            self.reachingParameter = reachingParam
            self.hueId = hueInstance.getByName_rid(_name, _type, _rtype)
            self.value = 0
            self.jsonRPC = {}
            self.jsonRPC["url"] = "http://{ip}/core/api/jeeApi.php".format(
                ip=hueInstance._jeedom_ip
            )
            self.jsonRPC["body"] = ""

        def __str__(self):
            return "{type} '{name}' : {hueId} : {param}".format(
                name=self.name,
                type=self.type.capitalize(),
                hueId=self.hueId,
                param=self.reachingParameter,
            )

        def setValue(self, value):
            self.value = value

        def sendJsonRPC(self):
            self.jsonRPC["body"] = {
                "jsonrpc": "2.0",
                "method": "cmd::event",
                "params": {
                    "id": self.jeedomId,
                    "value": self.value,
                    "apikey": "***REMOVED***",
                    "datetime": "0",
                },
            }
            data = json.dumps(self.jsonRPC["body"])
            uri = "{url}?request={body}".format(url=self.jsonRPC["url"], body=data)
            # print(uri)
            req = requests.get(
                uri, headers={"Content-Type": "application/json"}, verify=False
            )
            print("{0} {1}\n".format(uri, req.text))


class HueUpdate:
    def __init__(self, data):
        self.creationtime = data["creationtime"]
        self.type = data["type"]
        self.id = data["id"]
        self.data = data["data"]

    def __str__(self):
        return "{creationtime} : {type} {id}".format(
            creationtime=self.creationtime, type=self.type, id=self.id
        )

    def getValue(self, infoCmd):
        for d in self.data:
            if d["id"] == infoCmd.hueId:
                value = self.reachParam(d, infoCmd.reachingParameter)
        print(
            "{0} -> {1} = {2}".format(infoCmd.hueId, infoCmd.reachingParameter, value)
        )
        return value

    def reachParam(self, _json, _setting):
        for dk, dv in _json.items():
            for sk, sv in _setting.items():
                if dk == sk:
                    if type(dv) == type({}) and type(sv) == type({}):
                        return self.reachParam(dv, sv)
                    elif sv == "?":
                        return dv


if __name__ == "__main__":
    start = time.time()
    ##############################

    hue = ePhilipsHue("192.168.1.172")
    print("version : {0}".format(hue.version()))
    hue.addInfoCommand(
        "Ambiance RDC", "zone", "grouped_light", 7759, {"dimming": {"brightness": "?"}}
    )
    hue.stream_start()

    ##############################
    end = time.time()
    elapsed = end - start
    print(f"Temps d'exécution : {elapsed:.2}ms")
