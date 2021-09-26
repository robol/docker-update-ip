#/usr/bin/env python3 

import os, requests, sys, json, socket

from flask import Flask, request

app = Flask(__name__)

API_KEY = os.getenv("API_KEY")
PDNS_SERVER  = os.getenv("PDNS_SERVER")
PDNS_API_KEY = os.getenv("PDNS_API_KEY")

def check_domain(domain):
    return isinstance(domain, str)

@app.route('/')
def index():
    domain = request.args.get("domain")
    api = request.args.get("key")
    address = request.args.get("address")


    if not check_domain(domain):
        return "Invalid domain specified", 404

    previous_address = socket.gethostbyname(domain)
    if previous_address == address:
        return "No need to update", 200        

    if address is None:
        address = request.access_route[0]

    if api == API_KEY:
        if update_dns_name(domain, address):
            return "Ok, address updated correctly to %s (previously %s)" % (address, previous_address)
        else:
            return "The update failed with an internal error", 500
    else:
        return "Unauthorized", 401

def update_dns_name(record, address):

    global PDNS_SERVER, PDNS_API_KEY

    if record[-1] != ".":
        record = record + "."

    data = {
        "rrsets": [ 
            {
                "name": record, 
                "type": "A", 
                "changetype": "REPLACE", 
                "ttl": 300,
                "records": [ 
                    {   
                        "content": address, 
                        "disabled": False, 
                        "name": record, 
                        "type": "A", 
                        "priority": 0 
                    } 
                ] 
            } 
        ] 
    }

    zone = ".".join(record.split(".")[1:])

    url = PDNS_SERVER + "/api/v1/servers/localhost/zones/" + zone
    headers = {
        "X-Api-Key": PDNS_API_KEY
    }

    r = requests.patch(url, data = json.dumps(data), headers = headers, verify = False)

    if r.text != "":
        response = json.loads(r.text)

        if "error" in response:
            return response["error"]

    return True

