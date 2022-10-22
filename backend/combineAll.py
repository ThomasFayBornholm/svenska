import sys
import os
from os.path import exists
import json

wordClasses = ("verb", "adjektiv", "adverb", "substantiv_en", "substantiv_ett", "all", "test")
for c in wordClasses:
    # Plain Listing
    fileName = c + "-only"
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
    cnt1 = str(len(wordList_1))
    cnt2 = str(len(wordList_2))
    print("Combined " + fileName + " (git) with " + cnt1 + " words with " + fileName + " (local) with " + cnt2 + " words to get " + str(len(words)) + " unique words.")
    with open(fileName, 'w',encoding='utf-8') as outfile:
        for w in words:
            outfile.write(w)

    # Definition Listing
    # Not valid for 'all' class listing
    if (c != "all"):
        fileName = c + "-def"
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

        print("Combined " + fileName + " (git) with " + cnt1 + " words with " + fileName + " (local) with " + cnt2 + " words to get " + str(len(words)) + " unique words.")
        with open(fileName, 'w',encoding='utf-8') as outfile:
            if len(words) > 0:
                json.dump(words, outfile)
