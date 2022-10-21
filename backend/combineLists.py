import sys
if len(sys.argv) < 4:
    print("usage: " + sys.argv[0] + " infile_1 infile_2 outfile")
    exit()
f1 = sys.argv[1]
f2 = sys.argv[2]
outName = sys.argv[3]
with open(f1, 'r', encoding='utf-8') as infile_1:
    wordList_1 = infile_1.readlines()

with open(f2, 'r', encoding='utf-8') as infile_2:
    wordList_2 = infile_2.readlines()

words = list()
for w in wordList_1:
    words.append(w)

for w in wordList_2:
    if w not in words:
        words.append(w)

words.sort()
print("Combined wordList_1 with " + str(len(wordList_1)) + " words with wordList_2 with " + str(len(wordList_2)) + " words to get " + str(len(words)) + " unique words.")
with open(outName, 'w',encoding='utf-8') as outfile:
    for w in words:
        outfile.write(w)

