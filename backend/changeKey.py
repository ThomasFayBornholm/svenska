import json
import sys
from os.path import exists

def usage():
    print(sys.argv[0] + ": json_source key new_key_value")
try:
    dictSource = sys.argv[1]
    key = sys.argv[2]
    newVal = sys.argv[3]
    print(newVal)
except:
    usage()
    
with open(dictSource, 'r', encoding = "utf=8") as infile:
    jsonDict = json.load(infile)


# Permit only changing of existing keys

try:
    val = jsonDict[key]
    print("(Current key) " + key + " -> " + val)
    if not exists(dictSource):
        print("Source file: " + dictSource + " does not exist")
        exit()
    with open(dictSource,'r+',encoding="utf-8") as fh:
        data = json.load(fh)
        if newVal == "rm":
            data.pop(key, None)
            print("Deleted key: " + key);
        else:
            data[key] = newVal        
            print("(New key value) " + data[key]);
        fh.seek(0)
        json.dump(data,fh)
        fh.truncate()
        
except KeyError:
    print("Key : " + key + " is not found in source")
