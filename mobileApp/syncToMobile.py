import json
import os
templateList='''<?xml version="1.0" encoding="utf-8"?>
<resources>
    <string-array name="<class>_words">
<vallist>
    </string-array>
</resources>
'''
template='''<?xml version="1.0" encoding="utf-8"?>
<resources>
    <string-array name="<arr_1>">
<keyList>
    </string-array>
    <string-array name="<arr_2>">
<valList>
    </string-array>
</resources>'''

wordClasses=("verb", "adjektiv", "adverb", "substantiv_en", "substantiv_ett")
home=os.environ["HOMEDRIVE"] + os.environ["HOMEPATH"]
srcPath = "../backend/"
path = home + "/AndroidStudioProjects/Svenska/app/src/main/res/values"
print("Writing output to path: " + path)
for c in wordClasses:
    iWritten = 0
    # Word listing   
    out = ""
    strOut = templateList   
    name = c + "-score"
    with open(srcPath + name, 'r', encoding = 'utf-8') as infile:
        scoreDict =json.load(infile)
    name = c + "-def"
    with open(srcPath + name, 'r', encoding = 'utf-8') as infile:
        defDict=json.load(infile)        
    print(str(len(defDict)) + " words are defined")
    
    for w in defDict:
        try:
            score = int(scoreDict[w])
        except KeyError:
            score = 0
        if score != 2:
            out += "        <item>" + w.replace("\n","") + "</item>\n"
    strOut = strOut.replace("<class>",c)
    strOut = strOut.replace("<vallist>",out)
    name = c + "-only" + ".xml"    
    print("Writing " + str(len(defDict)) + " words to " + name)
    with open(path + "/" + name, 'w', encoding = 'utf-8') as outfile:
        outfile.write(strOut);
    # Definition 
    # Mobile platform needs separate arrays for the keys and values and will combine internally    
        
    keyList=()
    valList=()
    strKeys=""
    strVals=""
    for key in defDict.keys():
        score = 0
        try:
            score = int(scoreDict[key])
        except KeyError:
            print("No score found for: " + key)
        if score != 2:    
            strKeys += "        <item>" + key + "</item>\n"
            tmp = defDict[key]             
            d = ""
            tmpAtt = tmp.split("<br>")
            defTmp = ""
            for l in tmpAtt:
                if "<span" in l:
                    p1 = l.find("<span")
                    p2 = l.find("<b>",p1)
                    rep = l[p1:p2]                
                    l = l[:p1] + l[p2+3:]
                    l = l.replace("</b></span>","")
                defTmp += d + l
                d="<br>"    
            strVals += "        <item>" + defTmp + " </item>\n"
            iWritten = iWritten + 1
    name = c + "-def.xml"
    with open(path + "/" + name, 'w', encoding='utf-8') as outfile:
        strOut = template.replace("<arr_1>",c + "_keys") 
        strOut = strOut.replace("<arr_2>", c + "_values")
        strOut = strOut.replace("<keyList>", strKeys)
        strOut = strOut.replace("<valList>", strVals)
        strOut = strOut.replace("<br>","_br_")
        strOut = strOut.replace("<nedsättande>","(nedsättande)")
        strOut = strOut.replace("'","\\\"")
        print("Writing " + str(iWritten) + " defs to " + name + ".xml")
        outfile.write(strOut)

