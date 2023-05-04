<?php
	$word = $_GET['word'];	
	$class = $_GET['class'];
	// return an array to support casees where multiple entries exist
	$out['meta'] = "";
	$out['def'] = "";
	$out['more'] = "";
	$urlBase = 'https://svenska.se/tri/f_so.php?sok=';	
	$urlBaseId = 'https://svenska.se/tri/f_so.php?id=';
	$url = str_replace(" ", "%20", $urlBase . $word);	
	$ch = curl_init();		
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch,CURLOPT_URL,$url);
	curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
	curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:109.0) Gecko/20100101 Firefox/112.0");			
	$def = curl_exec($ch);			
	curl_close($ch);
			
	if (strlen($def) === 0) {
		debug("Failed to retrieve content from Svenska Ordlista server #1.");
		exit();
	}
	// Check this resolves to the correct word
	$find = 'class="slank" href="';
	if (strpos($def,$find)) {
		// Need to do some more work to resolve to word matches
		// Use id instead of word as key
		$find = "/?id=";
		$pos1 = strpos($def,$find);
		$find2="&pz=3";
		$pos2 = strpos($def,$find2);
		if ($pos1 && $pos2 && $pos2 > $pos1) {
			$pos1 = $pos1 + strlen($find);
			$lenFound = $pos2 - $pos1;
			$id = substr($def,$pos1, $lenFound);			
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
	
	if (strlen($def) === 0) {
		debug("Failed to retrieve content from Svenska Ordlista server #2.");
		exit();
	}
	// filter away uninteresting stuff
	$find = 'class="orto">';
	$start = strpos($def,$find) - 2;
	// Global end of content marker
	$END = ">Till SO<";	
	$end = strpos($def,$END);	
	
	if ($end) {
		$end += strlen($END);
	}
	
	$len = $end - $start;	
	$def = substr($def, $start,$len);

	if (!$def) {
		debug("Failed to remove unneeded content");
		return;
	}
	
	
	
	// Multiple lemma can exist - for now only scrape lemma that match current class.				
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
					$foundMatch = true;				;
					$def = getSingleLemma($def, $pos1, $END);
				}
			}
		}
	}
	
	$find = 'class="ordklass">';	
	while ($pos1 && $foundMatch === false) {			
		$pos1 = strpos($def, $find, $pos1 + strlen($find) + 1);		
		if ($pos1 || $pos1 === 0) {
			$pos2 = strpos($def, "</div>",$pos1);
			if ($pos2) {
				$tmpClass = str_replace($find,"",substr($def, $pos1, $pos2-$pos1));						
				if (matchClass($tmpClass, $class)) {
					$find = 'class="orto">';
					$pos1 = strpos($def, $find);				
					if ($pos1) {
						$foundMatch = true;
						$def = getSingleLemma($def, $pos1,$END);
					}
				}				
			}
		}
	}
	
	if (!$foundMatch) {
		echo json_encode("Failed to retrieve word class: " . $class);
		return;	
	}
	// Line-by-line analysis often easier way to extract relevant information.
	$defLines = explode("\n",$def);	
	
	if (count($defLines) < 6) {
		debug("Failed to get lemma");
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
		// END is required as end marker varies between words (php returned html versus pure html). 
		$end = strpos($def, "ordklass");
		// First lemvar after ordklass is the next lemma
		if ($end) {
			$end = strpos($def,"lemvar",$end);		
		}
		// Case where this is the last lemma		
		if (!$end) {			
			$end = strpos($def,$END,$start);			
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
		$pos1 = strpos($def, $find);				
		if ($pos1 || $pos1 === 0) {
			$pos2 = strpos($def, $end, $pos1) + strlen($end);
					
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
						$out['found'] = $out['found'] . "○ " . strip_tags($match) . "<br>";
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
			$out .= getDefFields($raw, $pos1,$defLines);
			
			// Loop for additional definitions
			$pos1 = strpos($raw, $start, $pos1 + $len + 1);
			while ($pos1) {
				$out .= "<br>" . getDefFields($raw, $pos1,$defLines);
				$pos1 = strpos($raw, $start, $pos1 + $len + 1);
			}
		}
		if (strlen($out) === 0) {
			debug("Failed to get 'def' information");
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
		$out = str_replace("<br><br>", "<br>",$out);			
		if (strlen($out) === 0) {
			debug("Failed to get 'more' information");
		} else {
			return $out;
		}
	}
	
	function getDefFields($raw, $pos1,$defLines) {				
		$out = "";
		$end = "expansion collapsed";
		$pos2 = strpos($raw, $end, $pos1);		
		// Work on single "lexem" block
		$tmp = substr($raw,$pos1,$pos2-$pos1);
		
		$firstChar = getClass($tmp, "punkt", true);			
		$out .= strip($firstChar);
		$context = getClass($tmp,"hkom", true);
		$out .= strip($context, "[","] ");		
		foreach($defLines as $l) {
			if (str_contains($l, "fkomblock")) {
				$grammar = $l;
				$out .= getGrammar($grammar);		
				break;
			}
		}		
		
		$defTxt = getClass($tmp, "def", true);		
		$pattern = '/href="\/so\/\?id=[\d]+">([a-zöäåA-ZÖÄÅ]+)<\/a>/';		
		if ($defTxt) {			
			$defTxt = $defTxt[0];			
			$link = preg_match($pattern,$defTxt,$matches);
			if ($link) {
				$link = $matches[1];				
			}	
			
		}
		$defTxt = strip_tags($defTxt);
		if ($link) {			
			$defTxt = str_replace($link, "<l>" . $link . "</l>",$defTxt);			
		}
		
		$out .= $defTxt;
		// Secondary definition
		$deftTxt = getClass($tmp, "deft", true);
		$out .= strip($deftTxt,"{","} ");	
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
		
		// First set of compound words.
		$compound = getClass($tmp, "mxblocklx");		
		
		if ($compound) {		
			$links = getLinkTexts($compound[0], '<a class="hvtag"');						
			$out = "SSAMMANSÄTTN./AVLEDN.: ";
			
			$out = "SAMMANSÄTTN./AVLEDN.: " . $links . "<br>";
		}

		// Konstruction			
		
		$konst = getClass($tmp,"valens");				
		if ($konst) {
			$compound = strpos($konst[0],"expandvalens");
			if ($compound) {
				$out .= compoundConstruction($tmp);
			} else {
				$out .= simpleConstruction($tmp);
			}
		}
		
		$syntEx = getClass($tmp, "syntex", true);
		$out .= listEl($syntEx, "EXEMPEL","; ");
		if (strlen($out) > 0) $out .= "<br>";
		
		$cyclic = getCyclicFields($tmp);
		if ($cyclic) $out .= $cyclic;
				
		$history = getClass($tmp, "etymologiblock");
		
		if ($history) {						
			$fb = getClass($history[0],"fb", true);
			$tmpHist = listEl($fb, "HISTORIK");
			
			$et = getClass($history[0],"et");			
			if ($et) {			
				$et = explode("\n", $et[0]);
				$del = " ";
				foreach ($et as $m) {	
					if (str_contains($m, "hvtag")) {
						$pos1 = strpos($m,">");
						if ($pos1) {
							$pos2 = strpos($m, "</a>",$pos1);
							if ($pos2 && $pos2 > $pos1) {								
								$tmpM = $del . "<l>" . substr($m,$pos1 + 1, $pos2 - $pos1 + 1) . "</l>";
							}
						}
						$del = ", ";
					} else if (str_contains($m, "hvord")) {						
						$pos1 = strpos($m,">");
						if ($pos1) {
							$pos2 = strpos($m, "</span>",$pos1);
							if ($pos2 && $pos2 > $pos1) {								
								$tmpM = $del . substr($m,$pos1 + 1, $pos2 - $pos1 + 1);
							}
						}
						$del = ", ";
					} else {
						$lastWasWord = false;
						$tmpM = strip_tags($m);
						$del = " ";
					}
					$tmpHist .= $tmpM;
				}
			}
			
			$tmpHist = str_replace(";<br>", "; ",$tmpHist);
			$out .= $tmpHist;
		}
		
		return $out;
	}
	
	function simpleConstruction($tmp) {
		$out = "";
		$konst = getClass($tmp,"valens");		
		if ($konst) {			
			$out = $out . listEl($konst, "KONSTRUKTION");			
		}
		$out = hackCapitals($out);
		return $out;
	}
	
	function compoundConstruction($tmp) {
		//
		$out = "";
		$pos1 = strpos($tmp, "expandvalens");
		if ($pos1) {		
			$pos2 = strpos($tmp, "details",$pos1);			
			if ($pos2 && $pos2 > $pos1) {				
				$tmp = substr($tmp, $pos1, $pos2-$pos1);
				$tmpArr = explode("\n", $tmp);
				$del = "";
				$out = "KONSTRUKTION: \n";
				foreach($tmpArr as $el) {					
					if (str_contains($el,'class="vt"') && !str_contains($el, "<summary>")) {
						$out .= $del . strip_tags($el);						
						$del = "\n";
					}					
				}
				$out .= "\n";
			}
		}
		// Hack
		$out = hackCapitals($out);		
		return $out;
	}
	
	function hackCapitals($in) {
		$out = str_replace("någonstans", "NÅGONSTANS", $in);
		$out = str_replace("någon", "NÅGON", $out);
		$out = str_replace("något", "NÅGOT", $out);
		$out = str_replace("några", "NÅGRA", $out);
		return $out;
	}
	
	function debug($in) {
		echo "*** " . $in . " ***\n";
	}
	
	function getCyclicFields($raw) {
		
		// This pattern can be repeated many times.
		// Want to consume all repititions systematically
		$out = "";
		$start = '<div class="cykel">';
		$end = '<div class="etymologiblock">';		
		$pos1 = strpos($raw,$start);
		while ($pos1) {
			$pos2 = strpos($raw,$start,$pos1 + 1); // Range goes to next "cykel" div
			if (!$pos2) $pos2 = strpos($raw,$end,$pos1 + 1); // Last "cykel" def extends to end of lemma
 			if ($pos2) {
				$tmp = substr($raw,$pos1, $pos2 - $pos1);				
				
				$point = getClass($tmp, "punkt", true);
				
				if ($point) {						
					$out .= strip_tags($point[0]) . " ";
				}
				
				$utv = getClass($tmp,"utv", true);
				if ($utv) {
					$out .= strip($utv);
				}
				
				$context = getClass($tmp,"hkom", true);
				$out .= strip($context, "[","] ");
				
				$grammar = getClass($tmp,"fkomblock", true);
				$out .= strip($grammar,"(",") ");	
				
				$def = getClass($tmp,"def", true);
				if ($def) {					
					$out .= strip($def) . "<br>";
				} else {
					$out .= "<br>";
				}					
				
				$konst = getClass($tmp,"valens");				
				if ($konst) {
					$compound = strpos($konst[0],"expandvalens");
					if ($compound) {
						$out .= compoundConstruction($tmp);
					} else {
						$out .= simpleConstruction($tmp);
					}
				}
				
				$syntEx = getClass($tmp, "syntex", true);
				$out .= listEl($syntEx, "EXEMPEL","; ") . "<br>";
				
				$links = getClass($tmp,"mxblocklx");
				if ($links) {
					foreach($links as $m) {
						$out .= "SAMMANSÄTTN./AVLEDN.: " . getLinkTexts($m,'<a class="hvtag"') . "<br>";
					}
				}
				
			}
			$pos1 = strpos($raw,$start,$pos1 + 1);
		}		
		return $out;				
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
				$out = $out . strip_tags($m) . $delim;
			}
		}
		return $out;
	}
	
	function matchClass($read, $given) {
		if ($read === "substantiv") {
			return ($given === "substantiv_en" || $given === "substantiv_ett");
		} else {
			return $read === $given;
		}
	}
?>