import sys
import os
from os.path import exists
import json
if len(sys.argv) < 2:
    print("usage: " + sys.argv[0] + " infile_1")
    exit()
fileName = sys.argv[1]
tmpFile = fileName + "_tmp"

if exists(tmpFile):
    os.remove(tmpFile)
os.rename(fileName, tmpFile)
os.system("git checkout -- " + fileName)

with open (fileName, 'r', encoding='utf-8') as infile1:
    json1 = json.load(infile1)

with open(tmpFile, 'r', encoding='utf-8') as infile2:
    json2 = json.load(infile2)
   
words = dict()
for key in json1:
    words[key] = json1[key]

for key in json2:
    if not key in words:
        words[key] = json2[key]

cnt1 = str(len(json1))
cnt2 = str(len(json2))

print("Combined wordDefList_1 with " + cnt1 + " words with wordDefList_2 with " + cnt2 + " words to get " + str(len(words)) + " unique words.")
with open(fileName, 'w',encoding='utf-8') as outfile:
    json.dump(words, outfile)
