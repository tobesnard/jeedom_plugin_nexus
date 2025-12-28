#! /bin/bash
# [{"success":{"username":"7fy3lMUACAR7A7KUWcxDgh-n3WrLJ4bjqJG6bp59","clientkey":"***REMOVED***"}}]
# retourne un json de la ressource demandée
# ./hue.sh <nom de la resource

hub_ip=192.168.1.172
jeton=7fy3lMUACAR7A7KUWcxDgh-n3WrLJ4bjqJG6bp59
jeton=***REMOVED***

if [ -z $1 ]; then
    url="https://${hub_ip}/clip/v2/resource"
else
    url="https://${hub_ip}/clip/v2/resource/${1}"
fi

curl --insecure -s -X GET ${url} -H "hue-application-key: ${jeton}" -H "Content-Type: application/json" | tee hue.json
