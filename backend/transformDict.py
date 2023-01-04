import xml.etree.ElementTree as ET
import sys

tree = ET.parse("dict.xml")
root = tree.getroot()

with open("svsv.html", "w", encoding="utf-8") as outfile:
    outfile.write("""<?xml version="1.0" encoding="utf-8"?>
    <html xmlns:idx="www.mobipocket.com" xmlns:mbp="www.mobipocket.com" xmlns:xlink="http://www.w3.org/1999/xlink">
      <body>\n\t\t""")

    for lemma in root.iter('lemma-entry'):
        form = lemma.find('form')
        pronunciation = lemma.find('pronunciation')
        inflection = lemma.find('inflection')
        pos = lemma.find('pos')

        outfile.write("<idx:entry>")
        outfile.write("<idx:orth>")
        outfile.write(("<b>"+form.text.replace('~','')+"</b> "))

        if(inflection != None and inflection.text != None and len(inflection.text)!=0):
            infl = "<idx:infl>"
            if pos.text == "adv.":
                words = []
                words.append(inflection.text)                
            else:
                words = inflection.text.split(' ')

            for s in words:
                infl += "<idx:iform value=\""
                infl += s
                infl += "\" exact=\"yes\"/>"                           
            infl += "</idx:infl>"
            if len(infl) > 250:
                print("long infl: " + str(len(infl)) + ", " + form.text)
            
            outfile.write(infl)

        lexemes = lemma.findall('lexeme')
        makelist = len(lexemes)>1
        if(makelist): outfile.write("<ol>")
        lCnt = 0
        for lexeme in lexemes:

            lexnr = lexeme.find('lexnr')
            definition = lexeme.find('definition')
            maxLen = 118
            if len(definition.text) > maxLen:
                definition.text = definition.text[:maxLen]
                #print(len(definition.text))
            '''                                                     
            usage = lexeme.find('usage')
            comment = lexeme.find('comment')
            valency = lexeme.find('valency')
            grammat_comm = lexeme.find('grammat_comm')
            definition_comm = lexeme.find('definition_comm')
            examples = lexeme.findall('example')
            idioms = lexeme.findall('idiom')
            compounds = lexeme.findall('compound')
            '''
            if(makelist): outfile.write("<li>")
            if(definition != None):outfile.write(definition.text.replace("_br_","<br>"))
            if(makelist): outfile.write("</li>")
             
        if(makelist): outfile.write("</ol>")
        else: outfile.write("<br>")

        outfile.write("</idx:orth>")
        outfile.write("</idx:entry>")
        outfile.write("\n\t\t")
        
    outfile.write(""" </body>
    </html>""")

    outfile.write("\n")
    print("Last word is '" + form.text + "'")