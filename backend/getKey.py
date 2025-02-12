import json
import sys
from os.path import exists

def usage():
    print(sys.argv[0] + ": json_source key")
try:
    dictSource = sys.argv[1]
    key = sys.argv[2]
except:
    usage()

if not exists(dictSource):
    print("ERR: Source file: " + dictSource + " does not exist")
    exit()
with open(dictSource, 'r', encoding = "utf=8") as infile:
    jsonDict = json.load(infile)

try:
    val = jsonDict[key]
    print(key + " -> " + str(val))
except KeyError:
    if key == "0":
        with open("listing", 'w', encoding = "utf-8") as outfile:            
            d = ""
            for key in jsonDict:                
                val = jsonDict[key]                
                outfile.write(d + key + " -> " + str(val))              
                d = "\n"
            print(len(jsonDict))
    elif key == "show_keys":
        for el in jsonDict.keys():
            print(el)
    else:
        print("Key : " + key + " is not found in source")
