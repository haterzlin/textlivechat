#!/usr/bin/python3

import sys
import requests
from unidecode import unidecode

user = ""
attributes = {}
attributename = ""

webserviceurl = "https://sso.cpost.cz/opensso/identity/attributes"
cookiename = "SSOCPostProd"
cookievalue = ""
cookielines = sys.argv[1][12:].split(";") # removes HTTP_COOKIE= and splits to lines
for line in cookielines:
    if line.split("=")[0].strip() == cookiename:
        cookievalue = line.split("=")[1]

try:
    r = requests.get(webserviceurl, cookies={cookiename: cookievalue}, verify=False)
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
except OSError:
#except ValueError:
    pass

print(unidecode(user).replace(" ", ""));
#print(user.replace(" ", ""));

