#!/usr/bin/python

""" 
parse commands for chat server, do whatever is needed and pri

 - commands:
   /getusers returns online user names
   /webrtc to allow webrtc signaling functionality
   /dice throws dice
   message saves message from user to log
"""

import sys 
from random import randint

def throwDice(x, y, z):
    # throw x y-sided dice then write it to room. If value on the die is greater or equal to z, throw again
    results = []
    try:
         for a in range(int(x)):
             value = randint(1, int(y))
             while value >= int(z) and int(z) > 1: # need to check if it's possible, that while will end, but what about randomizer?
                 results.append(str(value))
                 value = randint(1,int(y))
             results.append(str(value))
         print " Throws " + str(x) + " " + str(y) + "-sided dice: " + str(", ".join(results))
    except ValueError:
        print " Bad dice syntax, input is not numeric"


def getUserList(roomFile):
    userList = []
    with open(roomFile, 'r') as f:
        for user in f:
            userList.append(user.strip())
    print " Userlist: " + ", ".join(sorted(userList))


# main

words = sys.argv
if words[1] == "/getusers":
    getUserList(words[2])
elif words[1] == "/webrtc":
    pass
elif words[1] == "/dice":
    try:
        throwDice(words[2], words[3], words[4])
    except IndexError:
        print " Bad dice syntax, usage: /dice number_od_dice number_of_dice_sides target_number_for_throw_again"

else:
    print " ".join(words)
