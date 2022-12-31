import json
import sys
import re
# already have word and type
#fetch definition

nEl = 50000
twoTabs = "      "

def usage():
    "usage: " + sysargv[0] + "nEl"
if (len(sys.argv) > 1):
    try:
        nEl = int(sys.argv[1])
    except:
        usage()
def getPos(wc):
    if wc == "verb":
        return "verb"
    elif wc == "adjektiv":
        return "adj."
    elif wc == "adverb":
        return "adv."
    else:
        return "subst."

def getDef(word,defs):
    out = "No definition found"
    try:
        tmp = defs[word]
    except:
        print("No definition for " + word)
    tmpArr = tmp.split("<br>")
    # Show each definition
    
    isComp = False
    i = 0
    # Throw away JFR, SYN etc. comparators
    comp = ["JFR ", "SYN. ", "MOTSATS ", "SE "]
    removeList = []
    tmpArr = list(filter(None, tmpArr))
    for el in tmpArr:
        for compEl in comp:                    
            if el.startswith(compEl):                
                removeList.append(el)
                
    for rmEl in removeList:
        tmpArr.remove(rmEl)

    if len(tmpArr) > 1:
        out = ""

    for el in tmpArr:
        if el != '':
            i = i + 1            
            line = el.replace("● ","")
            # Remove any existing numerals
            if line[0] == ' ':
                line = line[1:]
            for j in range(1,20):
                if line.startswith(str(j) + " "):
                    line = line.replace(str(j) + " ", "")
                    break
            # Remove html links
            re1 = "(.*\(.*förbindelse.*span>\)).*"
            tmp = re.search(re1,line)            
            if (tmp != None):                          
                line = line.replace(tmp.group(1),"")
            re1 = "(.*\(.*sammansättn.,.*span>\)).*"
            tmp = re.search(re1,line)
            if (tmp != None):                          
                line = line.replace(tmp.group(1),"")
            re1 = "(.*\(se äv\. .*span>\)).*"
            tmp = re.search(re1,line)
            if (tmp != None):                          
                line = line.replace(tmp.group(1),"")
        
            line = line[:127]        
            if (len(tmpArr) > 1):
                out += twoTabs + "<lexeme>\n"
                out += twoTabs + "<lexnr>" + str(i) + "</lexnr>\n" + twoTabs + "<definition>" + line + "</definition>\n"
                out += twoTabs + "</lexeme>\n"
            else:
                out = twoTabs + "<lexeme>\n"
                out += twoTabs + "<definition>" + line + "</definition>\n"
                out += twoTabs + "</lexeme>\n"
    return out
def getInfl(word,metas):
    out = word
    try:
        tmp = metas[word].split("<br>")[0];
        tmp = tmp.replace(", presens","")
        tmp = tmp.replace("</i>även åld. <i>","")
        tmp = tmp.replace("</i>även <i>","")
        tmp = tmp.replace("</i>eller <i>","")
        tmp = tmp.replace("</i>eller vardagligt <i>","")
        tmp = tmp.replace("</i>även vardagligt <i>","")
        tmp = tmp.replace(", komparativ","")
        tmp = tmp.replace(", superlativ","")
        if tmp is not None:
            tmpArr = tmp.split(' ')
            out = ""
            delim = ""
            for el in tmpArr:                
                out += delim + el
                delim = " "
    except:
        print("No meta data for " + word)
    return out

preAmble='''<?xml version="1.0" encoding="utf-8" ?>
<!DOCTYPE lexin [
<!ELEMENT lexin (lemma-entry+)>
<!ELEMENT lemma-entry		(form, pronunciation, inflection, pos, lexeme*)>
<!ELEMENT  form			(#PCDATA)>
<!ELEMENT  pronunciation	(#PCDATA)>
<!ELEMENT  inflection		(#PCDATA)>
<!ELEMENT  pos			(#PCDATA)>
<!ELEMENT  lexeme 	(lexnr?, definition?)>
<!ELEMENT  lexnr		(#PCDATA)>
<!ELEMENT  definition		(#PCDATA)>
]>

<lexin>
'''
                                
srcPath = "C:/xampp/htdocs/svenska/backend/"
wordClasses = ["verb","adjektiv","adverb","substantiv_en","substantiv_ett"]

i = 0
out = preAmble
for wc in wordClasses:

    with open(srcPath + wc + "-only", 'r',encoding="utf-8") as wordList:
        contents = wordList.readlines()

    with open(srcPath + wc + "-def", 'r',encoding="utf-8") as fh:
        defs = json.load(fh)
    with open(srcPath + wc + "-meta", 'r', encoding="utf-8") as fh:
        metas = json.load(fh)
    with open(srcPath + wc + "-score", 'r', encoding="utf-8") as fh:
        scores = json.load(fh)

    # Create each word entry


    leadingDelim = ""
    for el in contents:
        score = 0
        word = el.strip()
        try:
            score = scores[word]
        except:
            print("Could not find score for " + word)
        if (int(score) < 2) or True:
            i = i + 1
            if (i > nEl):
                break        
            tmp = ""
        
        tmp += leadingDelim + "<lemma-entry>\n"
        tmp += twoTabs + "<form>" + word + "</form>\n"
        infl = getInfl(word,metas)            
        tmp += twoTabs + "<inflection>" + infl + "</inflection>\n"
        tmp += twoTabs + "<pos>" + getPos(wc) + "</pos>\n"
        # definition
        tmp += getDef(word,defs)
        tmp += "</lemma-entry>"

        out += tmp
        leadingDelim = "\n"
        
out += "\n</lexin>"
outname = "dict.xml"
print("Writing " + str(i) + " elements to " + outname)
with open(srcPath + outname,'w',encoding = "utf-8") as fh:
    fh.write(out)