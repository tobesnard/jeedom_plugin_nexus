#!/bin/bash

echo 'Event Stream'

hub_ip=192.168.1.215
jeton=7fy3lMUACAR7A7KUWcxDgh-n3WrLJ4bjqJG6bp59
jeton=Q3t3U-TWmmQLhJJGd-pIe6oVwUXpzIWStQoaplgo

url="https://${hub_ip}/eventstream/clip/v2"

curl --insecure -N -H "hue-application-key: ${jeton}" -H 'Accept: text/event-stream' -H "Content-Type: application/json" ${url} | tee hue.json
