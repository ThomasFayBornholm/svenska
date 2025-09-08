$(window).focus(function() {
	focusDef();		
});

function focusDef() {
	if ($('#iDefDiv').css("display") != "none") {
		$('#iDefText').get(0).focus();
	}
}

function focusInput() {
	if ($('#iDefDiv').css("display") === "none") {
		$('#iInput').focus();
	}
}

let N_TOT=0;
let N_ADJ=0;
let N_VERB=0;
let N_ADVERB=0;
let N_FRASER=0;
let N_EN=0;
let N_ETT=0;
let N_PLURAL=0;
let N_PREPOSITION = 0;
let N_INTERJEKTION = 0;
let N_PRONOMEN = 0;
let N_PREFIX = 0;
let N_SLUTLED = 0;
let N_NUMMER = 0;
let N_SUBJUNKTION = 0;
let N_INFINITIV = 0;
let N_CUR=0;

let N_LEMMAS = 0;
let N_DEFS = 0; // Number of definitions for current word lemma
let LAST_WORD=""; // Allow user to go back to previous word
let LAST_DEF_ENUM = 1; // The viewed definition is also remembered when recalling the previous word
let LAST_SCROLL_Y = 0;
let CUR_WORD="";
let CUR_CONJ="";
let CUR_DEF = "";
let CUR_DEF_ENUM = 1;
let N_DEF = 0;
let CLASS = "all";
var LAST_OBJ;
let DEBUG=false;
let LAST_DEF = "";
let LAST_CLASS = "all";
let CONTROLS_VISIBLE=true;
var TABS = [];
var CACHED_DEF_HTML = "";
var CLASSES = ["en","verb","adj","ett","adv","fra","plu","prep","pro","int","för","slut","sub","kon","räk","inf","art"]
var CLASS_LIST = ["substantiv_en","verb","adjektiv","substantiv_ett","adverb","fraser","plural","preposition","pronomen","interjektion","förled","slutled","subjunktion","konjunktion","r�kneord","infinitiv","artikel"]
// Crude toggle on site load to show tool tips
var TAB_DUMMY;
var TAB_LOOKUP
let ALL_DEFS = "";
GLOBAL = {
	cur_matches: [],
	cur_matches_count: 0,
	match_inc: 0,
}

function startup() {
	if (DEBUG) console.log("startup()")	
	// loop all of word class	
	const queryString = window.location.search;
	const urlParams = new URLSearchParams(queryString);
	if (urlParams.get('debug') === "1") DEBUG = true;
	wordCount();		
	setClass("substantiv_en");
	focusInput();
}

function clearSummary() {
    if (DEBUG) console.log("clearSummary");
    $('#iSummary').text("");
}

function getInput() {
	if (DEBUG) console.log("getInput()");
	let word = $('#iInput').val();		
	if (word === "~") word = CUR_WORD;
	$('#iInput').val(word);
	return word;
}

function setCUR_WORD(word) {
	if (word != CUR_WORD) {
		LAST_WORD = CUR_WORD;
		CUR_WORD = word;			
		//LAST_CLASS = CLASS;
	}	
	$('#iInput').val("");
}

function setN_DEF(n_def) {
	N_DEF = n_def;
}

function setN_CUR() {
	if (DEBUG) console.log("setN_CUR()")
	if (CLASS === "adjektiv") {
		N_CUR = N_ADJ;
	} else if (CLASS === "verb") {
		N_CUR = N_VERB;
	} else if (CLASS === "adverb") {
		N_CUR = N_ADVERB;
	} else if (CLASS === "substantiv_en") {
		N_CUR = N_EN;
	} else if (CLASS === "substantiv_ett") {
		N_CUR = N_ETT;
	} else if (CLASS === "fraser") {
		N_CUR = N_FRASER;
	} else if (CLASS === "preposition") {
		N_CUR = N_PREPOSITION;
	} else if (CLASS === "interjektion") {
		N_CUR = N_INTERJEKTION;
	} else if (CLASS === "pronomen") {
		N_CUR = N_PRONOMEN;
	} else if (CLASS === "förled") {
		N_CUR = N_PREFIX;
	} else if (CLASS === "plural") {
		N_CUR = N_PLURAL;
	} else if (CLASS === "slutled") {
		N_CUR = N_SLUTLED;
	} else if (CLASS === "räkneord") {
		N_CUR = N_NUMMER;
	} else if (CLASS === "konjunktion") {
		N_CUR = N_KONJUNKTION;
	} else if (CLASS === "subjunktion") {
		N_CUR = N_SUBJUNKTION;
	} else if (CLASS === "infinitiv") {
		N_CUR = N_INFINITIV;
	} else if (CLASS === "artikel") {
		N_CUR = N_ARTIKEL;
	} else if (CLASS === "all") {
		N_CUR = N_ALL;
	} else {
		N_CUR = N_ALL;
	}
	
	if (CLASS === "all") {
		$('#iCnt').text("");		
	} else {
		$('#iCnt').text(N_CUR.toLocaleString('sv'));		
	}	
	
	if (DEBUG) console.log("setN_CUR(); N_CUR = " + N_CUR);
}

function fmtDef(def) {
	if (DEBUG) console.log("fmtDef()")
	for (i = 0; i < 24; i++) {
		let old = " " + i + " ";
		let rep = "<br>" + i + " ";
		def = def.replace(old,rep)
	}
	return def;
}

function putMoreMany(moreArr, cur) {
	if (DEBUG) console.log("putMoreMany()");
	let moreLen = moreArr.length;
	// Don't forget no spaces in storage keys, use "-" instead
	fetch('backend/putMore.php?key=' + CUR_WORD.replaceAll(" ","-") + "_" + cur + '&more=' + moreArr[cur] + '&class=' + CLASS, {
		method: 'get',
		mode: 'cors',
		headers: {
			'Content-Type': 'application/json'
		}
	})
	.then(response => {
		cur++;
		if (cur < moreLen) putMoreMany(moreArr, cur);
	})
	.catch(error => {
		log(error);
	})
}

function addDef(key, meta, def, more, word_class, fast = false) {
	if (DEBUG) console.log("addDef(meta,def,more)");
	$('#iMeta').html("adding... '<b>" + key + "</b>'");
	$('#iParDef').html("");
	$('#iMore').html("");
	let moreArr = more.split("||");	
	moreLen = moreArr.length;
	if (moreLen > 4) {
		more = "";
		putMoreMany(moreArr, 0);
	}
	const MAX_URL = 2000;
	var tmpMore = more;
	if (def.length + more.length > MAX_URL) {
		more = "";
	}
	let url = 'backend/addDef.php?word=' + key.replace("/-[1-9]","") + '&meta=' + meta + '&def=' + def + '&more=' + more + '&class=' + word_class; 
	fetch(url, {
		method: 'get',
		mode: 'cors',
		headers: {
			'Content-Type': 'application/json'
		}
	})
	.then(response => {
		return response.json();
	})
	.then(json => {
		if (CLASS === "fraser") $('#iInput').val(key);
		CUR_DEF_ENUM = 1;
	})
	.catch(error => {
		console.log(error);
	})
	if (more.length === 0) {
		url = 'backend/addDef.php?word=' + key.replace("/-[1-9]","") + '&meta=&def=&more=' + tmpMore + '&class=' + CLASS; 
		fetch(url, {
			method: 'get',
			mode: 'cors',
			headers: {
				'Content-Type': 'application/json'
			}
		})
		.then(response => {
			return response.json();
		})
		.then(json => {
			console.log(json);
			$('#iMeta').html("addded '<b>" + key + "</b>'");
		})
		.catch(error => {
			console.log(error);
		})
	}
}


function putDef(def) {
	if (def.length === 0) {
		def = $('#iDefText').val();
	}
	if (DEBUG) console.log("FUNC putDef(def)")
	if (DEBUG) console.log("  PARAM def = " + def);

	if (CLASS === "fraser") {
		putFrasDef();
		return;
	}
	
	if (def.slice(0,1) === "\n") def = def.slice(1);
	
	
	let processed = preProc(def);
	def = processed[0];	
	meta = processed[1];
	more = processed[2];
	fetch('backend/putDef.php?class=' + CLASS + '&word=' + CUR_WORD + '&def=' + def + '&meta=' + meta + "&more=" + more, {
		method: 'get',
		mode:	'cors',
		headers: {
			'Content-Type': 'application/json'
		}
	})
	.then(response => {
		$('#iDefText').val("");
		$('#iDefDiv').css("display", "none");
		// Reset highlighting of missing definition
		LAST_OBJ.innerHTML = LAST_OBJ.innerText;		
		wordCount();
		displaySelection();
		focusInput()
	})
	getDiff();
}

function putFrasDef() {
	if (DEBUG) console.log("FUNC putFrasDef()")
	let def = $('#iDefText').val();
	if (def.indexOf("%%") == -1) {
		return;
	}
	let tmp = def.split("%% ");
	CUR_WORD = tmp[0];
	let meta = tmp[0] + "<br>fras";	
	
	let processed = preProc(tmp[1]);	
	def = processed[0];	
	def = def.replace("2", "<br>2");
	def = def.replace("3","<br>3");
	
	fetch('backend/putDef.php?class=fraser&word=' + CUR_WORD + '&def=' + def + '&meta=' + meta, {
		method: 'get',
		mode:	'cors',
		headers: {
			'Content-Type': 'application/json'
		}
	})
	fetch('backend/addWord.php?word=' + CUR_WORD + '&class=fraser', {
		method: 'get',
		mode:	'cors',
		headers: {
			'Content-Type': 'application/json'
		}	
	})
	$('#iDefText').val("");
	wordCount();
}

function displayMeta(meta) {
	if (DEBUG) console.log("FUNC displayMeta(meta)");
	if (meta.length > 0) $('#iMeta').html(fmtMeta(meta));
	if (CLASS === "fraser") $('#iMeta').html(fmtMeta(CUR_WORD));
}

function displayDef(def) {	
	if (DEBUG) console.log("displayDef(def)");	
	if (DEBUG) console.log("  PARAM def = " + def);
	def = makeLinks(def);
	let defRaw = def.split("<br>");	
	let defLines = [];
	let iWrite = 0;
	for (i = 0; i < defRaw.length; i++) {
		// Formatted
		if (defRaw[i].match(/^[1-9\u25CF]+[0-9]?.*/)) {			
			defLines[iWrite] = defRaw[i];
			iWrite++;			
		} else {			
			for (k = 1; k < 24; k++) {
				defRaw[i] = defRaw[i].replaceAll(k,"")				
			}
			defRaw[i] = defRaw[i].replaceAll("�</b>","</b>")
			if (defLines.length === 0) {
				defLines[0] = defRaw[i];
				iWrite++;
			} else {				
				defLines[iWrite-1] += "<br>" + defRaw[i];
			}
		}
	}	
	let outDef = "";
	let delim = "";	
	setN_DEF(defLines.length);
	for (j = 0; j < defLines.length; j++) {
		let key = CUR_WORD.replaceAll(" ","-") + "_" + j;		
		if (defLines[j].indexOf("<i>") === -1) {
			outDef += delim + defLines[j]						
		} else {
			outDef += delim + defLines[j];
		}
		delim = "<br>";
	}
	outDef = outDef.replaceAll(" </b>","</b>")
	// Dynamise links
	outDef = outDef.replaceAll("<l>","<span onclick=followLink(this.innerText) onmouseover=highlight(this,event) title='Click to follow'><b>");
	outDef = outDef.replaceAll("</l>","</b></span>");
	let tmpSpan = "<span id='iMeta' "

	if (CLASS != "all" && CLASS != "fraser") outDef = outDef.replace(CUR_DEF_ENUM + " ", CUR_DEF_ENUM + "/" + N_DEFS + " ");	
	if (CLASS === "all") outDef = "<b>" + CUR_WORD + "</b><br><br>" + outDef;
	$('#iParDef').html(outDef);
	if (CLASS === "fraser") $('#iInput').attr("placeholder", "idiomatisk");
}

// Make links to referenced words
function makeLinks(def) {
	if (DEBUG) console.log("makeLinks()")
	def = linkSingleWord(def);	
	if (def.split("<br>").length == 1) {
		return def;
	}
	
	def = addLinks("MOTSATS", def);
	def = addLinks("JFR", def);
	def = addLinks("SYN.", def);
	def = addLinks("SE", def);		
	return def;
}

function linkSingleWord(def) {	

	if (DEBUG) console.log("linkSingleWord(def), def = " + def);
	defArr = def.split("<br>");
	outDef = "";
	del = "";
	tmpSpan = "<l>_w_</l>";	
	for (line of defArr) {		
		if (line.length > 0) {
			// Exclude explicitly predefined links and word class links e.g. "verb:"
			if (line[0].match(/[A-Z]/) != null || line.indexOf(":") != -1) {
				outDef += del + line;
				del = "<br>";
			} else {
				let tmp = line;
				tmp = tmp.substr(2);			
				outDef += del + line;
				del = "<br>";
			}
		}
	}
	return outDef;
}

function addLinks(refClass,def) {	
	if (DEBUG) console.log("addLinks(), refClass = " + refClass + ", def = " + def);
	if (def.indexOf(refClass) === -1) return def;
	// Easiest to handle line by line and skip the lines without links
	// Expectation is each link class (JFR, SE etc.) has its own new line.
	defArr = def.split("<br>");
	def = "";
	m = refClass + " ";
	delim = "";
	beginSpan = "<span onclick=followLink(this.innerText) title='Click to follow'";
	beginSpan += " onmouseover=highlight(this,event)";
	beginSpan += ">"
	spanTmp = beginSpan + "<b>_w_</b></span>";
	for (line of defArr) {		
		if (line.indexOf(m) != -1) {
			elArr = line.split(", ");
			for (el of elArr) {
				el = el.replaceAll("�","");
				if (el.includes(m)) {
					let rep = m + spanTmp.replaceAll("_w_",el.substr(m.length, el.length - m.length))
					line = line.replace(el, rep);
				} else {
					let rep = spanTmp.replaceAll("_w_", el);
					line = line.replace(", " + el, ", " + rep);
				}
			}
		}
		def += delim + line;
		delim = "<br>";
	}
	return def;
}

function followLink(word) {
    if (DEBUG) console.log("followLink(" + word +  ")");
	LAST_SCROLL_Y = window.pageYOffset;	
	LAST_CLASS = CLASS;	
	LAST_DEF_ENUM = CUR_DEF_ENUM;	
    // Remove any soft hyphens in word
    word = word.replaceAll("\u00ad","");
	let rep = word.match(/\s/);
	if (rep != null) {
		word = word.replaceAll(rep[0], " ");
	}
    for (i = 0; i < 24; i++) {
		word = word.replace(i,"");
	}
	setCUR_WORD(word);
	wordFromAll(word);		
}

function defFromAll(word) {
	if (DEBUG) console.log("defFromAll(" + word + ")");
	return new Promise((resolve) => {	
		var out = [];
		out["match"] = "";
		out["class"] = "all";		
		fetch('backend/getDefAll.php?word=' + word, {
			method: 'get',
			mode: 'cors',
			headers: {
				'Content-Type': 'application/json'
			}
		})
		.then(response => {
			return response.json();
		})
		.then(json => {
			if (Object.keys(json).length > 0) {
				if (Object.keys(json).length === 1)	out["class"] = Object.keys(json)[0];
				out["match"] = word;
			}			
			resolve(out);
		});
	});
}

/* Return as soon as first match is Found
 Order is
 1: 'All'
 2: 'Conjugate (any class)'
 3: 'Fuzzy Fras' match
*/
function wordFromAll(word) {
	defFromAll(word)
	.then((result) => {
		if (result["match"].length > 0) {		
			setClass(result['class'], result['match']);
			return;
		} else {
			wordFromAll_getConj(result,word)
			.then((result2) => {
				if (result2["match"].length > 0) {
					setClass(result2['class'], result2['match']);
					return;
				} else {
					wordFromAll_FrasFuzzy(result2, word)
					.then((result3) => {
						if (result3["match"].length > 0) {
							setClass(result3['class'], result3['match']);
						} else {
							noMatch(word);
						}
					})
					.catch(error => {
						console.log(error);
					})
				}
			})
		}
	})
}					

function wordFromAll_getConj(inp, word) {
	if (DEBUG) console.log("wordFromAll_getConj");
	return new Promise((resolve) => {	
		var out = [];
		out['class'] = "all";
		if (inp["match"].length > 0) {
			// No further work required
			// Simply pass through input results			
			out['match'] = inp['match'];
			out['class'] = inp['class'];
			resolve(out);
		} else {	
			fetch('backend/getConj.php?word=' + word + '&class=all', {
				method: 'get',
				mode: 'cors',
				headers: {
					'Content-Type': 'application/json'
				}
			})
			.then(response => {
				return response.json();
			})
			.then(json => {
				
				out['match'] = json["word"];	
				if (json["word"].length > 1) {
					out['class'] = json["class"];	
				}
				resolve(out)
			})
		}
	});
}

function wordFromAll_FrasFuzzy(inp,word) {	
	if (DEBUG) console.log("wordFromAll_FrasFuzzy");
	return new Promise((resolve) => {	
		var out = [];
		out['class'] = "all";
		if (inp['match'].length > 0) {
			// No further work required
			// Simply pass through input results
			out['match'] = inp['match'];
			out['class'] = inp['class'];			
			resolve(out);
		} else {
			fetch('backend/fuzzyFrasMatch.php?key=' + word, {
				method: 'get',
				mode: 'cors',
				headers: {
					'Content-Type': 'application/json'
				}
			})
			.then(response => {
				return response.json();
			})
			.then(json => {				
				if (json['match'].length > 0) { 					
					out['match'] = json["match"];
					out['class'] = "fraser";
				} else {
					out['match'] = "";
				}
				resolve(out)
			})			
		}
	});
}

function getCompDef(meta, def) {
	if (DEBUG) console.log("getCompDef(def); def = " + def);
	// Compose and output string that combines definition of multiple (usually two)
	// component words
	words = getRefs(def);
	let complete = false;
	let res = "";
	let out = [];
	let done = 0;
	for (let i = 0; i < words.length; i++) {
		let w = words[i];
		w = w.replace("-","");
		// Dispatch n fetches to get definitions
		// add an identifier to each result to enable correct ordering 
		fetch('backend/getDef.php?class=' + guessClass(w) + '&word='+ removeClassDesc(w), {
			method: 'get',
			mode: 'cors',
			headers: {
				'Content-Type': 'application/json'
			}
		})
		.then(response => {
			return response.json();
		})
		.then(json => {
			// def - the original reference definition
			// tmp - original ref + link to the referenced word
			// json - the referenced definition
			let tmp = "<span onclick=\"followLink(this.innerText)\"><b>_w_</b></span>:";
			tmp = tmp.replaceAll("_w_",removeClassDesc(w));
			tmp = tmp + "<br>";
			out[done] = makeLinks(tmp + json["def"]);
			done++;
			if (done >= words.length) {
				// Perform any required ordering
				let txt = def + "<br>";
				let del = "";
				for (let i = 0; i < done; i++) {
					if (i != 0) del = "<br>"
					txt = txt + del + out[i];
				}
				$('#iMeta').html(fmtMeta(meta));
				$('#iParDef').html(txt);
				
			}
		})
		.catch(error => {
			log(error);
		})
	}
}
function getRefs(def) {
	if (!def.includes("till ") || !def.includes("'")) {
		return "";
	}
	def = def.slice(2);
	def = def.replaceAll("till ", "");
	def = def.replaceAll("'","");
	arr = def.split(",");
	if (DEBUG) console.log("getRef(), arr = " + arr);
	return arr;
}

function onlineLookup(elHTML, ev) {	
	if (DEBUG) console.log("onlineLookup(elHTML, ev)");
	ev.preventDefault();
	word = elHTML.value;
	if (word.length === 0) word = CUR_WORD;
	if (word.length > 0) {
		fetch('backend/showExtern.php?word=' + word, {
			'method': 'get',
			'mode': 'cors',
			'headers': {
				'Content-Type': 'application/json'
			}
		})
		.then(response => {
			return response.json();
		})
		.then(json => {
			let tmp = "";
			let delim = "";
			for (const el of json) {
				delim = "<br>";
				if (el.includes("; id=")) {
					let start = el.indexOf("; id=")+5;
					let tmpID = el.substr(start);
					tmp += delim + '<a href="http://svenska.se/so/?id=' + tmpID + '"';
					tmp += ' oncontextmenu=ScrapeByID(event,"' + tmpID + '")>' + el.replace("; id=" + tmpID,"") + "</a>";
				} else {
					tmp += delim + "<a href='http://svenska.se/so?sok=" + encodeURI(word) + "'>" + el + "</a>";
				}
			}
			$('#iExtern').html(tmp);
		})
		.catch(error => {
			console.log(error)
		})
	}
}

function switchTab() {	
	TAB_DUMMY.close()
	TAB_LOOKUP.focus();		
}

function wordCount() {
	// Hash to prevent cached result being returned.
	fetch('backend/wordCount.php?hash=' + makeHash(), {
		method: 'get',
		mode: 'cors',
		headers: {
			'Content-Type': 'application/json'
		},
	})
	.then(response => {
		return response.json();
	})
	.then(json => {
		N_TOT = json["total"];
		N_ALL = json["all"];		
		N_ADJ = json["adj"];
		N_VERB = json["verb"];
		N_ADVERB = json["adverb"];
		N_FRASER = json["fraser"];
		N_EN = json["substantiv_en"];
		N_ETT = json["substantiv_ett"];		
		N_PLURAL = json["plural"];
		N_PREPOSITION = json["preposition"];
		N_INTERJEKTION = json["interjektion"];
		N_PRONOMEN = json["pronomen"];
		N_PREFIX = json["prefix"];
		N_SLUTLED = json["slutled"];
		N_NUMMER = json["nummer"];
		N_KONJUNKTION = json["konjunktion"];
		N_SUBJUNKTION = json["subjunktion"];
		N_INFINITIV = json["infinitiv"];
		N_ARTIKEL = json["artikel"];
		setN_CUR();		
		$('#iInput').attr("placeholder",Math.round(N_ALL/1000,0) + "k searchable words");
		})
	.catch(error => {
		log(error);
	})
}

function showToggle() {
	if (CONTROLS_VISIBLE) {
		// Hide controls 
		$('#iControls').css("display", "none");
		$('#iShowToggle').text("__");
		CONTROLS_VISIBLE=false;
	} else {
		// Make visible class selector and word seeker controls
		$('#iControls').css("display", "block");
		$('#iShowToggle').text("HIDE");
		focusInput()
		CONTROLS_VISIBLE=true;
	}
	if (DEBUG) console.log("showToggle(); seek.v = " + $('#iInput').css("visibility") + ", sel.v = " + $('#iSelect').css("visibility"));
}

function newInputWrapper(e) {
	if (DEBUG) console.log("newInputWrapper()");
	$('#iExtern').empty();
	e.preventDefault();
	let word = getInput();
	if (word.length === 0) {
		if (typeof($('#iMore').html()) === "undefined" || ! $('#iMore').html()) {
			getMore(CUR_DEF_ENUM);
		} else {
			$('#iMoreText').empty();
			$('#iInput').attr("placeholder","Press ENTER to show more");
		}
		return;
	} else {
		$('#iInput').removeAttr('placeholder');
	}
	// Navigation only to a given word class.
	if (word.indexOf("@") === 0) {
		let newClass  = fullClassName(word.substr(1));
		if (newClass != "") {
			setClass(newClass);		
            clearInput();
            return;
		}
	// Navigate to word class + query
	} else if (word.indexOf("@") != -1) {
		// Add word
		let tmp  = word.split("@");
		let tmp2 = tmp[1].split(",");
		let newClass = tmp2[0];
		word = tmp[0]
		if (tmp[1].indexOf(",") != -1) word += ",";
		//setClass(fullClassName(newClass));
		setClass(fullClassName(newClass),word);
		
		//$('#iInput').val(word);
		//newInputWrapper(e);		
		return;
	} else if (word.indexOf(",") === word.length-1) {	
		// Trim query identifier
		word = word.substr(0,word.length-1)		
		$('#iInput').val(word);
		addWord();
		seekWordSingleClass(word);
		return;
	}
	seekWord(word);
}

function fmtUnit(val, unit) {
	if (DEBUG) console.log("fmtUnit()");
	let tmp = " (" + val + " " + unit + ")";
	return tmp;
}

function clearInput() {
    if (DEBUG) console.log("clearInput()");
    $('#iInput').val(""); 
}


/* Search concept
 1) Exact match any class
 2) Fuzzy match on idiom/fras.
 In parallel match any conjugation of the word in any class.
 */
// Link is set if this seek is called from a clickable link

// Called by user, prefer current class but allow for secondary matching in other word classes
// There is a recursion here somewhere, find it

async function seekWord(word, link = false, lastEnum = false) {
	if (DEBUG) console.log ("seekWord()");
	$('#iInput').val("");
	CUR_DEF_ENUM = 1; // Always show first defintion for a word the user has searched for
	CUR_CONJ = "";
	// For backwards navigation
	
	LAST_CLASS = CLASS;
	LAST_WORD = CUR_WORD;
	// Cleanup 
	$('#iAux').empty();
	$('#iMoreText').empty();
	$('#iInput').attr("placeholder","Press ENTER to show more");
	// Strip trailing white space	
	while (word.substr(word.length-1) === " ") {
		word = word.substr(0,word.length-1)
	}
	const res = await fetch('backend/getDefAll.php?word=' + word, {
		method: 'get',
		mode: 'cors',
		headers: {
			'Content-Type': 'application/json'
		}
	})
	let json = await res.json();
	if (json.length > 0) CUR_WORD = word;
	const res_conj = await fetch('backend/getConj.php?word=' + word, {
		method: 'get',
		mode: 'cors',
		headers: {
			'Content-Type': 'application/json'
		}
	})
	let json_conj = await res_conj.json();
	let conj_keys = Object.keys(json_conj);
	for(const k of conj_keys) {
		json[k] = json_conj[k];
		CUR_WORD = json[k]["word"];
		CUR_CONJ = word;
	}
	let matches = Object.keys(json);
	if (matches.length > 0) {
		GLOBAL.cur_matches = json;
		GLOBAL.cur_matches_count = matches.length;
		GLOBAL.match_inc = 0;
		let tmp_class = matches[0];
		setClass(tmp_class);
		CUR_DEF = GLOBAL.cur_matches[CLASS]["def"];
		N_DEFS = nDefs();
		let def = getDefInd(CUR_DEF_ENUM);
		if (def.length != 0) displayDef(def);
		let meta = json[CLASS]["meta"];
		if (meta.length != 0) displayMeta(meta);
	}
	if (matches.length > 1) {
		$('#iInput').attr("placeholder",matches.length + " matches, navigate with PageUp/Down");
	}
	focusInput();
}

async function getWordConj(word) {
	if (DEBUG) console.log("getWordConj");
	return new Promise((resolve) => {	
		if (CLASS === "fraser") {			
			resolve(word);
			return;
		}
		fetch('backend/getConj.php?word=' + word, {
			method: 'get',
			mode: 'cors',
			headers: {
				'Content-Type': 'application/json'
			}
		})
		.then(response => {
			return response.json()
		})
		.then(json => {
			resolve(json["word"]);
		})
	})
}

function noMatch(word) {
	$('#iParDef').html("No match in any word class: <b>" + word + "</b>");	
	$('#iMeta').empty();
	$('#iInput').val(word);
}

function setClass(c, showMore = false) {
    if (DEBUG) console.log("setClass(" + c + ", seek = " + seek + ")");
	LAST_CLASS = CLASS;	
	if (CLASS != "fraser") LAST_SCROLL_Y = window.pageYOffset;	
	
	if (DEBUG) console.log("setClass(c, seek), c = " + c + ", seek = " + seek);
	$('#iAux').html("");	
	$('#iMeta').html("");
	//wordCount(); // Think it is better to do word count only when there is an addition to the listing. This line was a previous hack.
	if (c != CLASS) {		
		$('#iDefDiv').css("display", "none");
		$('#iMore').empty();
		if (c != "all") {
			$('#iDefProgContainer').css("visibility","visible");
		} else {
			$('#iDefProgContainer').css("visibility","hidden");
		}
		CLASS = c;
		setN_CUR();	
	}
}

function getLastWord() {
	if (DEBUG) console.log("getLastWord()");			
	if (LAST_WORD.length > 0) {		
		let tmp = CLASS;
		let showMore = true
		seekWord(LAST_WORD);		
		LAST_CLASS = tmp;
	}
	setTimeout(setScroll, 500);
}

function setScroll() {	
	window.scrollTo(0,LAST_SCROLL_Y);
}

function countDefs() {
	N_LEMMAS = 0;
	// No sense in associating definitions with the 'all' word listing
	if (CLASS != "all") {
		fetch('backend/countDef.php?class=' + CLASS, {
			method: 'get',
			mode: 'cors',
			headers: {
				'Content-Type': 'application/json'
			}
		})
		.then(response => {
			return response.json();
		})
		.then(json => {
			if (json != "Failed to read definition file.") {	
				N_LEMMAS=json["cnt"];
				if (DEBUG) console.log("countDefs(); N_LEMMAS = " + N_LEMMAS);
				$('#iDefProg').css("width", N_LEMMAS/N_CUR * 100 + "%");
			} else {
				if (DEBUG) console.log("countDefs(); N_LEMMAS = " + N_LEMMAS);
			}
		})
	}
}

function clearDef() {
	if (DEBUG) console.log("clearDef()");
	$('#iParDef').html("");
	$('#iDefDiv').css("display", "none");
	for (let i = 0; i< N_FOUND; i++) {
		$('#w' + i).css("fontWeight", "");
	}
}

function pretty(def) {
	if (DEBUG) console.log("pretty(def); def = " + def);
	let out = "";
	subdefs = def.split("<br>");
	for (let i = 0; i < subdefs.length; i++) {
		del = "<br>";
		if (i === 0) del="";
		s = subdefs[i];		
		pJFR = s.indexOf("JFR");
		pSE = s.indexOf("SE");
		pMOTS = s.indexOf("MOTSATS");
		pSYN = s.indexOf("SYN");
		let tot = pJFR + pSE + pMOTS + pSYN;
		if (tot === -4) {
			out = out + del + s;
		} else {
			// New line before the JFR, SE, MOTS indicators for styling
			if (pJFR === -1) pJFR = 999999;
			if (pSE === -1) pSE = 999999;
			if (pMOTS === -1) pMOTS = 999999;
			if (pSYN === -1) pSYN = 999999;
			let start = pSE;
			if (pJFR < start) start = pJFR;
			if (pMOTS < start) start = pMOTS;
			if (pSYN < start) start = pSYN;
			let def2 =  s.slice(start,s.length);
			s = s.slice(0,start) + "<br>"
			def2 = def2.replaceAll("1","").replaceAll("2","").replaceAll("3","").replaceAll("4","");
			def2 = def2.replaceAll(" ,",",").replaceAll("  ", " ");
			def2 = def2.replaceAll(" 1<br>","<br>");
			out = out + del + s + def2;
		}
	}
	out = out.replaceAll("<br><br>","<br>");
	for (let i = 1; i < 24; i++) {
		out = out.replaceAll(i + " " + i, i);
		out = out.replaceAll("<br>" + i + "<br>","<br>" );
		out = out.replaceAll("<br> " + i + "<br>","<br>" );
		out = out.replaceAll("<br>" + i + " <br>","<br>" );
	}
	// Strip any leading new line
	if (out.slice(0,3) === "<br>") {
		out = out.slice(4,out.length) 
	}
	const regex1 = /[a-z][1-9]/;
	match = out.match(regex1);
	if (match != null) {
		strMatch = match[0];
		rep = strMatch[0] + "<br> " + strMatch[1];
		out = out.replace(strMatch, rep);
	}
	if (out[0] === " ") out = out.slice(1,out.length);
	out = out.replace("partikelntill", "partikeln till;");
	if (out[out.length-1] === ",") out = out.slice(0,out.length-1)
	return out;
}

function fullClassName(name) {
	if (name === "a") return "all"
	if (name === "en") return "substantiv_en"
	if (name === "ett") return "substantiv_ett"
	if (name === "adj") return "adjektiv"
	if (name === "adv") return "adverb"
	if (name === "fra") return "fraser"
	if (name === "sub") return "subjunktion"	
	if (name === "plu") return "plural"
	if (name === "prep") return "preposition"
	if (name === "pro") return "pronomen"
	if (name === "for") return "förled"
	if (name === "för") return "förled"
	if (name === "slut") return "slutled"
	if (name === "slut") return "slutled"
	if (name === "rak") return "räkneord"
	if (name === "räk") return "räkneord"
	if (name === "kon") return "konjunktion"
	if (name === "int") return "interjektion"
	if (name === "inf") return "infinitiv"
	if (name === "art") return "artikel"
	return name
}

function guessClass(word) {
	// Prefer explicit definition of class
	if (word.includes('(') && word.includes(')')) {
		// grab class	
		let start = word.indexOf('(')
		let end = word.indexOf(')')	
		if (start > end || start === -1) return 
		let c = word.slice(start+1,end);
		c = fullClassName(c);
		return c;
	} 
	// Otherwise try and semi-intelligently guess word class
	if (word.endsWith('a')) return "verb"
	if (word.endsWith('as')) return "verb"
	if (word.includes(' ')) return "verb"
	if (word.endsWith("ig")) return "adjektiv"
	if (word.endsWith("lös")) return "adjektiv"
	if (word.endsWith("sam")) return "adjektiv"
	if (word.endsWith("full")) return "adjektiv"
	if (word.endsWith("ell")) return "adjektiv"
	if (word.endsWith("isk")) return "adjektiv"
	if (word.endsWith("ad")) return "adjektiv"
	if (word.endsWith("en")) return "adjektiv"
	if (word.endsWith("p")) return "substantiv_ett"
	if (word.endsWith("ism")) return "substantiv_en"
	return "substantiv_en"
}

function removeClassDesc(word) {
	let start = word.indexOf('(');
	let end = word.indexOf(')');
	if (start > end || start === -1) return word 	
	return word.slice(0,start-1)
			
}

function removeWord() {
	if (DEBUG) console.log("removeWord()");
	addWord(true);
}

function addWord(remove = false) {	
	if (DEBUG) console.log("addWord()");	
	
	let word = $('#iInput').val();		
	let del = (word[word.length-1] === "." && word.indexOf("..") === -1) || remove;
	if (CLASS === "fraser" && !del) {
		return;
	}
	
	if (word[0] === '.') word = word.slice(1,word.length);
	word = word.replaceAll("/","\/");
	// Don't want characters in the storage key that the user would not enter
	word = word.replaceAll("�","");
	if (word[word.length-1] === '.' && del) word = word.slice(0,word.length-1);
	if (word.length > 0) {
		let op = "add"	
		if (del) op = "remove"
		fetch('backend/' + op + 'Word.php?class=' + CLASS + "&word=" + word, {
			method: 'get',
			mode: 'cors',
			headers: {
				'Content-Type': 'application/json'
			}
		})
		.then(response => {
			if (op === "remove") {
				// If this was the last listing of this word delete from "all" listing also
				fetch('backend/getDefAll.php?class=all&word=' + word, {
					method: 'get',
					mode: 'cors',
					headers: {
						'Content-Type': 'application/json'
					}
				})
				.then(response => {
					return response.json();
				})
				.then(json => {
					// It was last reference
					if (json.length < 1) {
						fetch('backend/removeWord.php?class=all&word=' + word, {
							method: 'get',
							mode: 'cors',
							headers: {
								'Content-Type': 'application/json'
							}
						})
						.catch(error => {
							log(error);
						})
						getWords();
						wordCount();
						
					}
				})
				.catch(error => {
					log(error);
				})
			// Navigate to newly added word for convenience.
			} else if (op === "add") {
				// Undesirable to have '�' character in word keys as user not expected to specifiy these. 
				word = word.replace("�","");
			}
		})
		.catch(error => {
			log(error);	
		})
	}	
}

function addWordWithEnum() {
	if (DEBUG) console.log("addWordWithEnum()");
	// Get enum index
	let word = $('#iInput').val();		
	if (word.length === 1) return;
	CUR_WORD = word.replace("#","");	
	fetch('backend/getIndEnum.php?word=' + CUR_WORD + '&class=' + CLASS, {
		nethod: 'get',
		mode: 'cors',
		headers: {
			'Content-Type': 'application/json'
		}	
	})
	.then(response => {
		return response.json();
	})
	.then(json => {
		let indEnum = json;
		CUR_WORD = CUR_WORD + "  " + indEnum;
		fetch('backend/addWord.php?class=' + CLASS + "&word=" + CUR_WORD, {
			method: 'get',
			mode: 'cors',
			headers: {
				'Content-Type': 'application/json'
			}
		})
		setTimeout(seekWordSingleClass, 500);
	})
	.catch(error => {
		log(error);
	})	
}

function addMeta(def) {
	// If a known meta phrase is identified at start of definition line then enclose it in square bracket formatting
	if (DEBUG) console.log("addMeta()");	
	regexArr = [
		/var�dagligt, skämtsamt/,
		/ålderdomligt el. ironiskt/,
		/sjö�fart/,
		/något ålderdomligt el. dialektalt/,
		/hög�tidligt el. skämtsamt/,
		/handels�namn/,
		/något �lderdomligt], ur�sprungligen bibliskt/,
		/sär�skilt i juridiska samman�hang/,
		/starkt ned�sättande; något ålderdomligt/,
		/ned�sättande, något ålderdomligt/,
		/något hög�tidligt; något ålderdomligt/,
		/var�dagligt el. i barn�språk/,
		/nästan en�bart i fack�språk/,
		/skämtsamt/,
		/dialektalt; något ned�sättande/,
		/kemi/,
		/var�dagligt, dialektalt/,
		/ekonomi, juridik/,
		/endast vid beskrivning av äldre förhållanden/,
		/ofta i natur�namn/,
		/mest i äldre tid/,
		/ofta i offentligt språk/,
		/ofta i politiska samman�hang/,
		/ej i fack�m�ssiga samman�hang/,
		/var�dagligt, kan upp�fattas som ned�sättande/,
		/var�dagligt, n�got �lderdomligt; kan upp�fattas som ned�sättande/,
		/var�dagligt; n�got �lderdomligt/,
		/ofta något hög�tidligt/,
		/något hög�tidligt el. �lderdomligt/,
		/något hög�tidligt; mindre brukligt/,
		/ofta i poetiska samman�hang/,
		/spr�k�vetenskap/,
		/sär�skilt i aff�rs�m�ssiga och vetenskapliga samman�hang/,
		/samman�fattande, formell beteckning/,
		/nu�mera ej officiell ben�mning/,
		/ofta i v�rderande ut�tryck/,
		/ofta statistik/,
		/ofta ironiskt/,
		/mest i vetenskapliga samman�hang/,
		/nu�mera s�llan i fack�m�ssiga samman�hang/,
		/nu�mera mindre brukligt i fack�m�ssiga samman�hang/,
		/i fackspr�kliga samman�hang/,
		/nu�mera mindre brukligt/,
		/ofta i religi�sa samman�hang/,
		/sär�skilt bibliskt; n�got �lderdomligt ut�om i vissa ut�tryck/,
		/sär�skilt geometri/,
		/ofta i bild�konst/,
		/mest som marxistisk term/,
		/s�r�skilt medicin, psykologi/,
		/s�r�skilt medicin/,
		/s�r�skilt i handels�spr�k/,
		/medicin/,
		/n�got h�g�tidligt ut\u00ADom i sammans�ttn./,
		/h�g�tidligt; mest bibliskt/,
		/n�got h�g�tidligt el. formellt/,
		/n�got h�g�tidligt el. sk�mtsamt/,
		/ibland n�got h�g�tidligt/,
		/n�got h�g�tidligt/,
		/h�g�tidligt/,			
		/n�got var�dagligt/,
		/var�dagligt; dialektalt/,
		/var�dagligt; n�got ned�sättande/,
		/var�dagligt; ned�sättande/,
		/ibland n�got ned�sättande/,
		/n�got ned�sättande/,
		/ned�sättande; �lderdomligt/,
		/var�dagligt; vanligen n�got ned�sättande/,
		/var�dagligt; ned\u00ADs�ttande/,
		/var�dagligt, ibland n�got ned�sättande/,
		/var�dagligt; sär�skilt sport/,
		/starkt var�dagligt/,
		/var�dagligt/,
		/dialektalt/,
		/i fack�språk/,
		/ofta i fackspråkliga samman�hang/,
		/nu\u00ADmera ej i fack\u00ADm�ssiga samman\u00ADhang/,
		/nu�mera mindre brukligt i fack\u00ADm�ssiga samman\u00ADhang/,
		/mindre brukligt/,
		/sär�skilt ekonomi/,
		/sär�skilt i matematiskt fack�spr�k/,
		/sär�skilt matematik/,
		/matematisk/,
		/fysik, kemi m.m./,
		/fysik/,
		/sär�skilt i juridiska samman\u00ADhang/,
		/sär�skilt juridik/,
		/juridik/,
		/sär�skilt i lag�språk/,
		/sär�skilt psykologi/,
		/psykologi/,
		/informations�teknik/,
		/sär�skilt i fack�språk/,
		/ofta i fack�språk/,
		/mest i fack�mässiga samman�hang/,
		/ålderdomligt ut\u00ADom i bibliska samman\u00ADhang/,
		/ålderdomligt ut\u00ADom i vissa ut�tryck/,
		/delvis något ålderdomligt/,
		/något ålderdomligt/,
		/något ålderdomligt el. ironiskt/,
		/ålderdomligt/,
		/sär�skilt vid beskrivning av ut�ländska förh�llanden/,
		/endast vid beskrivning av ut�ländska förh�llanden/,
		/något formellt/,
		/formellt i konkret an�vändning/,
		/formellt/,
		/delvis historiskt/,
		/mest historiskt/,
		/historiskt i Sverige/,
		/historiskt/,
		/sär\u00ADskilt i barn\u00ADspr�k och i imiterat barn\u00ADspr�k/,
		/mest i fack\u00ADspr�k/,
		/mest i sport�jargong/,
		/sär�skilt i vetenskapliga samman�hang/,
		/ofta i tekniska el. vetenskapliga samman�hang/,
		/i tekniska och vetenskapliga samman�hang/,
		/i vetenskapliga samman�hang/,
		/mest i tekniska samman�hang/,
		/mest i vetenskapliga samman\u00ADhang/,
		/sär�skilt i vetenskapliga samman\u00ADhang/,
		/i vetenskapliga samman\u00ADhang/,
		/ålderdomligt el. sk�mtsamt/,
		/ofta klandrande/,
		/mest vid beskrivning av äldre förh�llanden/,
		/s�r�skilt vid beskrivning av äldre förhållanden/,
		/äv. som musikalisk term/,
		/musikalisk/,
		/musik/,
		/sär�skilt meteorologi/,
		/sär�skilt i v�nster�politisk debatt/,
		/ofta i vänster�politisk debatt/,
		/ofta ned�sättande/,
		/ofta något ned�sättande/,
		/ned�sättande; något ålderdomligt/,
		/ned�sättande/,
		/ibland skämtsamt/,
		/ibland något skämtsamt/,
		/sär�skilt statistik/,
		/sär�skilt filosofi, biologi/,
		/sär�skilt filosofi/,			
		/filosofi/,		
		/i allmän�språket/,
		/sär�skilt sport/,
		/sär�skilt milit�r�v�sen och sport/,
		/sär�skilt milit�r�v�sen/,
		/sär�skilt sj��fart/,
		/ibland något ironiskt/,
		/sär�skilt arkeologi/,
		/ej officiell svensk beteckning/,
		/nu�mera ej officiell beteckning/,
		/försk�nande om�skrivning/,
		/ur�sprungligen bibliskt/,
		/bibliskt/,
		/ofta fysik och teknik/,
	]
	if (def.length < 3 || def[0].match(/A-Z/)) return def
	// Only apply one meta tag
	if (def[2] === "[" && def.indexOf("]") > 2) return def;	
	// Probably don't actual need regex but leave "ias is" for now
	for (i = 0; i < regexArr.length; i++) {
		let rawStr = regexArr[i].source;	
		browserDependentDelim = "";
		if (navigator.userAgent.indexOf("Chrome") != -1 ) {
			browserDependentDelim = " ";
		}
		
		if (def.indexOf(rawStr) === 2) {
			def = def.replace(rawStr, "[" + rawStr + "]" + browserDependentDelim);
		}
	}
	return def;
}

function grammar(def) {	
	if (DEBUG) console.log("grammar()");
	// Only one grammer expression is permitted.
	gArr = [
	 "n�stan en�bart presens particip",
	 "n�stan en�bart perfekt particip",
	 "vanligen i nekande och fr�gande ut�tryck",
	 "endast obest�md form sing.; i mer el. mindre adjektivisk an�v�ndning",
	 "anv. som plur.",
	 "i negerade och fr�gande samman�hang",
	 "ofta i substantivisk an�v�ndning",
	 "n�stan en�bart best�md form sing.",
	 "vanligen i b�jda former",
	 "n�stan alltid best�md form sing. och med versal",
	 "ofta best�md form sing. och med versal",
	 "vanligen best�md form sing. och med versal",
	 "med subst. som f�r�led",
	 "vanligen placerat efter huvud�ordet",
	 "ofta med versal",
	 "ofta med efter�f�ljande bi�sats",
	 "s�r�skilt i pass. konstruktioner",
	 "i konstruktion med komparativ",
	 "ofta substantiverat",
	 "ofta refl. el. perfekt particip",
	 "ofta refl. el. pass.",
	 "ofta refl."			,
	 "med konjunktionen",,
	 "vanligen pres.",
	 "endast pres. el. pret.",
	 "i best�md form",
	 "vanligen best�md form sing.",
	 "ofta best�md form sing.",
	 "vanligen best�md form",
	 "ofta plur.",
	 "knappast plur.; i vissa ut\u00ADtryck",
	 "knappast plur.",
	 "vanligen plur.",
	 "s�llan plur.",
	 "vanligen part.",
	// ToDo regex to get word reference	,
	 "ofta i sammans�ttn."		,
	 "mest i sammans�ttn.",
	 "nu�mera vanligen i sammans�tt."	,
	 "vanligen i sammans�ttn. och i vissa uttryck",
	 "vanligen i sammans�ttn.",
	"vanligen i vissa sammans�tt."			,
	"i vissa ut�tryck och sammans�ttn."			,
	"i sammans�ttn.",
	// ToDo regex this one			,
	"vanligen i vissa ut\u00ADtryck",
	"utan b�jning, i vissa ut\u00ADtryck",
	"s�r�skilt i vissa ut�tryck",
	 "s�r�skilt i ett ut�tryck",
	"vanligen i vissa ut\u00ADtryck",
	"i vissa ut\u00ADtryck",
	"vanligen i n�gra ut\u00ADtryck",
	"i n�gra ut\u00ADtryck",
	 "ofta pass. el. perfekt particip"			,
	"ofta pass."			,
	"vanligen pass. el. presens particip",
	"vanligen pass. el. perfekt particip",
	"vanligen pass. och perfekt particip",
	"vanligen pass. el. part.",
	"vanligen refl. el. pass.",
	"vanligen refl. el. perfekt particip.",
	"ofta best�md form",
	"vanligen perfekt particip",
	"ofta i perfekt particip",
	"ofta perfekt particip",
	"ofta presens particip",
	 "n�stan en�bart pres. el. pret.",
	"vanligen perfekt particip"			,
	"vanligen obest�md form sing.",
	"vanligen obest�md form",
	"ibl. tv� ord",
	"�v. tv� ord",
	"vanl. tv� ord",
	"�v. tre ord",
	"ofta tre ord",
	"vanl. tre ord",
	"n�stan en\u00ADbart i sammans�ttn.",
	"n�stan en�bart predikativt",
	"endast predikativt",
	"vanligen i konstruktioner"			,
	"vanligen i konstruktion",
	"vanligen i opersonlig konstruktion"			,
	"vanligen i opersonliga konstruktioner"			,
	"i opersonliga konstruktioner"			,
	"i opersonlig konstruktion"			,
	"vanligen opersonlig konstruktion",
	"vanligen koll.",
	 "ofta koll.",
	"ofta i nekande ut�tryck",
	"i nekande ut�tryck",
	"ofta i nekande el. fr�gande ut�tryck",
	"ofta i nekande konstruktion",
	"vanligen i nekande el. fr�gande ut�tryck",
	"ofta i nekande ut�tryck",
	"vanligen i nekande ut�tryck",
	"n�stan en�bart plur.",
	"i plur.",
	"vanligen predikativt",
	"�v. tv� ord",
	"ofta tv� ord",
	"i negerade samman�hang",
	"ofta i negerade ut�tryck"	,
	"i negerade ut�tryck",
	 "i ett f�tal ut�tryck",
	 "i negerade el. fr�gande samman�hang",
	 "vanligen supinum",
	 "ofta best. form sing.",
	 "ej predikativt",
	 "vanligen pass.",
	 "vanligen presens particip",
	 "vanligen pret.",
	]
	for (i = 0; i < gArr.length; i++) {
		let rep = gArr[i];
		/*
		if (def.indexOf(rep) != -1) {
			end = def.indexOf(rep) + rep.length;
			let after = def.substring(end,end+1);
			if (after === ")")
		}
		*/
		def = def.replace(" " + rep, " (" + rep + ")");		
	}
	return def;
}

function preProc(def) {
	if (DEBUG) console.log("preProc(def(); def = '" + def + "'")
	// takes user input and splits into "meta", "def" and "more" components
	let meta = "";	
	
	// " ++" is a user-control, remove if present in definition
	def = def.replaceAll(" ++","");
		
	// Check if any meta information exists		
	if (def[0] === " ") def = def.slice(1);
	
	let defArr = def.split("\n");
	// Concenate if multiple spellings are present
	// *** META ***
	if (defArr.length > 3 && defArr[1] === "eller" || defArr[1] === "�ven" || defArr[1] === "�ven �ld." || defArr[1] === "�ven vardagligt" || defArr[1] === "eller vardagligt") {
		defArr[0] = defArr[0] + " </i>" + defArr[1] + " <i>" + defArr[2];
		// Move remaining items up two elements
		for (i = 3; i < defArr.length; i++) {
			defArr[i-2] = defArr[i]
		}
		defArr.pop();
		defArr.pop();		
	}

	let firstLine = defArr[0];
	let opts = firstLine.split(" ");
	let firstWord = opts[0];
	let twoWords = "";
	let threeWords = "";
	let fourWords = "";
	// Longer matching only for phrases
	let fiveFras = "";
	let sixFras = "";
	let sevenFras = "";
	let eightFras = "";
	let nineFras = "";
	let tenFras = "";
	if (opts.length > 1) twoWords = firstWord + " " + opts[1];		
	if (opts.length > 2) threeWords = twoWords + " " + opts[2];
	if (opts.length > 3) fourWords = threeWords + " " + opts[3];
	if (opts.length > 4 && CLASS === "fraser") fiveFras = fourWords + " " + opts[4]; 
	if (opts.length > 5 && CLASS === "fraser") sixFras = fiveFras + " " + opts[5];
	if (opts.length > 6 && CLASS === "fraser") sevenFras = sixFras + " " + opts[6];
	if (opts.length > 7 && CLASS === "fraser") eightFras = sevenFras + " " + opts[7];
	if (opts.length > 8 && CLASS === "fraser") nineFras = eightFras + " " + opts[8];
	if (opts.length > 9 && CLASS === "fraser") tenFras = nineFras + " " + opts[9];
	
	var m;
	if (CLASS === "slutled") {
		m = "-" + CUR_WORD;
	} else if (CLASS === "f�rled") {
		m = CUR_WORD + "-";
	} else {
		m = CUR_WORD;
	}	
	// If the word differs only by conjugation from others in the word class then an additional enumeration is used.
	// Remove this enumeration when checking matching of root word for meta data extraction
	for (i = 2; i < 9; i++) {
		m = m.replace(" " + i, "");
	}
	/// *** META + DEF ****
	LongFrasMatch = fiveFras === m || sixFras === m || sevenFras === m || eightFras === m || nineFras === m || tenFras === m;	
	if (firstWord === m || twoWords === m || threeWords === m || fourWords === m || LongFrasMatch) {		
		meta = defArr[0].replaceAll("_"," ");		
		let del = "";
		def = "";
		for (let i = 1; i < defArr.length; i++) {		
			if (i < 3) {							
				if (defArr[i].indexOf('\u25cf') === 0) {
					def += del + defArr[i];
					del = "\n";
				} else {
					meta += "<br>" + defArr[i].replace("ORDKLASS: ","").replace("UTTAL: ","");
				}
			} else {
				def += del + defArr[i];
				del = "\n";
			}
		}
	}	
	/// *** DEF ***
	// Split definition
	def = def.replaceAll(" JFR", "\nJFR");
	def = def.replaceAll(" SE", "\nSE");
	def = def.replaceAll(" SYN.", "\nSYN.");
	def = def.replaceAll(" MOTSATS", "\nMOTSATS");	
	arr = def.split("\n");
	
	let tmpDef = "";
	let tmpMore = "";
	let del = "";	
	let delDef = "";
	for (i = 0; i < arr.length; i++) {
		// \u25cf or [1-20] is part of a definition
		// Also "JFR", "MOTSATS", "SE" are part of definition
		let cur = arr[i] // current
		let char1 = cur[0];
		let isLink = cur.slice(0,3) === "JFR" || cur.slice(0,6) === "MOTSATS" || cur.slice(0,2) === "SE" || cur.slice(0,3) === "SYN";
		let isNum = false;
		for (j = 1; j < 20; j++) {
			jStr = j.toString();
			n = jStr.length;			
			if (cur.slice(0,n) === jStr) {				
				isNum = true;				
				break;
			}
		}
		let isSingle = char1 === "\u25cf" || char1 === "\u2022"; // single definition		
		let isSamman = cur.slice(0,6) === "till '"// sammans�ttning
		if (isSingle || isLink || isNum || isSamman || CLASS === "fraser") {			
			tmpDef += delDef + cur;			
			delDef = "\n";			
		} else {			
			if (cur.length != 0) {
				if (cur.slice(0,6) === "belagt") cur = "HISTORIK:<br>" + cur
				tmpMore += del + cur;				
				del ="<br>"
			}
		}		
	}	
	arr = tmpDef.split("\n");
	let outDef = "";
	// Grammer information - Support for partikel parters
	// Can be between 1 and 3 suggested partner words
	let regex1 = /skilt[a-z���]+/;
	let regex1_alt = /partikeln[a-z���]+/;
	let regex2 = /skilt[a-z���]+, [a-z���]+/;
	let regex3 = /skilt[a-z���]+, [a-z���]+, [a-z���]+/;
	// Delimiter is blank on first line
	del = "";
	for (s of arr) {				
		if (s.length > 1) {
			// Remove any leading space(s)
			while (s[0] === " ") {
				s = s.slice(1,s.length)
			}
			// Defuault to "pretty" leading bullet point
			if (s[0].match(/[<a-zåöä([]+/) != null) {
				s = `\u25CF ` + s;
			}
			// Leading Meta tags
			// Beware the silent hyphens (\u00AD)			
			// ToDo Move the (many) regex definitions to the function body
			s = addMeta(s)
			
			// Remove any reference numbers other than that at line start			 
			let trail = s.slice(2,s.length);
			for (let i = 1; i < 24; i++) {				
				//trail = trail.replace(" " + i + " "," "); Temp remove as quick-fix, investigate further
				if (trail.indexOf(i) === 0) {
					//trail = trail.replace(i,""); Temp remove as quick-fix, investigate further
				}
			}
			trail = fmtParticles(trail);
			trail = fmtPrep(trail);
			s = s.slice(0,2) + trail;		
			s = s.replaceAll("  ", " ");
			s = s.replaceAll("  ", " ");
			
			if (s.includes("((")) s = s.replace("((", "(");
			// Trailing spaces (or non-breaking space) can break links, remove
			let lastCh = s.charAt(s.length-1);			
			if (lastCh.charCodeAt(0) === 32 || lastCh.charCodeAt(0) === 160) {								
				s = s.slice(0,s.length-1)
			}
			s = grammar(s)
			
			s = s.replace("vanligen med pronomenetsj�lv","(vanligen med pronomenet sj�lv)");
			// ToDo Regex this to include the multi-word verbs			
			s = fmtVerbApart(s,/(�v. l�s f�rbindelse, se )([a-z���]+)(\s[a-z���_]+)/);
			s = s.replace("formerna", "formerna ");
			s = s.replace("formen", "formen ");
			s = fmtVerbApart(s,/(�v. l�s f�rbindelse i formen )([a-z���]+)(\s[a-z���_]+)/);
			s = fmtVerbApart(s,/(�v. l�s f�rbindelse, jfr )([a-z���]+)(\s[a-z���_]+)/);
			s = fmtVerbApart(s,/(ofta l�s f�rbindelse i formen )([a-z���]+)(\s[a-z���_]+)/);
			s = fmtVerbApart(s,/(ofta l�s f�rbindelse, se )([a-z���]+)(\s[a-z���_]+)/);
			s = fmtVerbApart(s,/(ofta l�s f�rbindelse se )([a-z���]+)(\s[a-z���_]+)/);
			s = fmtVerbApart(s,/(ofta l�s f�rbindelse, jfr )([a-z���]+)(\s[a-z���_]+)/);
			s = fmtVerbApart(s,/(vanligen l�s f�rbindelse, se )([a-z���]+)(\s[a-z���_]+)/);
			s = fmtVerbApart(s,/(vanligen l�s f�rbindelse, jfr )([a-z���]+)(\s[a-z���_]+)/);
			s = fmtVerbApart(s,/(�v. l�s sammans�ttn., se )([a-z���]+)(\s[a-z���_]+)/);			
			// Format "fast sammans�ttn." grammer
			s = fmtVerbTogether(s,/(vanligen fast sammans�ttn., se )([a-z���_]+)/);
			s = fmtVerbTogether(s,/(vanligen fast sammans�ttn., jfr )([a-z���_]+)/);
			s = fmtVerbTogether(s,/(n�gon g�ng fast sammans�ttn., se )([a-z���_]+)/);
			s = fmtVerbTogether(s,/(ofta fast sammans�ttn., se )([a-z���_]+)/);
			s = fmtVerbTogether(s,/(ofta fast sammans�ttn., jfr )([a-z���_]+)/);
			s = fmtVerbTogether(s,/(�v. fast sammans�ttn., se )([a-z���_]+)/);			
			s = fmtVerbTogether(s,/(�v. fast sammans�ttn., jfr )([a-z���_]+)/);			
			s = simpleRef(s);		
			s = simpleBold(s);		
			// ToDo Regex this to include the verb
			//s = grammer(s,"vanligen i f�rbindelse");			
			s = s.replaceAll("_","");			
			outDef += del + s;			
			// HTML newline separator bon lines subsequent to the first
			del = "<br>";
		}
	}
	outDef = outDef.replace(",,","")
	
	// Get any "more" information that exists in the def
	
	var out = []
	out[0] = outDef;
	out[1] = meta;	
	out[2] = tmpMore;	
	// That's it!
	return out;
}

function fmtParticles(s) {
	if (DEBUG) console.log("fmtParticles(s)");
	// Particles appear comma delimited with space delimiter before the last particle.

	//const regex1 = /.*med partikel(?: eller dylikt),(s�r�skilt)(([a-z���]+, )*[a-z���]+).*/;		
	const regex1 = /.*med partikel(?: eller dylikt)?(?:, )(s�r�skilt)(([a-z���]+, )*[a-z���]+).*/;		
	match = s.match(regex1);
	
	if (match) s = "(" + s.replace(match[1] + match[2], match[1] + " <b>" + match[2] + "</b>)"); 
	
	const regex2 = /.*med partikel[n]?[,]? t.ex.(([a-z���]+, )*[a-z���]+).*/;
	match = s.match(regex2);	
	if (match) s = "(" + s.replace(match[1]," <b>" + match[1] + "</b>)");

	const regex3 = /.*med partikeln([a-z���]+).*/;
	match = s.match(regex3);
	if (match) s = "(" + s.replace(match[1]," <b>" + match[1] + "</b>)");
	
	const regex4 = /.*med partik[elnarn]+ som an.ger (?:r�relse)?riktning.*(t(?:ill)?\.ex\.|s�r.skilt)/;
	match = s.match(regex4);
	
	const regex5 = /.*med partik[elnarn]+ som betecknar riktning.*(t(?:ill)?\.ex\.|s�r.skilt)/;
	match = s.match(regex5);
	
	if (match) {
		let tmp = s.replace(match[0],"");
		let tmpArr = tmp.split(",");
		let old = "";
		let rep = "";
		let d = ""
		for(i = 0; i < tmpArr.length; i++) {
			let t = tmpArr[i]
			
			if (t.indexOf(" ") != -1) {
				if (t.indexOf(" ") === 0) t = t.substr(1);				
				old += d + t.split(" ")[0];
				if (t.indexOf(" ") != -1) break;
			} else {
				old += d + tmpArr[i];
			}
			d = ", "
		}
		rep = " <b>" + old + "</b>)";
		s = "(" + s.replace(match[0] + old,match[0] + rep);
	}
		
	const regex6 = /.*med n�gon av partiklarna(([a-z���]+, )*[a-z���]+).*/;
	match = s.match(regex6);	
	if (match) s = "(" + s.replace(match[1]," <b>" + match[1] + "</b>)");		
	
	s = s.replace("utan</b>) st�rre betydelse�skillnad", "</b> utan st�rre betydelse�skillnad)");
	s = s.replace("), utan st�rre betydelse�skillnad",", utan st�rre betydelse�skillnad)");
	// Ugly hack for now
	if (s.indexOf("([") != -1) {
		s = s.replace("([","[");
		s = s.replace("]","] (");
		s = s.replace("( ","(");
	}
	return s;
}

function fmtPrep(s) {
	const regex1 = /.*med prep.(([a-z���]+, )*[a-z���]+).*/;
	match = s.match(regex1);
	// Includes prep in replace for case where 'med' is the preposition
	if (match) s = "(" + s.replace("prep." + match[1],"prep. <b>" + match[1] + "</b>)");	
	return s;
}

function fmtVerbTogether(s,regx) {
	if (DEBUG) console.log("fmtVerbTogether(s,regx), s = " + s + ", regx = " + regx);	
	m = s.match(regx);
	
	if (m != null) {
		if (m.length === 3) {			
			let link = "<l>_w_</l>".replaceAll("_w_",m[2]);				
			// Allow user to extend the verb to include another partikel e.g. "sig"
			link = link.replaceAll("__"," ");
			s = s.replace(m[0],"(" + m[1] + link + ")");
		}
	}
	return s
}

function  fmtVerbApart(s,regx) {
	if (DEBUG) console.log("fmtVerbApart(s,regx), s = " + s + ", regx = " + regx);
	m = s.match(regx);
	if (m != null) {
		if (DEBUG) console.log("fmtVerbApart: Match!");
		if (m.length === 4) {		
			let repWord = m[2] + " " + m[3].substring(1);			
			// remove leading space from second word and replace with regular space.
			let link = "<l>_w_</l>";
			link = link.replaceAll("_w_", repWord);
			// Allow user to match only a single verb word
			link = link.replaceAll(" __","");
			link = link.replaceAll("___", " ");
			s = s.replace(m[0],"(" + m[1] + link + ")");			
		}
	}
	return s	
}
function simpleRef(s) {
	regx = "/\(se �v. ([a-z���]+)\)/"
	let link = "<l>_w_</l>";		
	m = s.match(regx);
	if (m) {
		s = s.replace(m[0],"(se �v. " + link.replaceAll("_w_",m[2]) + ")");
	}
	return s;
}

function simpleBold(s) {
	regx = "((vanligen (?:med )?komparativ)([a-z���]+))"
	let bold = "<b>_w_</b>";
	m = s.match(regx);
	if (m) {
		s = s.replace(m[0],"(" + m[2] + " " + bold.replaceAll("_w_"," " + m[3]) + ")");
	}	
	return s;
}

function withParticle(s) {
	rep = "ibland with partikel";
	s = s.replace(rep, "(" + rep);
	rep = "ofta with partikel";
	s = s.replace(rep, "(" + rep)
	rep = "vanligen with partikel";
	s = s.replace(rep, "(" + rep);
	repo = "vanligen med n�gon av partiklarna";
	s = s.replace(rep, "(" + rep);
	return s;
}

function highlight(el,event) {
	if (event.type === "mouseover") {
		el.style.cursor = "pointer";
	} else {
		el.style.cursor = "";
	}
}

function editCursor(event,el) {
	if (event.type === "mouseover") {
		el.style.cursor = "text";
	} else {
		el.style.cursor = "";
	}
}

function toggleDebug() {
	DEBUG = !DEBUG;
	if (DEBUG) {
		log("Entering debug mode...");
		$('#iDebugTxt').text("DEBUG");
		$('#iDebug').css("color","gray");
	}  else {
		$('#iDebug').css("color","");
		$('#iDebugTxt').text("");
		log("Leaving debug mode...");
	}
}

function fmtMeta(meta) {
	if (DEBUG) console.log("FUNC fmtMeta(meta)");
	if (DEBUG) console.log("  PARAM meta = " + meta);
	let rep = "<span id='iWordRoot'"
	rep += " onmouseover=highlight(this,event)";
	// Remove any disambiguation suffix when more than one entry exists for root key
	tmpCUR_WORD = CUR_CONJ;
	tmpCUR_WORD = tmpCUR_WORD.replace(/-.*/,"");
	rep += "><b>" + tmpCUR_WORD + "</b></span>"
	if (CLASS === "fraser") {
		let tmpMeta = rep = "<i>" + rep + "</i><br>"
		return tmpMeta
	}
	meta = meta.replace(tmpCUR_WORD,rep);
	metaArr = meta.split("<br>");
	let tmpMeta = "";
	delMeta = "";	
	for (let i = 0; i < metaArr.length; i++) {						
		if (i == 0) {
			delMeta = "";
		} else if (i === 1) {
			delMeta = "<br>ORDKLASS: ";
		} else if (i === 2) {
			delMeta = "<br>UTTAL: ";
		} else {
			delMeta = "<br>";
		}		
		// Quick-hack, do not show ORDKLASS or UTTAL
		if (i == 0) tmpMeta += delMeta + metaArr[i];
	}
	tmpMeta = "<i>" + tmpMeta + "</i><br>"
	let pretty_class = CLASS.replace(/_.*/,"");
	tmpMeta += "<br>" + pretty_class + "<br>";
	// Multi-line meta information get visual divider from the following definition text.
	tmpMeta += "---------------------<br>";
	return tmpMeta;
}
function ScrollViewDownWrapper() {

	if (false && $('#iDefDiv').css("display") === "none") {
		scrollViewDown();
	}
}

function closeDefs() {
	if (TABS.length === 0) {
		setClass("all");
	}
	for (i = 0; i < TABS.length; i++) {
		TABS[i].close();
	}
	TABS.length = 0;
}

// 1-based indices instead of 0-based for storage
function keyForUser(key) {
	start = key.indexOf("_");
	let res = key;
	if (start === -1) return key
	zero_based_ind = parseInt(key.substring(start+1));
	one_based_ind = zero_based_ind + 1
	res = key.replace("_" + zero_based_ind, "_" + one_based_ind);	
	res = res.replaceAll("-"," ");
	res = res.replaceAll("_", " (");
	res = res + "/" + N_DEF;
	res = res + ")";
	return res
}

function getMore(ind) {		
	if (DEBUG) console.log("getMore()");	
	if (CLASS === "fraser") return;
	if (!isNumeric(ind)) {
		console.log("getMore(ind), ind is NaN");
		return;
	}
	
	if (DEBUG) console.log("getMore(ind), ind = " + ind);			
	ind--;
	if (ind > N_DEFS) return;	
	let key = CUR_WORD.replaceAll(" ","-") + "_" + ind;	
	fetch('backend/showMore.php?class=' + CLASS + '&word=' + key, {
		method: 'get',
		mode: 'cors',
		headers: {
			'Content-Type': 'application/json'
		}
	})
	.then(response => {
		return response.json()
	})
	.then(json => {
		if (json.length === 0) {
			$('#iMoreText').empty();
		} else {												
			// Add links
			moreArr = json.split("<br>");
			let del = "";
			let tmpMore = "<br>";
			for (i = 0; i < moreArr.length; i++) {
				tmpMore += del + moreArr[i];
				moreArr[i];					
				del = "<br>";					
			}
			let tmp = "*********************<br>"		
			tmp += "<span id='iMore' oncontextmenu=editMore(event,'" + key + "')";						
			tmp += tmpMore;
			tmp = tmp.replaceAll("KONSTRUKTION:", "<b>KONSTRUKTION:</b>");
			tmp = tmp.replaceAll("SAMMANS\u00c4TTN./AVLEDN.:","<b>SAMMANS\u00c4TTN./AVLEDN.:</b>");
			tmp = tmp.replaceAll("EXEMPEL:","<b>EXEMPEL:</b>");
			tmp = tmp.replaceAll("HISTORIK:","<b>HISTORIK:</b>");
			// Use displayDef to to dynamise word links
			// Dynamise links
			tmp = tmp.replaceAll("<l>","<span class='link' onclick=followLink(this.innerText)>");
			tmp = tmp.replaceAll("</l>","</span>");
			tmp = tmp.replaceAll("<lf>","<span class='fraser' onclick=seekWord(this.innerText)><b>");
			tmp = tmp.replaceAll("</lf>","</b></span>");
			$('#iMoreText').html(tmp);		
			let moreHeight = parseInt($('#iMoreText').css("height").replace("px",""));
			MORE_LIM = 350; // If bigger than this then scrolling is required
			if (moreHeight > MORE_LIM) {			
				$('body').css("overflow-y", "scroll");														
				window.scrollTo(0,LAST_SCROLL_Y);
			} else {			
				$('body').css("overflow-y", "hidden");
			}
			$('#iInput').attr("placeholder", "Press ENTER to show less");
		}				
	})
	.catch(error => {
		log(error);
	})
}

function editMore(ev,key) {
	if (DEBUG) console.log(ev,key)	
	ev.preventDefault();
	let tmp = $('#iParDef').html();
	if (tmp.indexOf(" --") === -1 && tmp.indexOf(" ++") != -1) {
		CACHED_DEF_HTML = $('#iParDef').html();		
	}
	$("#iPutDef").css("display", "none");
	$('#iDefDiv').css("display", "block");	
	$("#iPutMore").css("display", "block");	
	focusDef();
}

function processKeyDown(ev) {
		
	if (DEBUG) console.log("processKeyDown(ev), ev.key = " + ev.key)	
	
	if (ev.key === "Escape") {
		$('#iInput').val("");
	}
	if (ev.key === "!") {			
		ev.preventDefault();	
		let tmp = $('#iInput').val() + "!"		
		seekWord(tmp);
	}
	if (ev.key === "ArrowUp") {
		let el = document.getElementById("iInput");
		onlineLookup(el,ev);
		// ?!
		focusDef()
	} else if (ev.key === "Insert") {
		addWord();	
	} else if (ev.key === "Delete") {
		removeWord();
	} else if (ev.key === "F1") {
		//showHelp
	} else if (ev.key === "*") {
		ev.preventDefault();
		fromEnglish();
	} else if (ev.key === "ArrowDown" || ev.key === "F2") {
		fetchDef();
	} else if (ev.key === "F6") {
		walkListing();
	} else if (ev.key === "#") {
		addWordWithEnum();
	} else if (ev.key === '"') {
		defaultMeta();
	} else if (ev.key === '!') {
		defaultMeta(false);
	} else if (ev.key === "ArrowLeft" || ev.key === "ArrowRight") {		
		navDef(ev);
	}
	
	if (ev.location === 2) {
		if (ev.key === "Control") {			
			if (CLASS != "all") {				
				setClass("all");
				randomWord();
			} else {
				$('#iInput').val("");
			}
		}
	}

	if (ev.key === "PageDown") {
		inc_match(1)
	} else if (ev.key === "PageUp") {
		inc_match(-1);
	} 

	if ( ev.code === "Backquote") {
		ev.preventDefault();
		getLastWord();	
	}	
    let regexNumeric = /^\d*$/
	if (ev.key.match(regexNumeric)) {		
		if (ev.key === "0" || ev.key === "9" || ev.location === 3) {			
			ev.preventDefault();	
			handleNumericInput(ev.key);
		}
    }
}

function navDef(ev) {
	if (DEBUG) console.log("navDef(ev)");
	let tmp = $('#iInput').val();
	$('#iMoreText').empty();
	if (tmp.length === 0) {
		if (ev.key === "ArrowLeft") {			
			CUR_DEF_ENUM--;
			if (CUR_DEF_ENUM < 1) CUR_DEF_ENUM = 1;
		} else if (ev.key === "ArrowRight") {
			CUR_DEF_ENUM++;
			if (CUR_DEF_ENUM > N_DEFS) CUR_DEF_ENUM = N_DEFS;
		}
		def = getDefInd(CUR_DEF_ENUM);
		
		displayDef(def,CUR_WORD);
	}	
}

function hack(ev) {
	if (DEBUG) console.log("hack()");
	ev.preventDefault();
	$('#iDefDiv').css("display","block");
}

function makeHash() {
	let dt = new Date();
	let hash = dt.getDate() + "/" + dt.getMonth() + "/" + dt.getFullYear() + " " + dt.getHours() + ":" + dt.getMinutes() + ":" + dt.getSeconds();
	return hash;
}

function bodyListener(ev) {
	if (ev.key === "Home") {
        focusInput();
    } else if (ev.key === "v") {
        if (! $('#iInput').is(":focus")) {
			if(! $('#iDefText').is(":focus")) {
				ev.preventDefault();
				setClass("verb");
			}
        }
    } else if (ev.key === "d") {
        if (! $('#iInput').is(":focus")) {
			if(! $('#iDefText').is(":focus")) {
				ev.preventDefault();
				setClass("adjektiv");
			}
        }
    } else if (ev.key === "n") {
        if (! $('#iInput').is(":focus")) {
			if(! $('#iDefText').is(":focus")) {
				ev.preventDefault();
				setClass("substantiv_en");
			}
        }
    } else if (ev.key === "t") {
        if (! $('#iInput').is(":focus")) {
			if(! $('#iDefText').is(":focus")) {
				ev.preventDefault();
				setClass("substantiv_ett");
			}
        }
    } else if (ev.key === "a") {
        if (! $('#iInput').is(":focus")) {
			if(! $('#iDefText').is(":focus")) {
				ev.preventDefault();
				setClass("adverb");
			}
        }
    } 
}

function defaultMeta(hasPlural = true) {
	let meta = "";
	let len = CUR_WORD.length;
	let lastChar = CUR_WORD.slice(-1)
	if (CLASS === "substantiv_en") {
		meta = CUR_WORD + " ~en"
		if (hasPlural) meta += " ~ar"
		meta += "<br>substantiv";			
		if (CUR_WORD.slice(-3) === "het") meta = meta.replace("~ar","~er");
		if (lastChar === "e" || lastChar === "a") {
			meta = meta.replace("~en","~n").replace(" ~ar","");
		}
	} else if (CLASS === "substantiv_ett") {
		meta = CUR_WORD + " ~et"
		if (hasPlural) meta += " ~en"
		meta += "<br>substantiv";
		if (CUR_WORD.slice(-4) === "ande" || lastChar === "e") {
			meta = meta.replace("~et","~t");
			meta = meta.replace("~en","~n");
		}
	} else if (CLASS === "adjektiv") {
		meta = CUR_WORD + " ~t ~a<br>adjektiv";
		if (CUR_WORD.slice(-2) === "ad") meta = CUR_WORD + " " + CUR_WORD.slice(len - 2) + "at" + " " + CUR_WORD + "e<br>adjektiv";
	} else if (CLASS === "verb") {
		meta = CUR_WORD + " ~de ~t<br>verb";
	}
	// So far only support for "en" words
	if (CLASS != "substantiv_en" && CLASS != "adjektiv" && CLASS != "verb" && CLASS != "substantiv_ett") return;
	fetch('backend/putDef.php?&def=' + '&meta=' + meta + '&class=' + CLASS + '&word=' + CUR_WORD, {
		method: 'get',
		mode: 'cors',
		headers: {
			'Content-Type': 'application/json'
		}
	})
	.then(response => {
		return response.json();
	})
	.then(json => {
		seekWordSingleClass();
	})
	.catch(error => {
		log(error);
	})
}

function round(dec, places) {
	let mult = 10 ** places;	
	let res = (Math.round(dec * mult) / mult).toString();
	if (places > 0 && res.indexOf(".") === -1) res  += ".0";
	return res;
}

function fetchDef() {
	if (DEBUG) console.log("FUNC fetchDef()");
	$('#iMeta').html("searching for '<b>" + CUR_WORD + "</b>' ... ");
	$('#iParDef').html("");
	$('#iMore').html("");
	let word = $('#iInput').val();
	if (word.length === 0) word = CUR_WORD;
	let rootWord = CUR_WORD;
	if (rootWord.indexOf("-") != -1) {
		rootWord = rootWord.slice(0,rootWord.indexOf("-"));
	}
	fetch('backend/ScrapeID.php?word=' + forScrapeID(word) + '&class=' + CLASS + "&debug=0", {
		method: 'get',
		mode: 'cors',
		headers: {
			'Content-Type': 'application/json'
		}
	})
	.then(response => {
		return response.json()
	})
	.then(json => {	
		let id = json["id"];
		let snr = json["snr"]		
		// 'debug' argument shows output from external svenska.se reference
		let backend = 'backend/ScrapeDef.php?word=' + forScrapeDef(word) + "&snr=" + snr + '&id='  + id + '&class=' + CLASS + '&debug=0';
		if (CLASS === "fraser") {	
			let query = $('#iInput').val();
			query = query.replaceAll("�",""); // No invisible chars to user present in keys
			backend = 'backend/ScrapeIdiom.php?query=' + query
		}
		
		fetch(backend, {
			method: 'get',
			mode: 'cors',
			headers: {
				'Content-Type': 'application/json'
			}
		})
		.then(response => {
			return response.json();
		})	
		.then(json => {		
			if (CLASS === "fraser") {			
				let tmp ="";
				let keys = Object.keys(json);
				del = "";
				for(let i = 0; i < keys.length; i++) {
					tmp += del + keys[i] + "%% " + json[keys[i]];
					del = "\n";
				}			
				$('#iDefDiv').css("display", "block");
				$('#iPutDef').css("display", "block");
				$('#iDivMore').css("display", "none");
				focusDef();
				$('#iDefText').val(tmp);					
				
			} else {
				if (! json.hasOwnProperty("error")) {
					const regex = /-(\[)+[2-9](\])+/;
					let key = json["key"].replace(regex, "");				
					key = key.replaceAll("?","");				
					if (key[0] === "-") key = key.substr(1);
					addDef(key,json['meta'],json['def'],json['more'], json["class"]);		
				} else {
					$('#iMeta').html("Failed to find match");
				}
			}
		})
		.catch(error => {
			console.log(error);
		})
	})
	.catch(error => {
		console.log(error);
	})
}

function getRaw() {
	if (DEBUG) console.log("FUNC getRaw()")
	fetch('backend/scrapeRaw.php?word=' + CUR_WORD + "&class=" + CLASS, {
		nethod: 'get',
		mode: 'cors',
		headers: {
			'Content-Type': 'application/json'
		}		
	})
	.then(response => {
		return response.json();
	})
	.then(json => {
		let url = json;
		window.open(url)
	})
	.catch(error => {
		log(error);
	})
	
	// Make this more intelligent so that it resolves the work class if multiple matches
}

function shortenClass() {
	// easier to handle short file names
	let res = CLASS;
	if (CLASS === "substantiv_en") {
		res = "en";
	} else if (CLASS === "substantiv_ett") {
		res = "ett";
	} else if (CLASS === "adjektiv") {
		res = "adj";
	} else if (CLASS === "interjektion") {
		res = "int";
	} else if (CLASS === "r�kneord") {
		res = "r�k";
	} else if (CLASS === "slutled") {
		res = "slut";
	} else if (CLASS === "f�rled") {
		res = "f�r";
	} else if (CLASS === "adverb") {
		res = "adv";
	} else if (CLASS === "preposition") {
		res = "prep";
	} else if (CLASS === "pronomen") {
		res = "pro";
	} else if (CLASS === "plural") {
		res = "plu";
	}
	return res;
}

function nDefs() {
	if (DEBUG) console.log("FUNC nDefs()")
	let n = 0;	
	let regex = /^\d+/;
	let tmpArr = CUR_DEF.split("<br>");
	
	for (let i =0; i < tmpArr.length; i++) {		
		if (regex.test(tmpArr[i])) n++;
	}
	if (n === 0) n = 1;
	return n;
}

// Pick out a single definition based on index
function getDefInd(ind) {		
	if (CLASS === "fraser") return CUR_DEF;
	if (DEBUG) console.log("FUNC getDefInd(ind), ind = " + ind)	
	let out = "";
	regex = /^\d+/;
	let tmpArr = CUR_DEF.split("<br>");
	let n = 0;
	del = "";	
	for (let i = 0; i < tmpArr.length; i++) {
		if (regex.test(tmpArr[i])) n++
		if (n > ind) break;
		if (n === ind || N_DEFS === 1) {
			out += del + tmpArr[i];
			del = "<br>";
		}
	}			
	return out;
}

function isNumeric(value) {
    return /^-?\d+$/.test(value);
}

function walkListing() {
	if (DEBUG) console.log("FUNC walkListing()");
	fetch('backend/getListing.php', {
		method: 'get',
		mode: 'cors',
		headers: {
			'Content-Type': 'application/json'
		}
	})
	.then(response => {
		return response.json();	
	})
	.then(json => {
		let arr = json;
		let last_inc
		for (let i = 0; i < arr.length - 1; i++) {
		
			let word = arr[i];
			if (word.length > 0) {
				let inc = i * 6000;			
				setTimeout(fillInput,inc,word);						
				//setTimeout(rand,inc);
			}
		}
		setTimeout(fillInput, arr.length * 6000, "DONE");
	})
	.catch(error => {
		log(error);
	})
}

function fillInput(text) {	
	if (DEBUG) console.log("FUNC fillInput(text), text = " + text)
	$('#iInput').val(text);
}

function rand() {
	$('#iInput').val(Math.random());
}

function fromEnglishExternal() {
	let src = $('#iInput').val();	
	if (src.length > 0) {
		let url = "https://translate.google.com/?sl=en&tl=sv&text=<src>&op=translate"
		url = url.replace("<src>", src);
		window.open(url);
	}
}

function fromEnglish() {
	let src = $('#iInput').val();	
	if (src.length > 0) {
		fetch('backend/fromEnglish.php?text=' + src, {
			method: 'get',
			mode: 'cors',
			headers: {
				'Content-Type': 'application/json'
			}
		})
		.then(response => {
			return response.json();
		})
		.then(json => {
			if (json != null) {
				$('#iInput').val(json)
			} else {
				// If no local translation use external service
				
				let url = "https://translate.google.com/?sl=en&tl=sv&text=<src>&op=translate"
				url = url.replace("<src>", src);
				window.open(url);
			}
		})
		.catch(error => {
			console.log(error);
		})
	}
}

function fromSwedish(ev) {
	ev.preventDefault();
	let src = $('#iInput').val();
	if (src.length > 0) {
		let url = "https://svenska.se/so/?sok=" + src;
		window.open(url);
	}
}

function hideTitle() {
	$('#iDivTitle').css("display", "none");
}

function fetchDefWords(allWords,ind) {
	if (DEBUG) console.log("fetchDefWord()");		
	let word = allWords[ind];
	if (word === "anno dazumal") {
		alert(1);
		return fetchDefWords(allWords,ind+1);
	}
	
	let backend = 'backend/ScrapeID.php?word=' + word + '&class=' + CLASS + '&debug=0';
	fetch(backend, {
		method: 'get',
		mode: 'cors',
		headers: {
			'Content-Type': 'application/json'
		}
	})
	.then(response => {
		return response.json();
	})
	.then(json => {
		if (json["snr"] && json["id"]) {			
			let id = json["id"];
			let snr = json["snr"]		
			let backend = 'backend/ScrapeDef.php?word=' + word + "&snr=" + snr + '&id='  + id + '&class=' + CLASS + '&debug=0';
			fetch(backend, {
				method: 'get',
				mode: 'cors',
				headers: {
					'Content-Type': 'application/json'
				}
			})
			.then(response => {
				return response.json();
			})
			.then(json => {
				if (! json.hasOwnProperty("error")) {
					let url = 'backend/addDef.php?word=' + word.replace("-","") + '&meta=' + json["meta"] + '&def=' + json["def"] + '&more=' + json["more"] + '&class=' + CLASS; 
					fetch(url, {
						method: 'get',
						mode: 'cors',
						headers: {
							'Content-Type': 'application/json'
						}
					})
					.then(response => {
						let upper = allWords.length - 1
						if (ind > upper) {
							return;
						} else {					
							return fetchDefWords(allWords,ind+1);
						}
					})
					.catch(error => {
						console.log(error);
					})
				} else {
					$('#iMeta').html("Failed to find match");
					let upper = allWords.length - 1
					if (ind >= upper) {						
						return;
					} else {
						return fetchDefWords(allWords,ind+1);
					}
				}
			})
		} else {
			// Continue to next word if no definition found for current
			return fetchDefWords(allWords,ind+1);
		}
	})
	
}

function forScrapeID(word) {
	// Use * to delimit instead 
	word = word.replace("_","*");
	const re = /\([2-9]\)/;
	word = word.replace(re,"");	
	const re2 = /-([2-9])/;
	word = word.replace(re2,"?$1");
	return word;
}

function forScrapeDef(word) {
	const re = /\[[2-9]\]/;
	word = word.replace(re,"");
	return word;
}

function ScrapeByID(ev,id) {
	ev.preventDefault();
	let snr="";
	let backend = 'backend/ScrapeDef.php?word=' + forScrapeDef(word) + "&snr=" + snr + '&id=' + id + '&class=' + CLASS + '&debug=0';
	fetch(backend, {
		method: 'get',
		mode: 'cors',
		headers: {
			'Content-Type': 'application/json'
		}
	})
	.then(response => {
		return response.json();
	})
	.then(json => {
		if (key[0] === "-") key = substr(key,1);
		addDef(json["key"],json['meta'],json['def'],json['more']);		
	})
	.catch(error => {
		console.log(error);
	})
}

function inc_match(inc) {
	if (DEBUG) console.log("FUNC inc_match(inc)");
	if (DEBUG) console.log("  PARAM inc = " + inc);
	let cur_match_inc = GLOBAL.match_inc;
	GLOBAL.match_inc += inc;
	if (GLOBAL.match_inc < 0) GLOBAL.match_inc = 0;
	if (GLOBAL.match_inc > GLOBAL.cur_matches_count-1) GLOBAL.match_inc = GLOBAL.cur_matches_count- 1;
	if (GLOBAL.match_inc != cur_match_inc) {
		let key = Object.keys(GLOBAL.cur_matches)[GLOBAL.match_inc];
		setClass(key);
		// This implies unique keys return from getDefAll
		CUR_DEF = GLOBAL.cur_matches[CLASS]["def"];
		N_DEFS = nDefs();
		let def = "";
		if (N_DEFS > 1) {
			def = getDefInd(CUR_DEF_ENUM)
		} else {
			def = CUR_DEF;
		}
		displayDef(def);
		displayMeta(GLOBAL.cur_matches[CLASS]["meta"]);
	}
}