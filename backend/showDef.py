import json
import sys
word = sys.argv[1]
name="verb-def"
with open(name) as infile:
    jStr = infile.read()

jObj= json.loads(jStr)
print(word + ":")
print(jObj[word])
