import json
import sys

name = sys.argv[1]
with open(name, 'r') as fh:
    dictObj = json.load(fh)
    
nBlue = 0
nGreen  = 0
nRed = 0
nAll = 0
delim = ""
out = ""
for key in dictObj:
    nAll = nAll + 1
    score = dictObj[key]
    score = int(score)
    if score == 0:
        nRed = nRed + 1
    elif score == 1:
        nBlue = nBlue + 1
    elif score == 2:
        nGreen = nGreen + 1
    out += delim + key
    delim = "\n"
    
print(nAll)
print(nRed + nBlue + nGreen)
print(nRed)
print(nBlue)
print(nGreen)

with open("scores-test",'w') as fh:
    fh.write(out)