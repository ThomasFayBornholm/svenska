#!/srv/venv/scrape/bin/python
from playwright.sync_api import sync_playwright
from bs4 import BeautifulSoup
import re
import json
import sys
import html5lib
from datetime import datetime

DEBUG=False
def ts(input):
    if DEBUG:
        print(f"{str(datetime.now().time())[:-5]}: {input}")

def fetch_page_content(url: str) -> str:

    ts("Init")

    with sync_playwright() as p:
        # Launch a headless Chromium browser
        browser = p.chromium.launch(headless=True)
        context = browser.new_context(
        viewport={"width": 1024, "height": 760}
            )
        page = context.new_page()
        page.goto(url)
        page.wait_for_timeout(2000)
        #page.wait_for_load_state("networkidle")
        #html_content = page.content()
        html = page.locator("body").inner_html()
        start_pos = html.find("Publicerad")
        end_pos = html.find("Alfabetisk lista")
        # Reduce string 
        html = html[start_pos:end_pos]

        ts("HTML loaded")

        browser.close()
        ts("Browser close")
        return html

word = sys.argv[1]
url = "https://svenska.se/?activeTab=so&q=" + word.replace(" ","+")
html = fetch_page_content(url)
ts("HTML returned")
tmp_res = html.split("-right-1")
del tmp_res[0]
regex_class=r'ORDKLASS: <\/span><span data-v-662ffdb7="" class="">(.*?)<\/span><\/div>'
regex_opts=r'<span data-v-662ffdb7="" class="text-black">(.*?)<\/span><\/div>'
regex_opts_2 = r'<span class="i">(.*?)<\/span>'
regex_def=r'-->(<span data-v-662ffdb7="" class="(?:text-sm)*">.*?<\/span>)<!--'
regex_links = r'-->(<div data-v-662ffdb7="" class="">.*?<\/div>)'
#regex_links = r'-->(<div data-v-662ffdb7="" class=".*)'
regex_grammar=r'\(<span data-v-662ffdb7="" class="text-sm">\⟨(.*?)\⟩ </span>'

def fmt_txt(el):
    tmp = el
    
    for j in range(1,10):
        # Remove all span tasg
        # Resolve hyperlinks to bold highlighting
        tmp = re.sub(r'<a href=.*?>(.*?)<\/a>',r'<b>\1</b>',tmp)
        # Does this handle nested spans correctly
        tmp = re.sub(r'<span data-v-662ffdb7="".*?>(.*?)<\/span>',r"\1",tmp)
        tmp = re.sub(r'<div data-v-662ffdb7="".*?>(.*?)<\/div>',r"\1",tmp)
    for j in range(1,10):
        tmp = re.sub(r'<span data-v-662ffdb7="".*?>','',tmp)
        tmp = re.sub(r'<div data-v-662ffdb7="".*?>','',tmp)
        
    tmp = re.sub(r'<div.*?<\/li>',"",tmp)
    tmp = re.sub(r'^.*?<!---->','',tmp)
    tmp = re.sub(r'<!---->','',tmp)
    tmp = re.sub(r'<span class="b">(.*?)\) </span>',r'<b>\1</b>) ',tmp)
    tmp = re.sub(r'<sup>.*?<\/sup>','',tmp)
    tmp = re.sub(r'<button.*','',tmp)
    tmp = re.sub(r'JFR','<br>JFR',tmp)
    tmp = re.sub(r'SE','<br>SE',tmp)
    tmp = re.sub(r'MOTSATS','<br>MOTSATS',tmp)
    tmp = re.sub(r'SYN','<br>SYN',tmp)
    return tmp

def fmt_links(raw):
    out = raw
    out = re.sub(r'<span data-v-662ffdb7="" class="text-sm text-slate-600 font-semibold">(.*?)<\/span>',r"\1",out)
    out = re.sub(r"<a.*?>(.*?)<\/a>",r"<b>\1</b>",out)
    out = re.sub(r"<div.*?>","",out)
    out = re.sub(r"<span.*?>","",out)
    out = re.sub(r"</span>","",out)
    out = re.sub(r"</div>","",out)
    out = re.sub(r"<!---->","",out)
    return out

matches = list()
tmp = dict()
for el in tmp_res:
    ts("Loop")
    tmp["class"] = "" 
    tmp["options"] = []
    tmp["grammar"] = ""
    tmp["def"] = ""
    tmp["more"] = ""
    tmp["meta"] = word 
    el = el.split("VISA MER")[0]
    match_class = re.search(regex_class,el)
    if match_class:
        tmp["class"] = str(match_class.groups(1)[0])
    match_opts = re.search(regex_opts,el)
    res_def = re.findall(regex_def,html)
    if (not res_def):
        tmp["debug"] = el 
    
    if res_def:
        delim = ""
        if len(res_def) == 1:
            tmp["def"] = "● " + fmt_txt(res_def[0])
        else:
            i = 1
            for el in res_def:
                tmp["def"] += delim + str(i) + " " + fmt_txt(el)
                delim = "<br>"
                i += 1
    res_links = re.findall(regex_links,html)
    if res_links:
        if len(res_def) == 1:
            for el in res_links:
                tmp["def"] += "<br>" + fmt_links(el);
    if match_opts:
        raw = str(match_opts.group(1))
        match_opt_2 = re.findall(regex_opts_2,raw)
        soup = BeautifulSoup(raw,"html.parser")
        tmp_opts =  soup.get_text()
        tmp["meta"] += tmp_opts
        tmp["options"] = match_opt_2

    matches.append(tmp)

sys.stdout.write(
    json.dumps(matches, ensure_ascii=False)
)
sys.stdout.flush()
