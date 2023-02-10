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
    print(key + " -> " + val)
except KeyError:
    if key == "0":
        for key in jsonDict:
            val = jsonDict[key]
            print(key + " -> " + val)
    else:
        print("Key : " + key + " is not found in source")
