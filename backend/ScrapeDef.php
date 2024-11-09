<?php
	$debug = $_GET['debug'];
	
	$word = $_GET["word"];
	$word = preg_replace("/-[2-9]/","",$word);
	$id = $_GET['id'];	
	$enum = "";
	if (str_contains($id,"_")) {
		$enum = $id[strlen($id)-1];
	}
	$snr = $_GET["snr"];
	$class = $_GET['class'];
	// return an array to support cases where multiple entries exist
	$out['meta'] = "";
	$out['def'] = "";
	$out['more'] = "";
	$urlBaseId = 'https://svenska.se/tri/f_so.php?id=';
	$url = $urlBaseId . $id;
	if ($class === "plural" && strlen($snr) > 0) $url = 'https://svenska.se/tri/f_so.php?sok=' . $word;
	$ch = curl_init();		
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch,CURLOPT_URL,$url);
	curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
	curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:109.0) Gecko/20100101 Firefox/112.0");				
	$def = curl_exec($ch);		
	if ($debug) {			
		echo $def;
		return;
	}
	curl_close($ch);	
	//var_dump($def);
	if (strlen($def) === 0) {
		error("Failed to retrieve content from Svenska Ordlista server #2.");		
	}
	
	$originalLength = strlen($def);
	
	// use "snr" id to go to the relevent lemma information
	if (strlen($snr) > 0) {
		$start = strpos($def,$snr);
		if (!$start) {
			echo "Could not find lemma information";
			return;
		}	
	}
	if (strlen($enum) > 0) {
		$start = strpos($def,"<sup>" . $enum);
		$end = strpos($def, "lemvarhuvud",$start+1);
		if (!$end) $end = strlen($def);
		if ($start != -1) $def = substr($def,$start,$end-$start);	
	}
	
	if (!$def) {
		error("Failed to get lemma for id: " . $id);
		return;
	}

	$lemmaLength = strlen($def);
	if ($lemmaLength === 0) {
		error("Failed to get lemma Err #1");
		return;
	}
	
	// Line-by-line analysis often easier way to extract relevant information.	
	$defLines = explode("\n",$def);		

	if (count($defLines) < 6) {
		error("Failed to get lemma Err #2");
		return;
	}
	
	$wordClass = $tmpClass;			

	if ($class === "slutled") $word = "-" . $word;
	if ($class === "förled") $word = $word . "-";	
	/* Find all the word roots, e.g. 'ge','giva'
	* Find all the conjugation groups	
	* And need the text seperator between conjugation groups
	*/ 
	$split = "";
	$conjLines = array();
	$wordRootLines = array();
	$splitLines = array();	
	foreach($defLines as $line) {
		$conjugations = "";			
		if (str_contains($line, 'class="bojning_inline"')) {					
			array_push($conjLines, conjugateToString($line));
		} else if (str_contains($line, 'class="subtype">')) {
			array_push($splitLines, cut($line,'class="subtype">',"</div>"));
		} else if (str_contains($line, 'class="orto">')) {
			array_push($wordRootLines, cut($line,'class="orto">',"</span>"));			
		}
	}	
	$i=0;
	$tmpConj = "";			
	foreach($conjLines as $c) {
		$tmpConj .= $wordRootLines[$i] . " " . $c;
		if ($i < count($splitLines)) {			
			$tmpConj .= " " . $splitLines[$i] . " ";
		}
		$i++;
	}	
	$tmpConj = str_replace("  ", " ", $tmpConj); // Hack
	$conjDelim = "";
	if (strlen($tmpConj) === 0) {
		foreach($wordRootLines as $el) {
			$tmpConj .= $conjDelim . $el;
			$conjDelim = " eller ";
		}
	}
	$out['meta'] = $tmpConj;	
	// Hack
	$out['meta'] = str_replace("</span>","",$out['meta']);
	$out['meta'] = str_replace("</div>","",$out['meta']);
			
	$out['def'] = getDef($def, $defLines);		
	$out['more'] = getMore($def);	
		
	
	// No dashes in word keys
	$word = str_replace("?","-",$word);
	$out['key'] = $word;
	echo json_encode($out);
	
	// Helper function to ease substring extraction
	function cut($in,$start,$end) {
		$out = "";	
		if ($in && $start && $end) {
			$pos1 = strpos($in, $start);
			if ($pos1 || $pos1 === 0) {				
				$pos2 = strpos($in,$end,$pos1);
				if ($pos2) {
					$len = strlen($start);
					$pos1 += $len;
					$out = substr($in, $pos1, $pos2 - $pos1);
				}
			}
		}
		return $out;
	}
		
	function getSingleLemma($def, $start, $END) {		
		// END is the global end of Lemma content after all "n" lemmas.
		// First lemvar after ordklass is the next lemma
		// Always parse past "ordklass" as before seeking end of lemma
		
		$pastStart = strpos($def, "ordklass",$start);
		if (!$start) return "";
		$end = strpos($def,"lemvar",$pastStart);				
		// Case where this is the last lemma		
		if (!$end) {			
			$end = strpos($def,$END,$start) + strlen($END);			
		}
		$out = substr($def,$start, $end - $start);				
		return $out;
	}
	
	function getClass($def,$find,$isSpan=false) {		
		$found = array();
		if ($isSpan) {
			$end = "</span>";
		} else {			
			$end = "</div>";		
		}				
		$find = 'class="' . $find . '">';
		$len = strlen($find);
		$lenEnd = strlen($end);		
		$pos1 = strpos($def, $find);				
		if ($pos1 || $pos1 === 0) {
			$pos2 = strpos($def, $end, $pos1);
			if ($pos2) {
				$pos2 += $lenEnd;
					
				$start = $pos1 + $len;
				$lenFound = $pos2 - $start;			
				if ($len > 3) {						
					array_push($found, substr($def, $start, $lenFound));
				}
				
				while ($pos1 >= 0) {									
					// Advance to find any remaining matches
					$pos1 = strpos($def, $find, $pos1 + 1);
					if (!$pos1) break;
					$pos2 = strpos($def, $end, $pos2 + 1);						
					$start = $pos1 + $len;
					$lenFound = $pos2 - $start;
					if ($lenFound > 3) {						
						array_push($found, substr($def, $start, $lenFound));				
					}					
				}
			}
					
			return $found;
		}
	}
	
	/* Rely on audio files for pronunciation guides
	function getPronunciation($def) {
		$tmp = "";
		$block = getClass($def,"uttalblock");
		
		if ($block) {
			$block = $block[0];			
			$tmp = strip_tags($block);
			$tmp = str_replace("\n", "", $tmp);			
			$tmp = "<br>" . $tmp;			
		}
		return $tmp;
	}	
	*/
	function getCykel($def,$find,$start) {		
		$out['found'] = "";
		$out['pos'] = null;
		$find = '"' . $find . '">';
		$cykel = getClass($def, "cykel");
		if ($cykel) {
			foreach($cykel as $el) {
				
				$utv = getClass($el, "utv",true);
				if ($utv) {					
					foreach ($utv as $match) {
						$tmp = strip_tags($match);								
						if (strlen($tmp) > 3) $out['found'] = $out['found'] . "○ " . $tmp . "<br>";
					}
				}
				$syntExBlock = getClass($tmp, "sxblocklx");
				if ($syntExBlock) {					
					$out .= strip_tags($syntExBlock[0]);
				}
			}
		}
		
		return $out;
	}
	
	function conjugateToString($line) {		
		// Strip out all conjugation text from line and concatenate to a string
		$out = "";
		$tmpArr = array();
		if (strlen($line) > 0) {			
			// Find by span
			// Remove leading bojning_inline class if present
			$line = preg_replace('/"bojning_inline" id="boj[\d]+">/',"", $line);
		
			$find = '">';		
			$lenFind = strlen($find);
			$close = "</span>";
			$start = strpos($line,$find);
			// First span
			if ($start) {				
				$end = strpos($line, $close,$start);
				if ($end) {					
					$start = $start + $lenFind;
					array_push($tmpArr,substr($line,$start,$end - $start));					
				}
				
			}		
			// All other spans
			$start = strpos($line,$find,$end);
			while ($start) {
				$end = strpos($line, $close,$start);
				if ($end) {
					$start = $start + $lenFind;
					array_push($tmpArr,substr($line,$start,$end - $start));
				}
				$start = strpos($line,$find,$end);
			}
					
			if ($tmpArr) {			
				for ($i = 0; $i < count($tmpArr); $i++) {
					$out .= " " . $tmpArr[$i];
				}
			}
			// Hack
			$out = str_replace(" ,",",",$out);	
		}		
		return $out;		
	}
	
	function getLinkTexts($link) {		
		$out = "";		
		$startLink = "hvtag";
		$startWord = "hvord";
		$regex='/&amp;ref=kcnr\d*/'; // Some entries have extra info, drop this info.
		$link = preg_replace($regex,"", $link);
		$arr = explode(";", $link);
		$del = "";
		foreach($arr as $el) {			
			if (str_contains($el, "hvtag")) {	
				$tmp = strip_tags($el);
				$out .= $del . "<l>" . $tmp . "</l>";
			} else if (str_contains($el, "hvord")) {
				$out .= $del . strip_tags($el);
			}
			$del = "; ";
		}
		return $out;		
	}
	
	function getDef($raw,$defLines) {		
		$out = "";
		$start = 'class="kernel"';
		$len = strlen($start);		
		$pos1 = strpos ($raw, $start);
		
		if ($pos1) {
			// Always expect one definition
			$def = substr($raw, $pos1-6);
			$out .= getDefFields($def, $pos1);			
		}
		$out = str_replace("<br><l>","<l>",$out);
		if (strlen($out) === 0) {
			error("Failed to get 'def' information");
		} else {
			return $out;
		};
	}
	
	function getMore($raw) {		
		$out = "";
		$start = '"detaljer">';
		$len = strlen($start);
		
		$pos1 = strpos ($raw, $start) + $len;
		if ($pos1) {
			// Always expect one definition
			$out = $out . getMoreFields($raw, $pos1);
			// Loop for additional definitions
			
			$pos1 = strpos($raw, $start, $pos1 + $len + 1);
			while ($pos1) {
				// Delimit with '||' to allow spliting of 'more' info according to associated definition
				$out = $out . "||" . getMoreFields($raw, $pos1);
				$pos1 = strpos($raw, $start, $pos1 + $len + 1);
			}
			
		}
		// Hack
		if (substr($out,0,1) === "\n") $out = substr($out,1);
		$out = str_replace("HISTORIK: <br>","HISTORIK: ",$out);
		$out = str_replace("\n", "<br>",$out);
		// Ugly hack to fix empty lines
		$out = str_replace("<br><br>", "<br>",$out);			
		$out = str_replace("<br><br>", "<br>",$out);
		$out = str_replace("EXEMPEL: ; ","EXEMPEL: ",$out); 		
		$out = str_replace(" KONSTRUKTION", "<br>KONSTRUKTION",$out);
		$out = str_replace(" >", "<br>>",$out);
		$out = str_replace("<br><br>", "<br>",$out);		
		$out = str_replace("<br> <br>", "<br>",$out);		
		// Remove any leading new line from first "more" entry.
		if (substr($out,0,4) === "<br>") $out = substr($out,4);
		// Remove leading new line from all other "more" entries.
		$out = str_replace("||<br>","||", $out);
		if (strlen($out) === 0) {
			error("Failed to get 'more' information");
		} else {
			return $out;
		}
	}
	
	function getDefFields($raw, $pos1) {						
		$out = "";	
		$raw = str_replace(' <span class="deft">', "\n" . '<span class="deft">',$raw);
		//echo $raw . "\n***\n";
		$tmpArr = explode("\n", $raw);		
		$delim = "";
		$skip = false;
		$particle = false;
		foreach($tmpArr as $l) {
			if (str_contains($l, "lös förbindelse")) $particle = true;
			if (str_contains($l, "fast sammansättn.")) $particle = true;
			if (str_contains($l, 'class="hv"')) $particle = false;
			//if (str_contains($l, "expansion collapsed")) break;
			if (str_contains($l,'</div>') || str_contains($l,"expansion collapsed")) $skip = true;
			if (str_contains($l,'class="kbetydelse"')) $skip = false;
			if (!$skip) {
				$tmp = processDefLine($l,$particle);
				if (strlen($tmp) > 0) {
					$out .= $delim . $tmp;
					$delim = "\n";
				}
			}
		}
		$out = str_replace("__\n","",$out);
		
		$out = str_replace("__ \n"," ",$out);
		$out = str_replace("\n__ "," ",$out);			
		
		$out = str_replace("\n","<br>",$out);
		$out = str_replace("det att <br>", "det att ",$out);
		if (str_contains($out, "det att")) $out = preg_replace("/\x{00a0}/u"," ",$out);
		if (str_contains($out, "fast sammansättn.")) $out = preg_replace("/\x{00a0}/u"," ",$out);
		if (str_contains(!$out,"lös förbindelse"))	$out = preg_replace("/\x{00a0}/u","",$out);
		
		// Removal of unwanted numerical references in cross-references.
		$out = preg_replace("/,[1-9]$/","",$out);
		$out = preg_replace("/ [1-9]/"," ",$out);

		$out = preg_replace("/,  /",", ",$out);
		$out = preg_replace("/ ,/",",",$out);
		
		$out = preg_replace("/[1-9],[1-9],[1-9]/","",$out);
		$out = preg_replace("/[1-9],[1-9]/","",$out);
		$out = preg_replace("/<l>[1-9]/","<l>",$out);
		$out = str_replace('\\',"\\\\",$out); // Hack to avoid (rare) backslashes in definitions.
		return $out;
		
	}
	
	function getGrammar($block) {

		$out = "";
				
		// Assuming only a single single present in grammar block
		$pos1 = strpos($block, "hvtag");
		$link = "";
		if ($pos1) {			
			$find = '">';
			$pos1 = strpos($block,'">',$pos1);
			if ($pos1) {					
				$pos1 += strlen($find);
				$pos2 = strpos($block,"</a>", $pos1);
				if ($pos2 && $pos2 > $pos1) $link = substr($block,$pos1,$pos2 - $pos1);
			}							
		}
		
		// Placeholder to later crowbar in bold syntax tags, exclude blocks that already contain links
		if (!str_contains($block,"fast sammansättn.") && (!str_contains($block,"lös förbindelse"))) {
			$block = str_replace('<span class="fkom2"><b>', " __b", $block);
			$block = str_replace("</b></span>","b__>",$block);
		}				
		$block = str_replace('<span class="fkom3">',"",$block);		
		$block = str_replace("skillnad</span>","", $block);
		
		$out = "";
		$out = strip_tags($block);			
		// Crowbar in bold syntax
		
		$out = str_replace("__b", "<b>",$out);
		$out = str_replace("b__>", "</b>",$out);

		$out = str_replace("\n" ,"", $out);
		$out = str_replace($link, " <l>" . $link . "</l>",$out);
	
		// Assume only one occurance of link textdomain
		if (strlen($out) > 0) {
			$out = " (" . $out . ") ";
		}
		return $out;
	}
	
	function getMoreFields($raw, $pos1) {				
		$out = "";
		// End is the div close of "etymologiblock"		
		$end = "etymologiblock";
		$pos2 = strpos($raw, $end, $pos1);
		$end = "</div>";
		$pos2 = strpos($raw, $end ,$pos2) + strlen($end);
		// Work on single "lexem" block
		$tmp = substr($raw,$pos1,$pos2-$pos1);
		$tmpArr = explode("\n",$tmp);		
		$divs = array();
		$tmp = array();		
		
		foreach($tmpArr as $l) {			
			if (strlen($l) > 0) {
				array_push($tmp,$l);
			}
			if (str_contains($l,"</div>")) {				
				array_push($divs, $tmp);
				$tmp = array();			
			}
		}
		
		
		$delim = "";
		$out = "";
		foreach($divs as $el) {
			$line = "";			
			foreach($el as $l) {					
				$tmp = processMoreLine($l);
				if (strlen($tmp) > 0) {
					if (strlen($line != 0) && (str_contains($tmp, "EXEMPEL:") || str_contains($tmp, "SAMMANSÄTTN."))) {
						$line .= "\n" . $tmp;
					} else {
						$line .= " " .$tmp;
					}
				}								
			}			
			if (strlen($line) > 0) {
				$out .= $delim . $line;
			}
			$delim = "\n";
		}
		
		$out = str_replace("\n \n","\n", $out);
		$out = preg_replace('/<l>[1-9]/',"<l>",$out);		
		$out = preg_replace('/[1-9]<\/l>/',"</l>",$out);		
		$out = preg_replace('/ <\/l>/',"</l>",$out);		
		$out = preg_replace('/ /'," ",$out);		
		return $out;
	}
	
	function processMoreLine($line) {			
		$ret = "";
		if (str_contains($line,'class="syntex">')) {
			$ret = "EXEMPEL: " . strip_tags($line);
		} else if (str_contains($line,'class="cbetydelse"')) {
			$ret = "○ ";
		} else if (str_contains($line,'class="utv"')) {
			$ret = "{" . strip_tags($line) . "}";
		} else if (str_contains($line,'class="def"')) {
			if (str_contains($line,'class="hvtag')) {
				$link = splitOutLink($line);				
				$ret = "<l>" . $link . "</l>" . str_replace($link, "",strip_tags($line));
			} else {
				$ret = strip_tags($line);
			}
		} else if (str_contains($line,'class="mxblocklx"')) {
			$ret = "SAMMANSÄTTN./AVLEDN.: " . getLinkTexts($line,'<a class="hvtag"');
		} else if (str_contains($line,'class="fkomblock"')) {
			$ret = "(" . strip_tags($line) . ")";
		} else if (str_contains($line, 'class="valens"')) {
			$ret = "KONSTRUKTION:\n";		
		} else if (str_contains($line,'class="idiom"')) {
			$ret = "- <lf>" . strip_tags($line) . "</lf>";				
		} else if (str_contains($line,'class="fb"')) {
			$ret = "HISTORIK: " . strip_tags($line);
		} else if (str_contains($line,'class="et"')) {
			$ret = strip_tags($line);
		} else if (str_contains($line,'class="hvtag')) {	
			$link = splitOutLink($line);
			if (strlen($link) > 0) {				
				$ret = "<l>" . $link . "</l>" . str_replace($link, "",strip_tags($line));
			} else {
				$ret = strip_tags($line);
			}
		} else if (str_contains($line,'class="hv"')) {
			$ret = strtoUpper(strip_tags($line));
		}
		
		if (str_contains($line,'class="vt"')) {
			if (str_contains($line,'class="expandvalens"') || $GLOBALS['kCount'] < 6) {
				if (str_contains($line,'class="expandvalens"')) {					
					$ret = " > " . hackCapitals(strip_tags($line)) . "\n";
				} else {
					if ($GLOBALS['kCount'] > 0) {
						$ret = "\n ○ " . hackCapitals(strip_tags($line));			
					} else {
						$ret = " ○ " . hackCapitals(strip_tags($line));			
					}
				}
				$GLOBALS['kCount']++;
			}
		} else {
			$GLOBALS['kCount'] = 0;
		}
		
		if ($GLOBALS["debug"]) {
			if (strlen($ret) > 0) {
				echo "consume: " . $line . "\n";
				echo $ret . "\n";
			} else {
				echo "discard: " . $line . "\n";
			}
		}
		
		$ret = str_replace("!!","",$ret); // hack as unsure what '!!' is representing for now.
		return $ret;
	}
	
	function processDefLine($line,$particle) {		
		$res = "";		
		if (str_contains($line, 'class="kbetydelse"')) {
			$res = strip_tags($line) . " __";
		} else if (str_contains($line, 'class="def"')) {
			if (str_contains($line, 'class="hvtag"')) {					
					$res = "<l>" . strip_tags($line) . "</l>";
				} else {
					$res = strip_tags($line);
				}	
		} else if (str_contains($line, 'class="hkom"')) {
			$res = "[" . strip_tags($line) . "] __";
		} else if (str_contains($line, 'class="fkomblock"')) {
			$line = str_replace("<b>"," _b",$line);
			$line = str_replace("</b>","b__",$line);
			$res = "(" . strip_tags($line);
			if (substr($res,-2) === "se") {
				$res .= " __";
			} else {
				$res .= " __";
			}
			$res = str_replace(" _b", " <b>",$res);
			$res = str_replace("b__", "</b>)",$res);
		} else if (str_contains($line, 'class="hv"') || str_contains($line, 'class="hvtyp"')) {
			$res = strip_tags($line) . " __";
		} else if (str_contains($line, 'class="hvtag"')) {
			$lineArr = explode(",",$line);
			$regex ='/\s\d/u';
			foreach($lineArr as $line) {
				//echo $line . "<br>";
				$mid = strpos($line,"</a>");
				$tmp = strip_tags(substr($line,0,$mid));
				$tmp = preg_replace($regex,"",$tmp);
				if (strlen($res) === 0) {
					$res .= "<l>" . $tmp . "</l>";
				} else {
					$res .= ", <l>" . $tmp . "</l>";
				}
				if ($particle) {
					$res .= ") __";
				}
			}
		} else if (str_contains($line, 'class="deft"')) {
			$res = "__ {" . strip_tags($line) . "}";
		}
		
		$res = str_replace("!!","",$res); // hack, not sure what the '!!' represents, so remove it for now.
		$LAST_LINE = $line;
		return $res;
	}
	
	function splitOutLink($line) {
		$start = strpos($line,"<a");
		$end = strpos($line, "</a>");
		if (($start === 0 || $start) && $end) {			
			$tmp = strip_tags(substr($line,$start,$end-$start));
			return $tmp;
		} else {
			return "";
		}
	}
	
	function hackCapitals($in) {
		$out = str_replace("någonstans", "NÅGONSTANS", $in);
		$out = str_replace("någons", "NÅGONS", $out);
		$out = str_replace("någon", "NÅGON", $out);
		$out = str_replace("något", "NÅGOT", $out);
		$out = str_replace("några", "NÅGRA", $out);
		$out = str_replace(" adj ", " ADJ ", $out);
		$out = str_replace(" sats", " SATS", $out);
		$out = str_replace("att+verb","att VERB", $out);
		$out = str_replace("/sats", "/SATS", $out);
		return $out;
	}
	
	function debug($in) {
		echo "*** " . $in . " ***\n";
	}
	
	function error($in) {
		$out["error"] = $in;
		echo json_encode($out);
		exit(-1);
	}	
	
	function strip($raw,$before="", $after=" ") {
		$out = "";
		if ($raw) {
			$out = $before . strip_tags($raw[0]) . $after;
		}
		return $out;
	}
	
	function listEl($arr,$preface, $delim = "<br>") {
		$out = "";
		if ($arr) {
			$out = $preface . ": ";
			foreach($arr as $m) {		
				$tmp = strip_tags($m);		
				if (strlen($tmp) > 3) $out = $out . $delim . $tmp;
			}
		}
		return $out;
	}
	
	function matchClass($read, $given) {		
		$res = false;		
		if ($read === "substantiv" && $given != "plural") {
			$res =  ($given === "substantiv_en" || $given === "substantiv_ett");
		} else if ($read === "adjektiviskt slutled") {
			$res = ($given === "slutled");			
		} else if ($given === "plural" && $read === "substantiv") {
			$res = true;
		} else if ($given === "adjektiv" && $read === "substantiverat adjektiv") {
			$res = true;			
		} else {			
			$res = $read === $given;
		}						
		return $res;
	}
	
	function getLongClassName($short) {
		// Default is no change
		$long = $short;
		switch ($short) {
			case "adj.":
				$long = "adjektiv";
				break;			
			case "subst.":
				$long = "substantiv";
				break;
			case "adv.":
				$long = "adverb";
				break;
		}
		return $long;
	}
	
	function breaker($txt) {
		echo $txt;
		exit();
	}
?>
