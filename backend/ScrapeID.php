<?php
	$debug = $_GET["debug"];
	function getEnum($word) {
		$start = strpos($word,"[");		
		if ($start) {
			$end = strpos($word,"]");
			if ($end) {
				$enum = substr($word,$start+1, $end - $start-1);
			} else {
				$enum = 1;
			}
		} else {
			$enum = 1;
		}
		return $enum;
	}
	
	$word = $_GET['word'];	
	$word = preg_replace("/\?[2-9]/","",$word);
	$class = $_GET['class'];
	if ($class === "fraser") {	
		if (str_contains($word,"*")) {
			$tmpArr = explode("*",$word);
			$word = $tmpArr[0];
			$class= fullClassName($tmpArr[1]);
		}
	}	
	$out["id"] = array();
	$out["snr"] = array();	
	$enum = getEnum($word);
	$word = preg_replace("/\[.*\]/","",$word);
	// return an array to support casees where multiple entries exist	
	$urlBase = 'https://svenska.se/tri/f_so.php?sok=';		
	$url = str_replace(" ", "%20", $urlBase . $word);	
	$url = str_replace("ä", "%C3%A4", $url);		
	
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch,CURLOPT_URL,$url);
	curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
	curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:109.0) Gecko/20100101 Firefox/112.0");				
	$response = curl_exec($ch);			
			
	$responseArr = explode("\n", $response);
	if ($debug) {
		var_dump($responseArr);
		return;
	}
	$find = "/so/?id=";
	$urlBaseId = 'https://svenska.se/tri/f_so.php?id=';	
	$tmpArrID = array();
	$tmpArrSNR = array();
	$tmpID = "";
	$tmpSNR = "";
	// Extract IDs
	if (count($responseArr) > 25) {
		// Full definition information
		foreach($responseArr as $l) {	
			if (str_contains($l, "Till SO")) {		
				$start = strpos($l,$find) + strlen($find);
				if ($start) {
					$idLen = 6;									
					$tmpID = substr($l,$start,$idLen);
					$tmpID = str_replace("&p","",$tmpID);
					array_push($tmpArrID,$tmpID);
				}
			}
		}
	} else {
		// Overview information only
		foreach($responseArr as $l) {	
			if (str_contains($l, $find) && str_contains($l,"wordclass")) {			
				
				$start = strpos($l,$find) + strlen($find);
				if ($start) {				
					$idLen = 8;
					// Check if ID belong to correct word class.				
					$tmpFind = 'class="wordclass"> ';
					$tmpStart = strpos($l,$tmpFind);
					if ($tmpStart) {
						$tmpStart += strlen($tmpFind);
						$tmpFind = "</span>";						
						$tmpEnd = strpos($l,$tmpFind,$tmpStart);
						if ($tmpEnd) {
							$tmpClass = substr($l,$tmpStart, $tmpEnd-$tmpStart);							
							if (matchClass($tmpClass,$class)) {								
								$tmpID = substr($l,$start,$idLen);					
								$tmpID = str_replace("&p","",$tmpID);
								array_push($tmpArrID,$tmpID);
							}
						}
					}
				}
			}
		}
	}		
	// Return only the requested ID.	
	if (count($tmpArrID) >= $enum) {		
		array_push($out["id"],$tmpArrID[$enum-1]);		
	}
	$def = "";
	if (count($out["id"]) > 0) {		
		$url = $urlBaseId . $out["id"][0];
		curl_setopt($ch,CURLOPT_URL,$url);					
		$def = curl_exec($ch);
	} else {
		echo json_encode("No Lemma ID found");
		return;
	}

	if (str_contains($def,"\n")) {
		$defArr = explode("\n",$def);			
		foreach ($defArr as $defLine) {							
			// Class checks
			if ($class === "substantiv_en" || $class === "substantiv_ett") {
				$tmpFind = 'class="bojning_inline"';				
				//$tmpFind = 'class="avstav"';
				if (str_contains($defLine,$tmpFind)) {										
					$tmpStart = strpos($defLine,$tmpFind) + strlen($tmpFind);
					$tmpEnd = strpos($defLine,"</span>",strlen($tmpFind));
					if ($tmpEnd) {
						// Note that we want rid of the invisible character "­" below. This is not a visible dash char!
						$firstConj = str_replace("­","",substr($defLine,$tmpStart, $tmpEnd - $tmpStart));																													
						$firstConj = str_replace("|","",$firstConj);
						$firstConj = str_replace("·","",$firstConj);
						$regex = '/' . $word . '/';
						$match0 = preg_match($regex,$firstConj);
						/*
						echo $regex . "\n";
						echo $firstConj . "\n";
						
						if (!$match0) echo "match0\n";
						*/
						
						// Quick-hack to exclude a trouble-some edge-case.	
						// $match0 = $match0 && strpos($firstConj,"boj919562") == null;
						$endChar = substr($firstConj,-1,1);
						
						$match = $match0 && $endChar === "t" && $class === "substantiv_ett";						
						$match = $match || $match0 && $endChar === "n" && $class === "substantiv_en";																								
						$match = 1; // force match if needed
						if ($match && !in_array($tmpSNR,$tmpArrSNR)) array_push($tmpArrSNR,$tmpSNR);												
					} else if (str_contains($defLine,"ingen")) {						
						if (!in_array($tmpSNR,$tmpArrSNR)) array_push($tmpArrSNR,$tmpSNR);
					}
				}
			} else {							
				if (str_contains($defLine,'class="ordklass"')) {	
					$tmpClass = strip_tags($defLine);
					$match = matchClass($tmpClass, $class);
					if ($match) {																
						array_push($tmpArrSNR,$tmpSNR);
					}
				}
			}
			if (str_contains($defLine, 'superlemma') && strlen($defLine) > 0) {		
				$tmpSNR = $defLine;
				$snrStart = strpos($tmpSNR, 'id="snr');
				if ($snrStart) {									
					$snrStart += 4;
					$snrEnd = strpos($tmpSNR,'">',$snrStart);
					if ($snrEnd) {	
						$tmpSNR = substr($tmpSNR,$snrStart,$snrEnd-$snrStart);
					} else {
						echo "Could not find SNR_End";
						exit();
					}
				}
			}
		}
	}
	
	curl_close($ch);	
	// Temporary hacks
    // array_unshift($tmpArrSNR,"test");
	// array_push($tmpArrSNR,"snr194285");	
	if (count($tmpArrSNR) >= $enum) {
		$out["snr"] = $tmpArrSNR[$enum-1];
	} else if (count($tmpArrID) === 1 && count($tmpArrSNR) === 0) {		
		echo json_encode($out);
		return;
	} else {
		var_dump($tmpArrID);
		var_dump($tmpArrSNR);				
		echo json_encode("Too few SNR IDs found");
		exit();
	}
	echo json_encode($out);
	
	function matchClass($read, $given) {
		$res = false;
		if ($read === "subst.") {
			$res =  ($given === "substantiv_en" || $given === "substantiv_ett");
		} else if ($read === "adj.") {
			$res = "adjektiv" === $given;			
		} else if ($read === "adv.") {
			$res = "adverb" === $given;
		} else if ($read === "adjektiviskt slutled") {
			$res = "slutled" === $given;
		} else if ($read === "substantiverat adjektiv") {				
			$res = "adjektiv" === $given;			
		} else {
			$res = $read === $given;
		}
		if ($given === "plural" && $read === "subst.") {
			$res = true;
		}
		return $res;
	}
		
	function breaker($txt) {
		echo $txt;
		exit();
	}
	
	function fullClassName($inClass) {		
		switch($inClass) {
			case "adj":
				return "adjektiv";
			case "en":
				return "substantiv_en";
			case "ett":
				return "substantiv_ett";
			case "adv":
				return "adverb";
			case "räk":
				return "räkneord";
			case "plu":
				return "plural";
		}
		return $inClass;
	}
?>