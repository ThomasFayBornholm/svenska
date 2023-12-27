<?php
	function fullClassName($short) {
		$full = $short;
		switch ($short) {
			case "en":
				$full = "substantiv_en";
				break;
			case "ett":
				$full = "substantiv_ett";
				break;
			case "adj":
				$full = "adjekttiv";
				break;
			case "pro":
				$full = "pronomen";
				break;
		}
		return $full;
	}
	
	// First get the root 
	$query = $_GET['query'];
	$tmp = explode("_",$query);
	$shortClass = explode(" ", $tmp[1])[0];
	$rest = str_replace($shortClass, "",$tmp[1]);
	
	$class = fullClassName($shortClass);		
	
	$fras = $tmp[0] . $rest;
	$regex = $fras;
	$regex = str_replace("(","\(",$regex);
	$regex = str_replace(")","\)",$regex);
	$regex = str_replace("/","\/", $fras);
	$regex = '/' . $fras . '/';
	$regex = '/vara \(stadd\) vid kassa/';
	
	if ($fras[strlen($fras)-1] === " ") $fras = substr($fras,0,strlen($fras)-1);
		
	$root = explode(" ",$tmp[0]);
	$root = $root[count($root)-1];
	$root = str_replace("-"," ",$root);
	
	$urlBase = 'https://svenska.se/tri/f_so.php?sok=';		
	$urlBaseId = 'https://svenska.se/tri/f_so.php?id=';
	$url = str_replace(" ", "%20", $urlBase . $root);	
	$ch = curl_init();		
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch,CURLOPT_URL,$url);
	curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
	curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:109.0) Gecko/20100101 Firefox/112.0");			
	$def = curl_exec($ch);			
	curl_close($ch);
	
	$find = 'class="slank';
	if (strpos($def,$find)) {		
		// Need to do some more work to resolve to word matches
		// Use id instead of word as key
		$tmpLines = explode("\n", $def);
		$tmpClass = "";
		$isMatch = false;
		foreach($tmpLines as $l ) {			
			if (str_contains($l, "wordclass")) {					
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
	}		

	if (strlen($def) === 0) {
		debug("Failed to retrieve content from Svenska Ordlista server #2.");
		exit();
	}
	
	$tmp = getClass($def,"fras");				
	
	$fullDef = "";
	if (!$tmp) {
		debug("Failed to find any idioms in root word: " . $root);
		exit();
	}
	
	// Check existing idioms and mark any that are not already listed
	$name = "fraser-only";	
	$contents = file_get_contents($name, 'UTF-8');	
	$elements= preg_split("/\r\n|\r|\n/", $contents);
	
	foreach($tmp as $el) {	
		$ref = "";
		$def = "";
		$deft = "";
		$ex = "";
		$fullDef = "";
		// User should not have to deal with non-breaking spaces in word keys			
		
		$el = str_replace("­","",$el);
		
		// Found the relevant phrase, extract info
		$arr = explode("\n",$el);
		foreach($arr as $l) {
			if (str_contains($l,"hvtag")) {
				$pos1 = strpos($l,">") + 1;				
				$pos2 = strpos($l,"</a>",$pos1);				
				$ref = substr($l, $pos1, $pos2-$pos1);
			}
		}
		
		$m = getClass($el,"idiomdef",true);
		if ($m) $def = str_replace("</span>","",$m[0]);
		$m = getClass($el,"idiomdeft",true);
		if ($m) $deft = str_replace("</span>","",$m[0]);
		$m = getClass($el, "idiomex", true);
		if ($m) $ex = str_replace("</span>","",$m[0]);
		
		if (strlen($ref) > 0) $fullDef .= "SE " . $ref;
		if (strlen($def) > 0) $fullDef .= "● " . $def;
		if (strlen($deft) > 0) $fullDef .= " {" . $deft . "}";
		if (strlen($ex) > 0) $fullDef .= ": " . $ex;
		$end = strpos($el,"</span>");
		$key = substr($el,0,$end);
		$key = str_replace("\u00a","", $key); // No invisible characters to user in keys
		foreach($elements as $f) {							
			if ($f === $key) {				
				$key = "***" . $key;
				break;
			}
		}					
		$list[$key] = $fullDef;
	}
		
	echo json_encode($list);
	
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
					$pos2 = strpos($def, $end, $pos1 + 1);						
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
	
	function debug($in) {
		echo "*** " . $in . " ***\n";
	}
?>