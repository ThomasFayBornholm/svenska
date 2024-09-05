import re

with open('testText.html', 'r',encoding="utf-8") as in_file:
    content = in_file.readlines();

def getTimestampAndText(str):
    tmpArr = str.split(">")    
    tmp = tmpArr[0]
    start = tmp.find("data-start")
    
    if start != -1:        
        tmp = tmp[start:]
        tmp = tmp.replace('data-start="',"")
        tmp = tmp.replace('"',"")
        ts = tmp
        text = tmpArr[1]
        return ts,text
    else:
        return None,None

allLines = ""
for line in content:
    allLines += line
    
spanArr = allLines.split("</span>")
re = re.compile('data-start=')
cnt = 0
lastTs=""
for el in spanArr:
    if "data-start" in el and "style" not in el:               
        match = re.search(el)     
        if match:
            start = match.span(0)[0]
            if start:
                tmp = el[start:]
                ts,text = getTimestampAndText(tmp)
                if ts and text:
                    if ts != lastTs:
                        print(ts + ": " + text)
                        lastTs = ts
                cnt += 1

