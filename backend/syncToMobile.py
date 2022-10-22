import json
template='''<?xml version="1.0" encoding="utf-8"?>
<resources>
    <string-array name="<arr_1>">
<keyList>
    </string-array>
    <string-array name="<arr_2>">
<valList>
    </string-array>
</resources>'''

wordClasses=("verb", "adjektiv", "adverb", "substantiv_en", "substantiv_ett", "test")
path = 'C:/Users/glatho01/AndroidStudioProjects/Svenska/app/src/main/res/values'
print("Writing output to path: " + path)
for c in wordClasses:
    # Mobile platform needs separate arrays for the keys and values and will combine internally
    name = c + "-def"
    with open(name, 'r', encoding = 'utf-8') as infile:
        jsonDict=json.load(infile)

    keyList=()
    valList=()
    strKeys=""
    strVals=""
    for key in jsonDict.keys():
        strKeys += "        <item>" + key + "</item>\n"
        strVals += "        <item>" + jsonDict[key] + " </item>\n"
    with open(path + "/" + name + ".xml", 'w', encoding='utf-8') as outfile:
        strOut = template.replace("<arr_1>",c + "_keys") 
        strOut = strOut.replace("<arr_2>", c + "_values")
        strOut = strOut.replace("<keyList>", strKeys)
        strOut = strOut.replace("<valList>", strVals)
        strOut = strOut.replace("<br>","_br_")
        strOut = strOut.replace("<nedsättande>","(nedsättande)")
        strOut = strOut.replace("'",'"')
        print("Writing " + str(len(jsonDict)) + " defs to " + name + ".xml")
        outfile.write(strOut)

