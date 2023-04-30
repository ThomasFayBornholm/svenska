<?php
	$word = $_GET['word'];
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
	$context = getSubStr($def, "hkom", true);
	if ($context) {
		$out['def'] = $out['def'] . "[" . $context[0] . "] ";
	}
	$grammar = getSubStr($def, "fkomblock", true);
	if ($grammar) {
		$out['def'] = $out['def'] . "(" . $grammar[0] . ") ";
	}
	$out['def'] = $out['def'] . strip_tags(getSubStr($def,"def", true)[0]);	
	// Secondary definition information
	$deft = getSubStr($def,"deft", true);		
	if ($deft) $out['def'] = $out['def'] . " {" . $deft[0] . "}";
	// Link type (only check the first span)
	$hv = getSubStr($def,"hv",true);	
	$type = "";
	$comp = ["SYN.","JFR", "MOTSATS", "SE"];
	if ($hv) {		
		foreach($hv as $match) {			
			$match = strip_tags($match);			
			$type = $match;
			$hvWord = getSubStr($def,"hv");
			if ($hvWord) {
				foreach($hvWord as $match) {				
					$res = strip_tags($match);	
					foreach($comp as $c) {
						$res = str_replace($c, " " . $c . " ", $res);
						$res = str_replace("\n","", $res);
					}
					if ($res) $out['def'] = $out['def'] . "<br>" . $res;
				}
			}
		}
	}
	// Link word (check the whole div
	
	
	
	// Get conjugation
	// Get word class
	$find = "ordklass";	
	$wordClass = getSubStr($def,$find)[0];
	// Find all the conjugations
	$find = "bojning";	
	$conjugations = getSubStr($def,$find, true);
	
	$out['meta'] = conjugate($word, $conjugations);
	$out['meta'] = $out['meta'] . "<br>" . $wordClass;
	$find = "uttal";
	$out['meta'] = $out['meta'] . "<br>" . getPronunciation($def);
	$out['more'] = getMore($def);
	echo json_encode($out);
	
	function getSubStr($def,$find,$isSpan=false) {
		
		$found = array();
		if ($isSpan) {			
			$end = "</span>";
		} else {
			$end = "</div>";
		}
		$find = '="' . $find . '">';
		$len = strlen($find);
		
		$pos1 = strpos($def, $find);
		if (!$pos1) return;
		$pos2 = strpos($def, $end, $pos1);
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
	
	function getPronunciation($def) {
		$found = "";
		$find = '="uttal"';
		$len = strlen($find);
		$pos1 = strpos($def, $find);
		if ($pos1) {
			$pos2 = strpos($def, "ljudfil",$pos1);
			$lenFound = $pos2 - $pos1 + $len;	
			$found = substr($def,$pos1 + $len + 1,$lenFound);
			$found = strip_tags($found);
		}
		
		return $found;
	}
	
	function getMore($def) {
		$more = "";
		$konst = getSubStr($def,"vt");
		if ($konst) {
			$konst = strip_tags($konst[0]);
			$more = $more . "KONSTRUKTION: " . $konst . "<br>";
		}
		$samman = getSubStr($def,"hvord");		
		if ($samman) {
			$samman = strip_tags($samman[0]);
			$more = $more . "SAMMANSÄTTN./AVLEDN.: " . $samman . "<br>";
		}
		$find = "</summary>";
		$len = strlen($find);
		$pos1 = strpos($def,"</summary>");
		if ($pos1) {
			$pos2 = strpos($def,"</details>");
			if ($pos2) {
				$lenFound = $pos2 - $pos1;
				$more = substr($def,$pos1 + $len + 1, $lenFound);
				$more = strip_tags($more);				
			}
		}
		// Primary example
		$syntExAll = getSubStr($def, "sxblocklx");				
		if ($syntExAll) {			
			$more = $more . "EXEMPEL: " . strip_tags($syntExAll[0] . "<br>");		
		}
		$samman = getSubStr($def, "mxblocklx");
		if ($samman) {
			$hvtag = getSubStr($samman[0],"mx",true);			
			if ($hvtag) {				
				foreach($hvtag as $match) {					
					$more = $more . strip_tags($match) . "; ";
				}
				$more = substr($more, 0, strlen($more) - 2);
			}
		}
		
		// Other examples
		// End is the first </div> after "syntex"
		$find = "cykel";
		$start = 0;
		$firstLoop = true;
		while ($start || $firstLoop) {			
			$firstLoop=false;			
			$res = getCykel($def,$find,$start);			
			$start = $res['pos'];
			$more = $more . "<br>" . $res['found'];			
		}
		$history = strip_tags(getSubStr($def,"etymologiblock")[0]);
		$linkText = getLinkText($history,"hvtag");
		//echo "<br>" . $linkText;
		$history = str_replace(";<br>", "; ", $history);
		$history = str_replace(";\n", "; ", $history);
		$more = $more . "HISTORIK: " . $history;		
		$more = str_replace("\n","<br>",$more);
		$more = str_replace("<br><br>","<br>",$more);				
		$more = str_replace("○<br>","○ ",$more);
		// Hack
		if (substr($more,0,4) === "<br>") $more = substr($more,4);
		return $more;
	}
	
	function getCykel($def,$find,$start) {		
		$out['found'] = "";
		$out['pos'] = null;
		$find = '"' . $find . '">';
		$cykel = getSubStr($def, "cykel");
		if ($cykel) {
			foreach($cykel as $el) {
				
				$utv = getSubStr($el, "utv",true);
				if ($utv) {
					foreach ($utv as $match) {
						$out['found'] = $out['found'] . "○ " . strip_tags($match) . "<br>";
					}
				}
				$syntex = getSubStr($el, "syntex",true);
				if ($syntex) {
					foreach ($syntex as $match) {
						$out['found'] = $out['found'] . strip_tags($match) . "<br>";
					}
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
	
	function getLinkText($link,$start) {
		$pos1 = strpos($link,$start);
		if (!$pos1) return;
		
		$pos2 = strpos($link, "</a>");
		if ($pos2) {
			$text = substr($link,$pos1, $pos2-$pos1+4);
			$text = strip_tags($text);
		}
		return $text;
	}
?>