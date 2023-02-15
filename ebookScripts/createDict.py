import json
import sys
import re
# already have word and type
#fetch definition

nEl = 100000
twoTabs = "      "
breakWord = ""

MAXLENWITHCOMP = 111
COMP = ["JFR", "SYN. ", "MOTSATS ", "SE "]
def usage():
    "usage: " + sysargv[0] + "nEl/word"
if (len(sys.argv) > 1):
    try:
        nEl = int(sys.argv[1])
    except:
        breakWord = sys.argv[1]
        nEl = 0
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
        
    i = 0
    # Throw away JFR, SYN etc. comparators       
    '''
    removeList = []
    tmpArr = list(filter(None, tmpArr))
    for el in tmpArr:
        for compEl in COMP:                    
            if el.startswith(compEl):                
                removeList.append(el)
                
    for rmEl in removeList:
        tmpArr.remove(rmEl)
    '''
    
    if len(tmpArr) > 1:
        out = ""
    lastEnd = 0    
    outArr = []
    for el in tmpArr:
        if el != '':
            i = i + 1    
            line = el.replace("● ","")
            line = line.replace("• ","") 
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
            '''
            out = out.replace("<b>","")
            out = out.replace("</b>","")
            '''
            # Permitted definition length is shorter depending on the number of separate defintion points           
            if i > 1:
                end = 125
            else:
                end = 126 - len(word)
            line = line[:end]     
            outArr.append(line)            
            
    return outArr
    
def chop(full, chopPoint):    
    if chopPoint in full:        
        end = full.find(" " + chopPoint)        
        if end != -1:
            return full.substring(0,end)
    else:
        return full
def test(full,chopPoint):
    print("test")
def getInfl(word,metas, wordClass):
    out = ""
    try:        
        tmp = metas[word].split("<br>")[0];
        if wordClass == "verb":
            tmp = tmp.replace(",","")
        tmp = tmp.replace(" presens","")
        if ';' in tmp:
            tmp = tmp.split(";")[0]
        if ',' in tmp:
            tmp = tmp.split(",")[0]
                
        tmp = tmp.replace("</i>även åld. <i>","")
        if wordClass != "adverb":
            tmp = chop(tmp,"</i>även <i>")        
            tmp = chop(tmp,"</i>eller <i>")
        else:
            tmp = tmp.replace("</i>även <i>","")        
            tmp = tmp.replace("</i>eller <i>","")
        tmp = tmp.replace("</i>eller vardagligt <i>","")
        tmp = tmp.replace("</i>även vardagligt <i>","")
        if word != "komparativ":
            tmp = tmp.replace(" komparativ","")
        tmp = tmp.replace(" superlativ","")
        tmp = tmp.replace(" ingen böjning","")
        tmp = tmp.replace("­","")
        tmp = tmp.replace("och plural ","")
        tmp = tmp.replace(" bestämd form","")
        tmp = tmp.replace("neutrum ","")
        tmp = tmp.replace("ngn gång ","")
        tmp = tmp.replace(" komparativ sämre ngn gång dåligare, superlativ sämst ngn gång dåligast","")
        tmp = tmp.replace(" sämre dåligare sämst dåligast","")
          
        if tmp is not None:
            if wordClass == "adverb":
                tmpArr = []
                tmpArr.append(tmp)
            else:
                tmpArr = tmp.split(' ')
            out = ""
            delim = ""
            lastEl = ''
            for el in tmpArr:
                if el != lastEl and el != " " and el != "":
                    out += delim + el.replace("~",word)
                    delim = " "
                    lastEl = el
    except:
        print("No meta data: " + word)
    if (len(out) > 200):
        print("long infl: " + infl)
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
wordClasses = ["verb","adjektiv","substantiv_en","substantiv_ett","adverb","preposition","pronomen","interjektion","konjunktion","subjunktion"]
i = 0
iWritten = 0
out = preAmble
word = ""

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
        tmp = ""
        #if not ' ' in el and word == "död":        
        if not ' ' in el:                  
            if (iWritten == nEl and nEl != 0):
                break       
            word = el.strip()

            i = i + 1
            try:
                score = int(scores[word])
            except:
                score = 0
            if (nEl > 0 or breakWord == word) and score != 2:
                iWritten = iWritten + 1
                tmp += "<lemma-entry>\n"
                tmp += twoTabs + "<form>" + word + "</form>\n"
                infl = getInfl(word,metas,wc)  
                if infl != "":
                    tmp += twoTabs + "<inflection>" + infl + "</inflection>\n"
                tmp += twoTabs + "<pos>" + getPos(wc) + "</pos>\n"
                
                head = tmp
                tmpDef = getDef(word,defs)            
                iDef = 0
                for elDef in tmpDef:
                    isComp = False
                    for elComp in COMP:
                        if elDef.startswith(elComp):                            
                            isComp = True
                            break
                    if not isComp:                             
                        iDef = iDef + 1            
                    if iDef > 1 and not isComp:
                        tmp += "\n" + head.replace("<form>" + word,"<form>" + str(iDef))                                    
                        tmp += twoTabs + "<lexeme>\n"
                        tmp += twoTabs + "<definition>" + elDef + "</definition>\n"
                        tmp += twoTabs + "</lexeme>\n"
                        tmp += "</lemma-entry>"
                    elif isComp:
                        # Add to previous def
                        posStart = tmp.rfind("<definition>") + len("<definition>")
                        posEnd = tmp.rfind("</definition>")
                        lastDef = tmp[posStart:posEnd]
                        print("lastDef = " + lastDef)
                        
                        if posStart != -1 and posEnd != -1:                            
                            newDef = lastDef[:MAXLENWITHCOMP-len(elDef)] + "_br_" + elDef
                            tmp = tmp.replace(lastDef,newDef)
                    else:
                        tmp += twoTabs + "<lexeme>\n"
                        tmp += twoTabs + "<definition>" + elDef + "</definition>\n"
                        tmp += twoTabs + "</lexeme>\n"
                        tmp += "</lemma-entry>"                   
                                               
                out += leadingDelim + tmp
                leadingDelim = "\n"
                if breakWord == word:
                    break
        
out += "\n</lexin>"
outname = "dict.xml"
print("Writing " + str(iWritten) + " elements to " + outname)
print("Last word = " + word + ", index = " + str(i))
with open(srcPath + outname,'w',encoding = "utf-8") as fh:
    fh.write(out)