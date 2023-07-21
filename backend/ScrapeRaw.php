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
		echo "Failed to retrieve content from Svenska Ordlista server #1.";
		exit();
	}
	// Check this resolves to the correct word
	$find = 'class="slank" href="';
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
			}
		}
	}
	echo json_encode($url);
	
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
	
	
?>