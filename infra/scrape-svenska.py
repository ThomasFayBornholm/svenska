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

def extract_defs(raw):
    re_li = r"<li.*?</li>"
    li_matches = re.findall(re_li,raw)
    defs = list()
    for el in li_matches:
        text = BeautifulSoup(el,"html.parser").get_text()
        text = text.replace("VISA MER +","")
        text = text.replace("JFR","<br><b>JFR</b>")
        text = text.replace("SYN","<br><b>SYN</b>")
        text = text.replace("SE","<br><b>SE</b>")
        text = text.replace("MOTSATS","<br><b>MOTSATS</b>")
        text = text.replace("<br><br>","<br>")
        defs.append(text)
    return defs 


word = sys.argv[1]
url = "https://svenska.se/?activeTab=so&q=" + word.replace(" ","+")
html = fetch_page_content(url)
ts("HTML returned")
'''
Output a list of dictionaries corresponding to word class matches
Each dictionary contains 
class: e.g. "substantiv"
def: e.g. person som (yrkesmässigt) ägnar sig åt akrobatik JFR balanskonstnär, ekvilibrist, lindansare
meta: akrobaten akrobater
'''
out = list()
# ORDKLASS: </span><span data-v-93e4013d="" class="">substantiv</span>
re_word_class = r"ORDKLASS: </span><span.*?>(.*?)</span>"
tmp_classes = re.findall(re_word_class,html)
for el in tmp_classes:
    tmp = dict() 
    tmp["class"] = el
    out.append(tmp)
word_classes = html.split("ORDKLASS")
word_classes.pop() # Remove trailing text

ind = 0
for el in word_classes:
    re_meta = r'<span class="i">(.*?)</span>'
    meta_el = re.findall(re_meta,el)
    out[ind]["options"] = (meta_el)
    ind += 1

re_def = r"<ol data-v-93e4013d.*?</ol>"
def_raw = re.findall(re_def,html)
ind = 0
for el in def_raw:
    out[ind]["def"] = extract_defs(el)
    ind += 1

sys.stdout.write(
    json.dumps(out, ensure_ascii=False)
)
sys.stdout.flush()
