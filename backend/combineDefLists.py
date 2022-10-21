import sys
import json
if len(sys.argv) < 4:
    print("usage: " + sys.argv[0] + " infile_1 infile_2 outfile")
    exit()
f1 = sys.argv[1]
f2 = sys.argv[2]
outName = sys.argv[3]
with open (f1, 'r', encoding='utf-8') as infile1:
    json1 = json.load(infile1)

with open(f2, 'r', encoding='utf-8') as infile2:
    json2 = json.load(infile2)
   
words = dict()
for key in json1:
    words[key] = json1[key]

for key in json2:
    if not key in words:
        words[key] = json2[key]

print("Combined wordDefList_1 with " + str(len(json1)) + " words with wordDefList_2 with " + str(len(json2)) + " words to get " + str(len(words)) + " unique words.")
with open(outName, 'w',encoding='utf-8') as outfile:
    json.dump(words, outfile)
