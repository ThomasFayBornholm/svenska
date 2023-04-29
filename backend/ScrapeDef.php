<?php
	$word = $_GET['word'];
	// return an array to support casees where multiple entries exist
	$out['meta'] = "";
	$out['def'] = "";
	$out['more'] = "";
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
			
			$found = str_replace("</span>", "",$found);
			$found = str_replace('<span class="condensed_accent">','',$found);
			$found = str_replace('<a class="ljudfil" href="#!','',$found);
			
		}
		
		return $found;
	}
	
	function getMore($def) {
		$more = "";
		
		$find = "</summary>";
		$len = strlen($find);
		$pos1 = strpos($def,"</summary>");
		if ($pos1) {
			$pos2 = strpos($def,"</details>");
			if ($pos2) {
				$lenFound = $pos2 - $pos1;
				$more = substr($def,$pos1 + $len + 1, $lenFound);
				$more = str_replace("</span>","",$more);
				$more = str_replace("</div>","",$more);
				$more = str_replace("</details>\n","",$more);
				$more = str_replace('<div class="vt">',"",$more);
				$more = str_replace('<span class="caps">',"",$more);
				
			}
		}
		// Primary example
		$syntExArr = getSubStr($def, "syntex",true);				
		$more = $more . "EXEMPEL: " .$syntExArr[0];		
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
		$history = getSubStr($def, "fb", true)[0];
		$history = str_replace(' (<span class="ur">Svenska Medeltids-Postillor',"",$history);		
		$more = $more . "<br>HISTORIK<br>" . $history;
		
		$more = str_replace("\n","<br>",$more);
		$more = str_replace("<br><br>","<br>",$more);				
		$more = str_replace("○<br>","○ ",$more);
		return $more;
	}
	
	function getCykel($def,$find,$start) {
		$out['found'] = "";
		$out['pos'] = null;
		$find = '"' . $find . '">';
		
		$len = strlen($find);
		$pos1 = strpos($def,$find,$start);
		if ($pos1) {
			$pos2= strpos($def, "syntex", $pos1);					
			if ($pos2) $pos2 = strpos($def, "</div>", $pos2 + 1);			
			if ($pos2) {
				
				$lenFound = $pos2 - $pos1 + $len;
				$tmp = substr($def, $pos1 + $len, $lenFound);
				// Replace all ids
				$pattern = '/\d*/';
				
				$tmp = preg_replace($pattern,"",$tmp);
				$rep = ["</a>", "</span>", "</div>",'<div class="sxblocklx">','<span class="utv">','<span class="syntex">','<span class="cbetydelse" id="kcnr"><span class="punkt">'];
				array_push($rep, '<div class="mxblocklx"><span class="mx"><a class="hvtag" target="_parent" href="/so/?id=">');
				foreach($rep as $r) {
					$tmp = str_replace($r, "", $tmp);
				}
				$tmp = str_replace("\n\n","\n",$tmp);
				$out['found'] = $tmp;
				$out['pos'] = $pos1 + $len + 1;
			}
		}
		return $out;
	}
	
	function conjugate($word, $arr) {
		$tmp = $word . " ";		
		for ($i = 0; $i < count($arr); $i++) {
			$tmp = $tmp . " " . $arr[$i];
		}
		return $tmp;
	}
	
	$def =  file_get_contents('https://www.svenska.se/tri/f_so.php?sok=' . $word);
	$out['def'] = getSubStr($def,"def", true)[0];
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
?>