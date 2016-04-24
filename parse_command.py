#!/usr/bin/python

""" 
parse commands for chat server, do whatever is needed and pri

 - commands:
   /webrtc to allow webrtc signaling functionality
   /dice throws dice
   message saves message from user to log
"""

import sys 
from random import randint

def throwDice(x, y, z):
    # throw x y-sided dice then write it to room. If value on the die is greater or equal to z, throw again
    max_dice_number = 100;
    results = []
    printinfo = ""
    try:
         if int(x) > max_dice_number:
             x = max_dice_number;
             printinfo = " Max dice number is grater than " + str(max_dice_number) + " throwing only first " + str(max_dice_number) + " dice."
         for a in range(int(x)):
             value = randint(1, int(y))
             while value >= int(z) and int(z) > 1: # need to check if it's possible, that while will end, but what about randomizer?
                 results.append(str(value))
                 value = randint(1,int(y))
             results.append(str(value))
         print printinfo + " Throws " + str(x) + " " + str(y) + "-sided dice, throws again on " + str(z) + ": " + str(", ".join(results))
    except ValueError:
        print " Bad dice syntax, input is not numeric"

# main

words = sys.argv
if words[1] == "/dice":
    try:
        throwDice(words[2], words[3], words[4])
    except IndexError:
        print " Bad dice syntax, usage: /dice dice_count number_of_dice_sides target_number_for_throw_again"
elif words[1] == "/color":
    try:
        print " Changed color to " + str(words[2])
    except IndexError:
        print " Not changed color, missing color number"

else:
    print " unknown command " + " ".join(words[1:])
