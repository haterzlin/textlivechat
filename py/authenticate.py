#!/usr/bin/python3

import sys
import requests
from unidecode import unidecode
import configparser

config = configparser.ConfigParser()
config.readfp(open('/home/chat/conf/websocket_chat_server.ini'))

user = ""
attributes = {}
attributename = ""
cookievalue = ""
webserviceurl = config["global"]["AUTHENTIZATION_WEBSERVICE_URL"]
cookiename = config["global"]["AUTHENTIZATION_COOKIENAME"]
try:
    cookielines = sys.argv[1][12:].split(";") # removes HTTP_COOKIE= and splits to lines
except IndexError:
    # print("no cookies received")
    cookielines = ""
for line in cookielines:
    if line.split("=")[0].strip() == cookiename:
        cookievalue = line.split("=")[1]

try:
    #print("trying authenticate to " + webserviceurl + " with " + cookiename + " = " + cookievalue)
    r = requests.get(webserviceurl, cookies={cookiename: cookievalue}, verify=False)
    #print(r.status_code)
    if r.status_code == 200:
        lines = r.text.split("\n")
        for line in lines:
            if line[:27] == "userdetails.attribute.name=":
                try:
                    attributename = line[27:]
                except IndexError:
                    pass
            else:
                try:
                    attributes[attributename] = line[28:]
                    attributename = ""
                except IndexError:
                    pass
        try:
            user = attributes["givenname"] + " " + attributes["sn"] + ":#" + attributes["uid"]
            #user = attributes["uid"] + ":" + attributes["uid"]
        except:
            pass
#except OSError:
#except ValueError:
except IndexError:
    pass

print(unidecode(user).replace(" ", ""));
#print(user.replace(" ", ""));

