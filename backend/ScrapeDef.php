<?php
	$GLOBALS["debug"]=$_GET['debug'];
	$word = $_GET['word'];	
	$class = $_GET['class'];
	// return an array to support casees where multiple entries exist
	$out['meta'] = "";
	$out['def'] = "";
	$out['more'] = "";
	$urlBase = 'https://svenska.se/tri/f_so.php?sok=';	
	$urlBaseId = 'https://svenska.se/tri/f_so.php?id=';
	$url = str_replace(" ", "%20", $urlBase . $word);	
	$url = str_replace("ä", "%C3%A4", $url);
	$ch = curl_init();		
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch,CURLOPT_URL,$url);
	curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
	curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:109.0) Gecko/20100101 Firefox/112.0");				
	$def = curl_exec($ch);			
	curl_close($ch);	
	// Remove all "mdr" class lines from def. These are not interesting to us
	$defArr = explode("\n", $def);
	$del = "";
	$def = "";
	foreach($defArr as $el) {
		if (!str_contains($el,'class="mdr"')) {						
			$def .= $del . $el;
		}
		$del = "\n";
	}
	$defArr = explode("\n", $def);
	if (strlen($def) === 0) {
		error("Failed to retrieve content from Svenska Ordlista server #1.");
	}
	// Check this resolves to the correct word
	$find = 'class="slank" href="';
	$find = 'class="slank';
	if (strpos($def,$find)) {
		// Need to do some more work to resolve to word matches
		// Use id instead of word as key
		$tmpLines = explode("\n", $def);
		$tmpClass = "";
		$isMatch = false;
		foreach($tmpLines as $l ) {			
			// Discard slutled is slutled is not desired
			$keep = $class === "slutled" || !str_contains($l, "</span>-");
			if (str_contains($l, "wordclass") && $keep) {												
				$tmpClass = getClass($l,"wordclass", true); // Discard trai
				if ($tmpClass) {					
					$tmpClass = str_replace("</span>","",$tmpClass);
					$tmpClass = substr($tmpClass[0],1);
					// Remove leading space from word class entry
					$tmpClass = getLongClassName($tmpClass);				
					if (matchClass($tmpClass, $class)) {
						$def = $l;
						$isMatch = true;						
						break;
					}
				}
			}
		}		
		
		if ($isMatch) {			
			$find = "/?id=";
			$pos1 = strpos($def,$find);
			$find2="&pz=3";
			$pos2 = strpos($def,$find2);
			if ($pos1 && $pos2 && $pos2 > $pos1) {
				$pos1 = $pos1 + strlen($find);
				$lenFound = $pos2 - $pos1;
				$id = substr($def,$pos1,$lenFound);
				$url = $urlBaseId . $id;
				$ch = curl_init();		
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
				curl_setopt($ch,CURLOPT_URL,$url);
				curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
				curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:109.0) Gecko/20100101 Firefox/112.0");			
				$def = curl_exec($ch);			
				curl_close($ch);			
			}
		}
	}	
	
	if (strlen($def) === 0) {
		error("Failed to retrieve content from Svenska Ordlista server #2.");		
	}
	
	$originalLength = strlen($def);
	// filter away uninteresting stuff
	$find = 'class="orto">';
	$start = strpos($def,$find) - 2;
	// Global end of content marker
	$END = ">Till SO<";	
	$end = strpos($def,$END);	
	
	if ($end) {
		$end += strlen($END);
		
	} else {
		error("Failed to find end of lemma contents");
	}
	
	$filteredLength = $end - $start;		
	$def = substr($def, $start, $filteredLength);
		
	if (!$def) {
		error("Failed to remove unneeded content");
		return;
	}
		
	// Multiple lemma can exist - for now only scrape a single lemma that matches the current class.				
	$find = 'class="ordklass">';	
	$foundMatch = false;
	$pos1 = strpos($def, $find);
	
	if ($pos1 || $pos1 === 0) {		
		$pos2 = strpos($def, "</div>",$pos1);	
		if ($pos2) {			
			$tmpClass = str_replace($find,"",substr($def, $pos1, $pos2-$pos1));								
			if (matchClass($tmpClass, $class)) {				
				$find = 'class="orto">';
				$pos1 = strpos($def, $find);				
				if ($pos1) {
					$foundMatch = true;
					$def = getSingleLemma($def, $pos1, $END);
				}
			}
		}
	}		
		
	$find = 'class="ordklass">';	
	while ($pos1 && $foundMatch === false) {					
		$pos1 = strpos($def, $find, $pos1 + 1);		
		if ($pos1 || $pos1 === 0) {
			$pos2 = strpos($def, "</div>",$pos1);
			if ($pos2) {
				$tmpClass = str_replace($find,"",substr($def, $pos1, $pos2-$pos1));						
				if (matchClass($tmpClass, $class)) {
					$find = 'class="orto">';					
					if ($pos1) {
						$foundMatch = true;
						$pos1 -= 550; // Ugly hack to include conjugations also
						$def = getSingleLemma($def, $pos1,$END);
					}
				}				
			}
		}
	}
	
	
	if (!$foundMatch) {
		error(("Failed to retrieve word:" . $word . "; class: " . $class));
	}
	
	$lemmaLength = strlen($def);
	if ($lemmaLength === 0) {
		error("Failed to get lemma #1");
		return;
	}
	
	// Line-by-line analysis often easier way to extract relevant information.	
	$defLines = explode("\n",$def);		

	if (count($defLines) < 6) {
		error("Failed to get lemma #2");
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
	if (strlen($tmpConj) === 0) $tmpConj = $word;
	$out['meta'] = $tmpConj;
	$out['meta'] = $out['meta'] . "<br>" . $wordClass;
	// Hack
	$out['meta'] = str_replace("</span>","",$out['meta']);
	$out['meta'] = str_replace("</div>","",$out['meta']);
		
	$out['meta'] .= getPronunciation($def);	
	$out['def'] = getDef($def, $defLines);		
	$out['more'] = getMore($def);	
		
	
	// No dashes in word keys 
	//$word = str_replace("-","", $word);
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
				$out .= $del . "<l>" . strip_tags($el) . "</l>";
			} else if (str_contains($el, "hvord")) {
				$out .= $del . strip_tags($el);
			}
			$del = "; ";
		}
		return $out;		
	}
	
	function getDef($raw,$defLines) {		
		$out = "";
		$start = '"kernel"';
		$len = strlen($start);
		
		$pos1 = strpos ($raw, $start);
		
		if ($pos1) {
			// Always expect one definition
			$out .= getDefFields($raw, $pos1);
			
			// Loop for additional definitions
			$pos1 = strpos($raw, $start, $pos1 + $len + 1);
			while ($pos1) {
				$out .= "<br>" . getDefFields($raw, $pos1);
				$pos1 = strpos($raw, $start, $pos1 + $len + 1);
			}
		}
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
		if (strlen($out) === 0) {
			error("Failed to get 'more' information");
		} else {
			return $out;
		}
	}
	
	function getDefFields($raw, $pos1) {				
		$out = "";
		$end = "expansion collapsed";
		$pos2 = strpos($raw, $end, $pos1);		
		// Work on single "lexem" block
		$tmp = substr($raw,$pos1,$pos2-$pos1);
		
		$firstChar = getClass($tmp, "punkt", true);			
		$out .= strip($firstChar);
		$context = getClass($tmp,"hkom", true);
		$out .= strip($context, "[","] ");		
		$defLines = explode("\n", $tmp);
		
		$consume = false;
		$grammar = "";
		foreach($defLines as $l) {
			if (str_contains($l, "fkomblock")) $consume = true;												
			if ($consume) {				
				$grammar .= $l;				
			}
			// Stop consuming grammar information after first close span after fkomlbock
			if (str_contains($l, "</span>")) $consume = false;;
			
		}		
		
		$out .= getGrammar($grammar);						
		
		// Nain definition content						
		$defTxt = getClass($tmp, "def", true);														
		if ($defTxt) {
			$defTxt = $defTxt[0];
			$pattern = '/(_\d+&(?:amp;ref=lnr\d+)+)/';
			// Some stray information is sometimes present that can be removed e.g. "/so/?id=161228_1&amp;ref=lnr279219" instead of clean "/so/?id=161228"		
			$defTxt = preg_replace($pattern,"",$defTxt);				
			preg_match($pattern, $defTxt, $matches);		
			
			// 'hvhomo' class is some kind of link, treat accordingly	
			$pattern = '/<span class="hvhomo"><\/span>([a-zöäå\s]+)/';						
			
			preg_match($pattern, $defTxt, $matchesHomo);			
									
			$pattern = '/<a class="hvtag" target="_parent" href="\/so\/\?id=\d+">([a-zöäå\s]+)+/';						
			preg_match($pattern, $defTxt, $matches);			
			
			$defTxt = strip_tags($defTxt);
			if ($matchesHomo) {
				$defTxt = preg_replace($pattern,"<l>" . $matches[1] . "</l>",$defTxt);
			}
			
			if ($matches) {						
				$defTxt = str_replace($matches[1], "<l>" . $matches[1] . "</l>",$defTxt);			
			}
						
			$out .= $defTxt;
			// Secondary definition
			$deftTxt = getClass($tmp, "deft", true);
			$out .= strip($deftTxt,"{","} ");	
			
			// Handle linked words
			$hasLinks = getClass($tmp, "hv");		
			if ($hasLinks) {			
				$hasLinks = $hasLinks[0];			
				
				$arr = explode("\n",$hasLinks);			
				
				$tmp = "";
				$firstLoop = true;
				foreach($arr as $el) {								
					$lastWasWord = false;				
					if (str_contains($el, "hvtyp")) {					
						if (!$firstLoop) $tmp .= "<br>";
						$tmp .= strip_tags($el);
						$lastWasWord = false;
					} else if (str_contains($el, "hvtag")) {													
						if ($lastWasWord) {
							$tmp .= ", ";
						} else {
							$tmp .= " ";
						}
						$tmp .= strip_tags($el);					
						$lastWasWord = true;
					}		
					$firstLoop = false;
				}
				if (strlen($tmp) > 0) $out .= "<br>" . $tmp;
			}
		}
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
				$tmp = processLine($l);
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
				
		return $out;
	}
	
	function processLine($line) {		
		$ret = "";
		if (str_contains($line,'class="syntex">')) {
			$ret = "EXEMPEL: " . strip_tags($line);
		} else if (str_contains($line,'class="cbetydelse"')) {
			$ret = "○ ";
		} else if (str_contains($line,'class="utv"')) {
			$ret = "{" . strip_tags($line) . "}";
		} else if (str_contains($line,'class="def"')) {			
			$ret = strip_tags($line);
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
		} else if (str_contains($line,'class="hvtag"')) {			
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
		return $ret;
	}
	
	function splitOutLink($line) {
		$start = strpos($line,"<a");
		$end = strpos($line, "</a>");
		if (($start === 0 || $start) && $end) {			
			return strip_tags(substr($line,$start,$end-$start));
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
		if ($read === "substantiv") {
			$res =  ($given === "substantiv_en" || $given === "substantiv_ett");
		} else {
			$res = $read === $given;
		}		
		if ($given === "plural" && $read === "substantiv") {
			$res = true;
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
?>