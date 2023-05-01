<?php
	$word = $_GET['word'];
	$class = $_GET['class'];
	// return an array to support casees where multiple entries exist
	$out['meta'] = "";
	$out['def'] = "";
	$out['more'] = "";
		
	$def =  file_get_contents('https://www.svenska.se/tri/f_so.php?sok=' . $word);
	// Check this resolves to the correct word
	$find = 'class="slank" href="';
	if (strpos($def,$find)) {
		// Need to do some more work to resole to word matches
		$pos1 = strpos($def,$find);
		$find2="&pz=3";
		$pos2 = strpos($def,$find2);
		if ($pos1 && $pos2 && $pos2 > $pos1) {
			$pos1 = $pos1 + strlen($find);
			$lenFound = $pos2 - $pos1;
			$ext = substr($def,$pos1, $lenFound);
			$def =  file_get_contents('https://svenska.se/' . $ext);		
		}
	}
	
	// filter away uninteresting stuff
	$find = 'class="orto">';
	$start = strpos($def,$find) - 2;
	$find = 'alfabeta';
	
	$end = strpos($def,$find);
	$len = $end - $start;
	$def = substr($def, $start,$len);
	
	if (!$def) return;
	
	// Multiple lemma can exist - for now only scrape lemma that match current class.		
	$wordClasses = array();
	$posArr = array();
	$find = 'class="orto">';	
	$foundMatch = false;
	$pos1 = strpos($def, $find);
	
	if ($pos1 || $pos1 === 0) {
		$pos2 = strpos($def, "</div>",$pos1);		
		if ($pos2) {			
			$tmpClass = str_replace($find,"",substr($def, $pos1, $pos2-$pos1));		
			if (matchClass($tmpClass, $class)) {
				$foundMatch = true;
				$def = getSingleLemma($def, $pos1);
			}
		}
	}
	
	while ($pos1 && $foundMatch === false) {			
		$pos1 = strpos($def, $find, $pos1 + strlen($find) + 1);		
		if ($pos1 || $pos1 === 0) {
			$pos2 = strpos($def, "</div>",$pos);
			if ($pos2) {
				$tmpClass = str_replace($find,"",substr($def, $pos1, $pos2-$pos1));		
				if (matchClass($tmpClass, $class)) {
					$foundMatch = true;
					$def = getSingleLemma($def, $pos1);
				}				
			}
		}
	}	
	// Get word class
	$find = "ordklass";	
	
	$wordClass = getClass($def,$find);
	if (!$wordClass) {
		echo json_encode("Failed to retrieve word class");
		return;	
	}
	$wordClass = $wordClass[0];
	
	$find = 'class="orto">';	
	if ($class === "slutled") $word = "-" . $word;
	if ($class === "förled") $word = $word . "-";
	
		
	// Find all the conjugations
	$find = "bojning";	
	$conjugations = getClass($def,$find, true);	
	$out['meta'] = conjugate($word, $conjugations);
	$out['meta'] = $out['meta'] . "<br>" . $wordClass;
	// Hack
	$out['meta'] = str_replace("</span>","",$out['meta']);
	$out['meta'] = str_replace("</div>","",$out['meta']);
	
	$find = "uttal";
	$out['meta'] = $out['meta'] . "<br>" . getPronunciation($def);
	$out['def'] = getDef($def);		
	$out['more'] = getMore($def);
	echo json_encode($out);
	
	function getSingleLemma($def, $start) {
		return substr($def,$start - 6, strpos($def,"lemvar",$start) - $start);
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
		$found = "";
		$find = '="uttal"';
		$len = strlen($find);
		$pos1 = strpos($def, $find);
		if ($pos1) {
			$pos2 = strpos($def,"ljudfil",$pos1);
			$lenFound = $pos2 - $pos1 + $len;	
			$found = substr($def,$pos1 + $len + 1,$lenFound);			
			$found = strip_tags($found);			
		}
		
		return $found;
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
	
	function conjugate($word, $arr) {
		$tmp = $word;
		if ($arr) {
			 $tmp = $tmp . " ";		
			for ($i = 0; $i < count($arr); $i++) {
				$tmp = $tmp . " " . $arr[$i];
			}
		}
		return $tmp;
	}
	
	function getLinkTexts($link,$start) {		
		$found = array();
		$pos1 = strpos($link,$start);		
		$len = strlen($start);
		if (!$pos1) return;		
		$pos2 = strpos($link, "</a>");		
		if ($pos2) {
			$text = substr($link,$pos1, $pos2-$pos1+4);			
			array_push($found,strip_tags($text));
		}
		
		$pos1 = strpos($link,$start,$pos1 + $len + 1);		
		while ($pos1) {			
			if ($pos1) {				
				$pos2 = strpos($link, "</a>",$pos1);
				if ($pos2) {
					$text = substr($link,$pos1, $pos2-$pos1+4);
					array_push($found,strip_tags($text));
				}
			}
			$pos1 = strpos($link,$start,$pos1 + $len + 1);
		}
		$out = "";
		$del = "";
		
		foreach($found as $m) {
			$out .= $del . "<l>" . $m. "</l>";
			$del = ", ";
		}		
		return $out;
	}
	
	function getDef($raw) {		
		$def = "";
		$start = '"kernel"';
		$len = strlen($start);
		
		$pos1 = strpos ($raw, $start);
		
		if ($pos1) {
			// Always expect one definition
			$def = $def . getDefFields($raw, $pos1);
			
			// Loop for additional definitions
			$pos1 = strpos($raw, $start, $pos1 + $len + 1);
			while ($pos1) {
				$def = $def . "<br>" . getDefFields($raw, $pos1);
				$pos1 = strpos($raw, $start, $pos1 + $len + 1);
			}
		}
		
		return $def;
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
		return $out;
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
		$grammar = getClass($tmp,"fkomblock", true);
		$out .= strip($grammar,"(",") ");
		
		
		$defTxt = getClass($tmp, "def", true);
		$out .= strip($defTxt);
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
			$out .= "<br>" . $tmp;
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
		$out .= listEl($syntEx, "EXEMPEL","; ") . "<br>";
		
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
								$tmpM = $del . "<l>" . substr($m,$pos1 + 1, $pos2 - $pos1 + 1) . "</l> ";
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
		$out = str_replace("någon", "NÅGON", $in);
		$out = str_replace("något", "NÅGOT", $out);
		$out = str_replace("några", "NÅGRA", $out);
		return $out;
	}
	
	function printStr($in) {
		echo "***" . $in . "***";
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
			$out = "<b>" . $preface . "</b>: ";
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