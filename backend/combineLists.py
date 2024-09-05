import sys
import os
from os.path import exists
if len(sys.argv) < 2:
    print("usage: " + sys.argv[0] + " infile_1")
    exit()
fileName = sys.argv[1]
tmpFile = fileName + "_tmp"
if exists(tmpFile): 
    os.remove(tmpFile)
os.rename(fileName, tmpFile)
os.system("git checkout -- " + fileName)
with open(fileName, 'r', encoding='utf-8') as infile_1:
    wordList_1 = infile_1.readlines()

with open(tmpFile, 'r', encoding='utf-8') as infile_2:
    wordList_2 = infile_2.readlines()

words = list()
for w in wordList_1:
    words.append(w)

for w in wordList_2:
    if w not in words:
        words.append(w)

words.sort()
print("Combined wordList_1 with " + str(len(wordList_1)) + " words with wordList_2 with " + str(len(wordList_2)) + " words to get " + str(len(words)) + " unique words.")
with open(fileName, 'w',encoding='utf-8') as outfile:
    for w in words:
        outfile.write(w)

